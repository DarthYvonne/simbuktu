<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsHeadline;
use App\Services\News\NewsDigester;
use App\Services\News\RssFetcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContextController extends Controller
{
    public function __construct(private NewsDigester $digester, private RssFetcher $fetcher)
    {
    }

    public function index()
    {
        $course = Auth::user()->currentCourse();
        return view('admin.context.index', [
            'course' => $course,
            'digest' => $this->digester->current(),
            'headlines' => NewsHeadline::orderByDesc('published_at')->limit(30)->get(),
            'totalHeadlines' => NewsHeadline::count(),
        ]);
    }

    public function setMode(Request $request)
    {
        $course = Auth::user()->currentCourse();
        if (!$course) return back()->with('error', 'Intet aktivt kursus.');
        $mode = $request->input('context_mode');
        $course->context_mode = in_array($mode, ['auto', 'manual']) ? $mode : 'auto';
        $course->save();
        return redirect('/slophub/admin/context')->with('success', 'Mode opdateret.');
    }

    public function saveManual(Request $request)
    {
        $course = Auth::user()->currentCourse();
        if (!$course) return back()->with('error', 'Intet aktivt kursus.');
        $course->manual_context = $request->input('manual_context');
        $course->context_mode = 'manual';
        $course->save();
        return redirect('/slophub/admin/context')->with('success', 'Manuel kontekst gemt.');
    }

    public function buildDigest()
    {
        set_time_limit(0);
        try {
            $this->fetcher->fetchAll();
            $this->digester->refresh();
            return redirect('/slophub/admin/context')->with('success', 'Opsamling lavet.');
        } catch (\Throwable $e) {
            return redirect('/slophub/admin/context')->with('error', 'Fejl: ' . $e->getMessage());
        }
    }
}
