<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicClient
{
    public function __construct(private ?string $apiKey = null)
    {
        $this->apiKey ??= config('services.anthropic.api_key');
    }

    public function generateText(string $prompt, string $model = 'claude-haiku-4-5-20251001', array $context = []): string
    {
        if (!$this->apiKey) {
            throw new RuntimeException('Anthropic API-nøgle mangler (sæt ANTHROPIC_API_KEY).');
        }

        $start = microtime(true);
        $response = Http::timeout(120)
            ->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => 2048,
                'temperature' => 0.9,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);
        $latency = (int) round((microtime(true) - $start) * 1000);

        if (!$response->successful()) {
            throw new RuntimeException("Anthropic error ({$response->status()}): {$response->body()}");
        }

        $json = $response->json();
        $text = data_get($json, 'content.0.text');
        if (!$text) throw new RuntimeException('Anthropic returned no content');

        $usage = $json['usage'] ?? [];
        UsageLogger::record(
            provider: 'anthropic',
            model: $model,
            inputTokens: (int) ($usage['input_tokens'] ?? 0),
            outputTokens: (int) ($usage['output_tokens'] ?? 0),
            latencyMs: $latency,
            context: $context,
        );

        return $text;
    }
}
