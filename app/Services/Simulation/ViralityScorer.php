<?php

namespace App\Services\Simulation;

use App\Models\Post;

class ViralityScorer
{
    public function score(Post $post): int
    {
        $exposures = $post->exposures()->count();
        $comments = $post->comments()->count();
        $shares = $post->exposures()->where('action', 'share')->count();
        $maxDepth = $this->maxThreadDepth($post);
        $subcultureSpread = $this->subcultureSpread($post);
        $rounds = max(1, $post->round);

        // Reach speed (0-30): comments per round
        $speed = min(30, ($comments / $rounds) * 10);

        // Engagement volume (0-30)
        $engagement = min(30, $comments * 2 + $shares * 4);

        // Thread depth (0-20): how deep the longest thread goes
        $depth = min(20, $maxDepth * 5);

        // Subculture spread (0-20)
        $spread = min(20, $subcultureSpread * 4);

        return (int) round($speed + $engagement + $depth + $spread);
    }

    private function maxThreadDepth(Post $post): int
    {
        $comments = $post->comments()->get(['id', 'parent_id'])->keyBy('id');
        $maxDepth = 0;
        foreach ($comments as $c) {
            $depth = 0;
            $cur = $c;
            while ($cur->parent_id && isset($comments[$cur->parent_id])) {
                $depth++;
                $cur = $comments[$cur->parent_id];
                if ($depth > 50) break;
            }
            $maxDepth = max($maxDepth, $depth);
        }
        return $maxDepth;
    }

    private function subcultureSpread(Post $post): int
    {
        $personaIds = $post->comments()->pluck('persona_id')->filter()->unique();
        if ($personaIds->isEmpty()) return 0;
        $repo = app(\App\Services\Personas\PersonaRepository::class);
        $subs = collect();
        foreach ($personaIds as $id) {
            $p = $repo->find($id);
            if ($p) $subs = $subs->merge($p['subcultures'] ?? []);
        }
        return $subs->unique()->count();
    }
}
