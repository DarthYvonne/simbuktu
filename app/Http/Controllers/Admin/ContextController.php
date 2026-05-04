<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsHeadline;
use App\Models\Prompt;
use App\Services\News\NewsDigester;
use App\Services\News\RssFetcher;
use App\Services\PromptRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContextController extends Controller
{
    public function __construct(
        private NewsDigester $digester,
        private RssFetcher $fetcher,
        private PromptRepository $prompts,
    ) {
    }

    public function index()
    {
        $course = Auth::user()->currentCourse();
        $defaults = $this->prompts->defaults();
        $newsDefault = $defaults['news.digest'] ?? null;
        $newsPrompt = Prompt::firstWhere('key', 'news.digest');
        $newsBody = $newsPrompt?->body ?? ($newsDefault['body'] ?? '');
        $newsOverridden = $newsPrompt && $newsDefault && $newsPrompt->body !== $newsDefault['body'];

        return view('admin.context.index', [
            'course'             => $course,
            'digest'             => $this->digester->current(),
            'headlines'          => NewsHeadline::orderByDesc('published_at')->limit(30)->get(),
            'totalHeadlines'     => NewsHeadline::count(),
            'newsBody'           => $newsBody,
            'newsDefault'        => $newsDefault['body'] ?? '',
            'newsPlaceholders'   => $newsDefault['placeholders'] ?? [],
            'newsOverridden'     => $newsOverridden,
        ]);
    }

    public function saveNewsPrompt(Request $request)
    {
        $request->validate(['body' => 'required|string|max:20000']);
        $defaults = $this->prompts->defaults();
        $defaultBody = $defaults['news.digest']['body'] ?? '';
        $body = trim($request->input('body'));

        $prompt = Prompt::firstOrCreate(
            ['key' => 'news.digest'],
            [
                'name'         => $defaults['news.digest']['name'] ?? 'Opsamling af nyheder',
                'description'  => $defaults['news.digest']['description'] ?? null,
                'placeholders' => $defaults['news.digest']['placeholders'] ?? [],
                'body'         => $defaultBody,
            ]
        );
        $prompt->body = $body;
        $prompt->save();
        $this->prompts->clearCache('news.digest');
        return redirect('/simulation/admin/context')->with('success', 'Opsamlings-prompt gemt.');
    }

    public function resetNewsPrompt()
    {
        $defaults = $this->prompts->defaults();
        $defaultBody = $defaults['news.digest']['body'] ?? '';
        Prompt::where('key', 'news.digest')->update(['body' => $defaultBody]);
        $this->prompts->clearCache('news.digest');
        return redirect('/simulation/admin/context')->with('success', 'Prompt nulstillet til standard.');
    }

    public function setMode(Request $request)
    {
        $course = Auth::user()->currentCourse();
        if (!$course) return back()->with('error', 'Intet aktivt kursus.');
        $mode = $request->input('context_mode');
        $course->context_mode = in_array($mode, ['auto', 'manual']) ? $mode : 'auto';
        $course->save();
        return redirect('/simulation/admin/context')->with('success', 'Mode opdateret.');
    }

    public function saveManual(Request $request)
    {
        $course = Auth::user()->currentCourse();
        if (!$course) return back()->with('error', 'Intet aktivt kursus.');
        $course->manual_context = $request->input('manual_context');
        $course->context_mode = 'manual';
        $course->save();
        return redirect('/simulation/admin/context')->with('success', 'Manuel kontekst gemt.');
    }

    public function buildDigest()
    {
        set_time_limit(0);
        try {
            $this->fetcher->fetchAll();
            $this->digester->refresh();
            return redirect('/simulation/admin/context')->with('success', 'Opsamling lavet.');
        } catch (\Throwable $e) {
            return redirect('/simulation/admin/context')->with('error', 'Fejl: ' . $e->getMessage());
        }
    }
}
