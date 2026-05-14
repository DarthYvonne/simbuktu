<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiClient
{
    public function __construct(private ?string $apiKey = null)
    {
        $this->apiKey ??= config('services.openai.api_key');
    }

    public function generateText(string $prompt, string $model = 'gpt-5-mini', array $context = []): string
    {
        if (!$this->apiKey) {
            throw new RuntimeException('OpenAI API-nøgle mangler (sæt OPENAI_API_KEY).');
        }

        $start = microtime(true);
        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);
        $latency = (int) round((microtime(true) - $start) * 1000);

        if (!$response->successful()) {
            throw new RuntimeException("OpenAI error ({$response->status()}): {$response->body()}");
        }

        $json = $response->json();
        $text = data_get($json, 'choices.0.message.content');
        if (!$text) throw new RuntimeException('OpenAI returned no content');

        $usage = $json['usage'] ?? [];
        UsageLogger::record(
            provider: 'openai',
            model: $model,
            inputTokens: (int) ($usage['prompt_tokens'] ?? 0),
            outputTokens: (int) ($usage['completion_tokens'] ?? 0),
            latencyMs: $latency,
            context: $context,
        );

        return $text;
    }
}
