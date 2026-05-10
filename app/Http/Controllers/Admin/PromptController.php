<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prompt;
use App\Services\PromptRepository;
use Illuminate\Http\Request;

class PromptController extends Controller
{
    public function __construct(private PromptRepository $repo)
    {
    }

    public function index(?string $key = null)
    {
        $defaults = $this->repo->defaults();
        $existing = Prompt::get()->keyBy('key');
        foreach ($defaults as $k => $d) {
            if (!isset($existing[$k])) {
                Prompt::create([
                    'key' => $k,
                    'name' => $d['name'],
                    'description' => $d['description'] ?? null,
                    'placeholders' => $d['placeholders'] ?? [],
                    'body' => $d['body'],
                ]);
                continue;
            }
            // Sync metadata (name/description/placeholders) but never overwrite user-edited body
            $p = $existing[$k];
            $p->name = $d['name'];
            $p->description = $d['description'] ?? null;
            $p->placeholders = $d['placeholders'] ?? [];
            if ($p->isDirty()) $p->save();
        }
        // Drop prompts whose key is no longer in defaults (retired prompts)
        Prompt::whereNotIn('key', array_keys($defaults))->delete();
        // Menu order — independent of defaults() definition order
        $menuOrder = array_flip([
            'persona.narrative',
            'image.profile',
            'news.digest',
            'image.describe_post',
            'reaction.batch',
            'comment.interpret',
            'comment.compose',
            'sentiment.analyse',
        ]);
        $prompts = Prompt::get()->sortBy(fn ($p) => $menuOrder[$p->key] ?? 999)->values();
        $current = $key ? Prompt::where('key', $key)->first() : $prompts->first();
        return view('admin.prompts.index', ['prompts' => $prompts, 'current' => $current]);
    }

    public function update(Request $request, Prompt $prompt)
    {
        $request->validate(['body' => 'required|string']);
        $prompt->body = $request->input('body');
        $prompt->save();
        $this->repo->clearCache($prompt->key);
        return redirect("/simulation/admin/prompts/{$prompt->key}")->with('success', 'Prompt gemt.');
    }

    public function reset(Prompt $prompt)
    {
        $default = $this->repo->defaults()[$prompt->key] ?? null;
        if ($default) {
            $prompt->body = $default['body'];
            $prompt->save();
            $this->repo->clearCache($prompt->key);
            return redirect("/simulation/admin/prompts/{$prompt->key}")->with('success', 'Nulstillet til standard.');
        }
        return redirect("/simulation/admin/prompts/{$prompt->key}")->with('error', 'Ingen standard fundet.');
    }
}
