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
        $regionSpread = $this->regionSpread($post);
        $rounds = max(1, $post->round);

        // Reach speed (0-30): comments per round
        $speed = min(30, ($comments / $rounds) * 10);

        // Engagement volume (0-30)
        $engagement = min(30, $comments * 2 + $shares * 4);

        // Thread depth (0-20): how deep the longest thread goes
        $depth = min(20, $maxDepth * 5);

        // Region spread (0-20) — how widely across DK regions the post reached
        $spread = min(20, $regionSpread * 4);

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

    private function regionSpread(Post $post): int
    {
        $personaIds = $post->comments()->pluck('persona_id')->filter()->unique();
        if ($personaIds->isEmpty()) return 0;
        return \App\Models\Persona::whereIn('id', $personaIds)
            ->distinct()
            ->count('region');
    }
}
