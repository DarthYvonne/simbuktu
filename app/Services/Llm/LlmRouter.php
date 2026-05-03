<?php

namespace App\Services\Llm;

use RuntimeException;

class LlmRouter
{
    public function generateText(string $prompt, ?string $modelId = null, array $context = []): string
    {
        $modelId ??= $this->currentModel();
        $providers = config('llm_models');
        if (!isset($providers[$modelId])) {
            throw new RuntimeException("Unknown model: {$modelId}");
        }
        $provider = $providers[$modelId]['provider'];

        return match ($provider) {
            'gemini' => app(GeminiClient::class)->generateText($prompt, $modelId, null, $context),
            'grok' => (new GrokClient())->generateText($prompt, $modelId, $context),
            default => throw new RuntimeException("Unsupported provider: {$provider}"),
        };
    }

    public function currentModel(): string
    {
        $algo = \App\Http\Controllers\Admin\AlgorithmController::current();
        $model = $algo['comment_model'] ?? config('gemini.text_model', 'gemini-2.5-flash');
        $available = config('llm_models');
        return isset($available[$model]) ? $model : 'gemini-2.5-flash';
    }
}
