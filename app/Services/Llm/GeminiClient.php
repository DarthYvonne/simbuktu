<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiClient
{
    public function __construct(private array $config)
    {
    }

    public function generateText(string $prompt, ?string $model = null, ?array $responseSchema = null, array $context = []): string
    {
        $model ??= $this->config['text_model'];
        $body = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => array_filter([
                'temperature' => 0.9,
                'responseMimeType' => $responseSchema ? 'application/json' : null,
                'responseSchema' => $responseSchema,
            ]),
        ];

        $start = microtime(true);
        $response = Http::timeout($this->config['timeout'])
            ->post($this->endpoint($model), $body);
        $latency = (int) round((microtime(true) - $start) * 1000);

        if (!$response->successful()) {
            throw new RuntimeException("Gemini text error ({$response->status()}): {$response->body()}");
        }

        $json = $response->json();
        $text = data_get($json, 'candidates.0.content.parts.0.text');
        if (!$text) {
            throw new RuntimeException('Gemini returned no text: ' . $response->body());
        }

        $this->logUsage($json, $model, $latency, $context);
        return $text;
    }

    public function describeImage(string $imagePath, string $prompt, ?string $model = null, array $context = []): string
    {
        $model ??= $this->config['text_model'];
        $mime = mime_content_type($imagePath) ?: 'image/jpeg';
        $body = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['inlineData' => ['mimeType' => $mime, 'data' => base64_encode(file_get_contents($imagePath))]],
                ],
            ]],
        ];
        $start = microtime(true);
        $response = Http::timeout($this->config['timeout'])->post($this->endpoint($model), $body);
        $latency = (int) round((microtime(true) - $start) * 1000);
        if (!$response->successful()) {
            throw new RuntimeException("Gemini vision error ({$response->status()}): {$response->body()}");
        }
        $json = $response->json();
        $text = data_get($json, 'candidates.0.content.parts.0.text');
        if (!$text) throw new RuntimeException('Gemini vision returned no text');
        $this->logUsage($json, $model, $latency, $context);
        return $text;
    }

    public function generateImage(string $prompt, ?string $model = null, array $context = []): string
    {
        $model ??= $this->config['image_model'];
        $body = [
            'contents' => [['parts' => [['text' => $prompt]]]],
        ];

        $start = microtime(true);
        $response = Http::timeout($this->config['timeout'])
            ->post($this->endpoint($model), $body);
        $latency = (int) round((microtime(true) - $start) * 1000);

        if (!$response->successful()) {
            throw new RuntimeException("Gemini image error ({$response->status()}): {$response->body()}");
        }

        $json = $response->json();
        $parts = data_get($json, 'candidates.0.content.parts', []);
        foreach ($parts as $part) {
            if (isset($part['inlineData']['data'])) {
                $this->logUsage($json, $model, $latency, $context);
                return base64_decode($part['inlineData']['data']);
            }
        }

        throw new RuntimeException('Gemini returned no image: ' . substr($response->body(), 0, 500));
    }

    private function logUsage(array $json, string $model, int $latencyMs, array $context): void
    {
        $usage = $json['usageMetadata'] ?? [];
        UsageLogger::record(
            provider: 'gemini',
            model: $model,
            inputTokens: (int) ($usage['promptTokenCount'] ?? 0),
            outputTokens: (int) ($usage['candidatesTokenCount'] ?? 0),
            latencyMs: $latencyMs,
            context: $context,
        );
    }

    private function endpoint(string $model): string
    {
        return rtrim($this->config['base_url'], '/') . "/{$model}:generateContent?key=" . $this->config['api_key'];
    }
}
