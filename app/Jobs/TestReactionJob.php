<?php

namespace App\Jobs;

use App\Services\Llm\CommentPromptBuilder;
use App\Services\Llm\LlmRouter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class TestReactionJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        public string $batchId,
        public array $persona,
        public string $postText,
        public ?string $imageDescription = null,
        public ?array $linkPreview = null,
        public ?string $modelId = null,
    ) {
    }

    public function handle(): void
    {
        $result = [
            'model' => $this->modelId,
            'persona' => $this->persona,
            'text' => null,
            'error' => null,
        ];
        try {
            $builder = new CommentPromptBuilder();
            $prompt = $builder->build($this->persona, $this->postText, [], null, $this->imageDescription, $this->linkPreview);
            $result['text'] = trim(app(LlmRouter::class)->generateText($prompt, $this->modelId, [
                'prompt_key' => 'comment.compose',
                'persona_id' => $this->persona['id'] ?? null,
            ]));

            $call = \App\Models\LlmCall::where('persona_id', $this->persona['id'] ?? null)
                ->where('model', $this->modelId)
                ->where('prompt_key', 'comment.compose')
                ->latest('id')
                ->first();
            if ($call) {
                $result['cost_usd'] = (float) $call->cost_usd;
            }
        } catch (\Throwable $e) {
            $result['error'] = substr($e->getMessage(), 0, 200);
        }

        $key = "tester:results:{$this->batchId}";
        $existing = Cache::get($key, []);
        $existing[] = $result;
        Cache::put($key, $existing, 3600);
        Cache::increment("tester:done:{$this->batchId}");
        Cache::put("tester:unread:{$this->batchId}", true, 3600);
    }

    public function failed(\Throwable $e): void
    {
        $key = "tester:results:{$this->batchId}";
        $existing = Cache::get($key, []);
        $existing[] = [
            'model' => $this->modelId,
            'persona' => $this->persona,
            'text' => null,
            'error' => substr($e->getMessage(), 0, 200),
        ];
        Cache::put($key, $existing, 3600);
        Cache::increment("tester:done:{$this->batchId}");
    }
}
