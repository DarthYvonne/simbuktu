<?php

namespace App\Services\Llm;

use App\Models\LlmCall;
use Illuminate\Support\Facades\Log;

class UsageLogger
{
    public static function record(
        string $provider,
        string $model,
        int $inputTokens,
        int $outputTokens,
        ?int $latencyMs = null,
        array $context = [],
    ): void {
        try {
            $models = config('llm_models');
            $price = $models[$model] ?? null;
            $cost = 0.0;
            if ($price) {
                $cost = ($inputTokens / 1_000_000) * ($price['price_in'] ?? 0)
                      + ($outputTokens / 1_000_000) * ($price['price_out'] ?? 0);
            }

            LlmCall::create([
                'course_id' => $context['course_id'] ?? null,
                'post_id' => $context['post_id'] ?? null,
                'persona_id' => $context['persona_id'] ?? null,
                'prompt_key' => $context['prompt_key'] ?? 'unknown',
                'provider' => $provider,
                'model' => $model,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost_usd' => round($cost, 6),
                'latency_ms' => $latencyMs,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("UsageLogger failed: {$e->getMessage()}");
        }
    }
}
