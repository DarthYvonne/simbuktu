<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LinkPreview
{
    public function fetch(string $url): ?array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; SlopHubBot/1.0; +https://shitstormlab.dk)'])
                ->get($url);

            if (!$response->successful()) return null;
            return $this->parse($response->body(), $url);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function extractFirstUrl(string $text): ?string
    {
        if (preg_match('#https?://[^\s<>"\']+#i', $text, $m)) {
            return rtrim($m[0], '.,;:!?)');
        }
        return null;
    }

    private function parse(string $html, string $url): array
    {
        $og = [];

        if (preg_match_all('#<meta\s+[^>]*(?:property|name)=["\'](og:[^"\']+|twitter:[^"\']+)["\'][^>]*content=["\']([^"\']*)["\'][^>]*>#i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $og[strtolower($m[1])] = html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        if (preg_match_all('#<meta\s+[^>]*content=["\']([^"\']*)["\'][^>]*(?:property|name)=["\'](og:[^"\']+|twitter:[^"\']+)["\'][^>]*>#i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $og[strtolower($m[2])] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        $title = $og['og:title'] ?? $og['twitter:title'] ?? null;
        if (!$title && preg_match('#<title[^>]*>([^<]+)</title>#i', $html, $m)) {
            $title = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        $description = $og['og:description'] ?? $og['twitter:description'] ?? null;
        if (!$description && preg_match('#<meta\s+[^>]*name=["\']description["\'][^>]*content=["\']([^"\']*)["\'][^>]*>#i', $html, $m)) {
            $description = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $image = $og['og:image'] ?? $og['twitter:image'] ?? null;
        if ($image && !preg_match('#^https?://#i', $image)) {
            $image = $this->resolveUrl($url, $image);
        }

        $siteName = $og['og:site_name'] ?? parse_url($url, PHP_URL_HOST);

        return [
            'url' => $url,
            'title' => $title ? mb_substr($title, 0, 200) : null,
            'description' => $description ? mb_substr($description, 0, 400) : null,
            'image' => $image,
            'site_name' => $siteName,
        ];
    }

    private function resolveUrl(string $base, string $relative): string
    {
        if (str_starts_with($relative, '//')) return 'https:' . $relative;
        $parsed = parse_url($base);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        if (str_starts_with($relative, '/')) return "{$scheme}://{$host}{$relative}";
        return "{$scheme}://{$host}/{$relative}";
    }
}
