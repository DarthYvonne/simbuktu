<?php

namespace App\Console\Commands\Simulation;

use App\Http\Controllers\Admin\AlgorithmController;
use App\Jobs\TickPostJob;
use App\Models\Post;
use App\Services\Simulation\RoundRunner;
use Illuminate\Console\Command;

class TickCommand extends Command
{
    protected $signature = 'simulation:tick
        {--post= : kør kun for ét post-id}
        {--rounds=1 : antal runder}
        {--force : ignorér round_duration_minutes}
        {--queued : dispatch hver post som et job (kræver kørende queue worker)}';
    protected $description = 'Kør én simulerings-runde for alle aktive posts (eller én specifik).';

    public function handle(RoundRunner $runner): int
    {
        set_time_limit(0);
        $rounds = (int) $this->option('rounds');
        $postId = $this->option('post');
        $force = (bool) $this->option('force');
        $queued = (bool) $this->option('queued');

        for ($r = 0; $r < $rounds; $r++) {
            $query = Post::where('status', 'active');
            if ($postId) $query->where('id', $postId);
            $posts = $query->with('course')->get();

            if ($posts->isEmpty()) {
                $this->info('Ingen aktive posts.');
                return self::SUCCESS;
            }

            foreach ($posts as $post) {
                $algoConfig = AlgorithmController::configFor($post->course);
                $duration = (int) ($algoConfig['round_duration_minutes'] ?? 1);
                if (!$force && $post->last_ticked_at && $post->last_ticked_at->diffInMinutes(now()) < $duration) {
                    $remaining = $duration - $post->last_ticked_at->diffInMinutes(now());
                    $this->line("Skip post #{$post->id} — venter {$remaining} min mere (runde-varighed: {$duration} min)");
                    continue;
                }
                if ($queued) {
                    TickPostJob::dispatch($post->id);
                    $this->info("Dispatched tick for post #{$post->id} (runde " . ($post->round + 1) . ")");
                } else {
                    $this->info("Tick post #{$post->id} (runde " . ($post->round + 1) . ")");
                    $stats = $runner->tick($post);
                    $this->line("  → " . json_encode($stats));
                }
            }
        }
        return self::SUCCESS;
    }
}
