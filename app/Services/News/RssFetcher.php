<?php

namespace App\Services\News;

use App\Models\NewsHeadline;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RssFetcher
{
    private array $sources = [
        'dr' => 'https://www.dr.dk/nyheder/service/feeds/allenyheder',
        'tv2' => 'https://nyheder.tv2.dk/rss',
        'politiken' => 'https://politiken.dk/rss/senestenyt.rss',
    ];

    public function fetchAll(): array
    {
        $summary = [];
        foreach ($this->sources as $name => $url) {
            try {
                $summary[$name] = $this->fetchSource($name, $url);
            } catch (\Throwable $e) {
                Log::warning("RSS fetch failed for {$name}: {$e->getMessage()}");
                $summary[$name] = ['error' => $e->getMessage()];
            }
        }
        // Prune older than 14 days
        NewsHeadline::where('published_at', '<', now()->subDays(14))->delete();
        return $summary;
    }

    public function fetchSource(string $name, string $url): array
    {
        $response = Http::timeout(15)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (SlopHub news-context-bot)'])
            ->get($url);
        if (!$response->successful()) {
            throw new \RuntimeException("HTTP {$response->status()}");
        }

        $xml = @simplexml_load_string($response->body(), null, LIBXML_NOCDATA | LIBXML_NOERROR);
        if (!$xml) {
            throw new \RuntimeException('XML parse failed');
        }

        $items = $xml->channel->item ?? $xml->entry ?? [];
        $added = 0;
        $seen = 0;
        foreach ($items as $item) {
            $title = trim((string) ($item->title ?? ''));
            $link = trim((string) ($item->link ?? ($item->link['href'] ?? '')));
            $pub = (string) ($item->pubDate ?? $item->published ?? '');
            if (!$title || !$link) continue;

            $publishedAt = $pub ? @strtotime($pub) : null;
            $seen++;

            $exists = NewsHeadline::where('url', $link)->exists();
            if (!$exists) {
                NewsHeadline::create([
                    'source' => $name,
                    'url' => $link,
                    'title' => mb_substr($title, 0, 250),
                    'published_at' => $publishedAt ? date('Y-m-d H:i:s', $publishedAt) : now(),
                    'fetched_at' => now(),
                ]);
                $added++;
            }
        }
        return ['seen' => $seen, 'added' => $added];
    }
}
