<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GrokClient
{
    public function __construct(private ?string $apiKey = null)
    {
        $this->apiKey ??= config('services.grok.api_key');
    }

    public function generateText(string $prompt, string $model = 'grok-3-mini', array $context = []): string
    {
        $start = microtime(true);
        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.x.ai/v1/chat/completions', [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.9,
            ]);
        $latency = (int) round((microtime(true) - $start) * 1000);

        if (!$response->successful()) {
            throw new RuntimeException("Grok error ({$response->status()}): {$response->body()}");
        }

        $json = $response->json();
        $text = data_get($json, 'choices.0.message.content');
        if (!$text) throw new RuntimeException('Grok returned no content');

        $usage = $json['usage'] ?? [];
        UsageLogger::record(
            provider: 'grok',
            model: $model,
            inputTokens: (int) ($usage['prompt_tokens'] ?? 0),
            outputTokens: (int) ($usage['completion_tokens'] ?? 0),
            latencyMs: $latency,
            context: $context,
        );

        return $text;
    }
}
