<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\Simulation\RoundRunner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

/**
 * Runs one simulation round for a single post. Wrapped as a job so
 * TickCommand can dispatch one per active post and let the queue
 * (Horizon, when live) run them in parallel instead of looping serially.
 *
 * Under the sync queue driver this behaves like an inline method call.
 */
class TickPostJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;          // simulation is not safe to retry — would duplicate comments
    public int $timeout = 300;      // worst case: lots of personas, slow LLM
    public string $queue = 'simulation';

    public function __construct(public int $postId)
    {
    }

    public function middleware(): array
    {
        // Don't let two workers tick the same post at the same time.
        return [(new WithoutOverlapping((string) $this->postId))->releaseAfter(30)->expireAfter(600)];
    }

    public function handle(RoundRunner $runner): void
    {
        $post = Post::find($this->postId);
        if (!$post || $post->status !== 'active') return;

        try {
            $stats = $runner->tick($post);
            Log::info('TickPostJob ok', ['post_id' => $this->postId, 'stats' => $stats]);
        } catch (\Throwable $e) {
            Log::error('TickPostJob failed', [
                'post_id' => $this->postId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
