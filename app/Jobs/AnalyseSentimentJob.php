<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\Llm\LlmRouter;
use App\Services\PromptRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Run the post-comment sentiment classifier asynchronously.
 *
 * AnalyseController::sentiment used to do the LLM call inline. The
 * admin user clicked "Kør analyse" and stared at a spinner for 5–15s.
 * Now the controller flips a `sentiment_pending` flag, dispatches this
 * job, returns immediately. The frontend polls /sentiment-status.
 */
class AnalyseSentimentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 120;

    public function __construct(public int $postId)
    {
    }

    public function handle(LlmRouter $llm, PromptRepository $prompts): void
    {
        $post = Post::find($this->postId);
        if (!$post) return;

        $intel = $post->intelligence ?? [];
        if (($intel['sentiment_pending'] ?? false) !== true) return; // already cancelled / completed

        $comments = $post->comments()->get(['id', 'persona_name', 'body']);
        if ($comments->isEmpty()) {
            $this->markFailed($post, 'Ingen kommentarer at analysere endnu.');
            return;
        }

        $list = $comments->map(fn ($c) => "[{$c->id}] {$c->persona_name}: " . str_replace(["\r","\n"], ' ', (string) $c->body))->implode("\n");
        $prompt = $prompts->render('sentiment.analyse', [
            'post_text'     => $post->body,
            'comments_list' => $list,
        ]);

        $raw = null;
        try {
            $raw = $llm->generateText($prompt, null, ['prompt_key' => 'sentiment.analyse']);
            $json = preg_replace('/^```(?:json)?\s*|\s*```\s*$/m', '', trim($raw));
            if (preg_match('/\{.*\}/s', $json, $m)) $json = $m[0];
            $parsed = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            Log::warning("AnalyseSentimentJob failed for post {$this->postId}: {$e->getMessage()} — raw: " . substr($raw ?? '', 0, 500));
            $this->markFailed($post, 'Analysen fejlede: ' . $e->getMessage());
            return;
        }

        $stanceById = [];
        foreach (($parsed['classifications'] ?? []) as $c) {
            $id = (int) ($c['id'] ?? 0);
            $stance = $c['stance'] ?? 'neutral';
            if (!in_array($stance, ['pro', 'con', 'neutral'], true)) $stance = 'neutral';
            if ($id) $stanceById[$id] = $stance;
        }

        $buckets = ['pro' => [], 'con' => [], 'neutral' => []];
        foreach ($comments as $c) {
            $stance = $stanceById[$c->id] ?? 'neutral';
            $buckets[$stance][] = ['id' => $c->id, 'persona_name' => $c->persona_name, 'body' => $c->body];
        }

        $intel = $post->fresh()->intelligence ?? [];
        $intel['sentiment'] = [
            'summary'     => $parsed['summary'] ?? '',
            'totals'      => [
                'pro'     => count($buckets['pro']),
                'con'     => count($buckets['con']),
                'neutral' => count($buckets['neutral']),
                'total'   => $comments->count(),
            ],
            'buckets'      => $buckets,
            'generated_at' => now()->toIso8601String(),
        ];
        $intel['sentiment_pending'] = false;
        unset($intel['sentiment_error']);
        $post->intelligence = $intel;
        $post->save();
    }

    public function failed(?\Throwable $exception): void
    {
        $post = Post::find($this->postId);
        if (!$post) return;
        $this->markFailed($post, 'Server-fejl' . ($exception ? ': ' . $exception->getMessage() : '.'));
    }

    private function markFailed(Post $post, string $reason): void
    {
        $intel = $post->fresh()->intelligence ?? [];
        $intel['sentiment_pending'] = false;
        $intel['sentiment_error']   = $reason;
        $post->intelligence = $intel;
        $post->save();
    }
}
