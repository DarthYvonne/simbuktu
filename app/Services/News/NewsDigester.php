<?php

namespace App\Services\News;

use App\Models\NewsHeadline;
use App\Services\Llm\LlmRouter;
use App\Services\PromptRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NewsDigester
{
    private const CACHE_KEY = 'news_digest';

    public function __construct(private LlmRouter $llm, private PromptRepository $prompts)
    {
    }

    public function current(): ?array
    {
        return Cache::get(self::CACHE_KEY);
    }

    public function refresh(): ?array
    {
        // Grab ~200 most recent headlines from 14-day buffer
        $headlines = NewsHeadline::orderByDesc('published_at')->limit(200)->get();
        if ($headlines->isEmpty()) return null;

        $listText = $headlines->map(fn ($h) => '[' . $h->published_at?->format('Y-m-d H:i') . ' · ' . $h->source . '] ' . $h->title)->implode("\n");

        $prompt = $this->prompts->render('news.digest', ['headlines_list' => $listText]);

        $schema = [
            'type' => 'object',
            'properties' => [
                'ongoing_situations' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => '3-6 sentences on ongoing big stories',
                ],
                'recent_breaking' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => '5-10 bullet points from the last 24-48h',
                ],
            ],
            'required' => ['ongoing_situations', 'recent_breaking'],
        ];

        try {
            $json = $this->llm->generateText($prompt, 'gemini-2.5-flash', [
                'prompt_key' => 'news.digest',
            ]);
            // Strip code fences if any
            $json = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($json));
            $parsed = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            $result = [
                'ongoing_situations' => self::toArray($parsed['ongoing_situations'] ?? []),
                'recent_breaking' => self::toArray($parsed['recent_breaking'] ?? []),
                'generated_at' => now()->toIso8601String(),
                'headline_count' => $headlines->count(),
            ];
            Cache::put(self::CACHE_KEY, $result, now()->addHours(24));
            return $result;
        } catch (\Throwable $e) {
            Log::warning("News digest failed: {$e->getMessage()}");
            return null;
        }
    }

    public static function formatForPrompt(array $digest): string
    {
        $out = "AKTUEL KONTEKST — hvad der fylder i dansk offentlighed lige nu:\n\n";
        $ongoing = self::toArray($digest['ongoing_situations'] ?? []);
        $breaking = self::toArray($digest['recent_breaking'] ?? []);
        if (!empty($ongoing)) {
            $out .= "Igangværende:\n";
            foreach ($ongoing as $s) $out .= "- {$s}\n";
        }
        if (!empty($breaking)) {
            $out .= "\nSenest (seneste 1-2 døgn):\n";
            foreach ($breaking as $s) $out .= "- {$s}\n";
        }
        return trim($out);
    }

    public static function toArray($v): array
    {
        if (is_array($v)) return array_values(array_filter($v, fn ($x) => !empty(trim((string) $x))));
        if (is_string($v) && trim($v) !== '') {
            // LLM occasionally returns a single paragraph; split on newlines or keep as one-item array
            $lines = preg_split('/\r?\n/', trim($v));
            $lines = array_values(array_filter(array_map('trim', $lines)));
            return $lines ?: [trim($v)];
        }
        return [];
    }
}
