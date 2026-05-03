<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshCovers extends Command
{
    protected $signature = 'covers:refresh {--page= : Fixed page number (1-20); random if omitted}';
    protected $description = 'Fetch fresh Unsplash cover photos for each persona subculture';

    private const PER_SUBCULTURE = 30;
    private const CAP = 40;
    private const TARGET = 1000;

    private const MAP = [
        'manosfære/redpill'        => 'men gym workout strength',
        'hash-miljø'               => 'cannabis culture street',
        'klima-aktivisme'          => 'climate protest crowd street',
        'boomer-Facebook'          => 'family reunion outdoor barbecue',
        'Trump-sympati (DK)'       => 'flag rally crowd patriotic',
        'fitness/biohacking'       => 'athlete training gym workout',
        'Christiania-miljø'        => 'colorful street art festival',
        'jæger/friluft'            => 'hunter forest nature outdoor',
        'gaming'                   => 'esports gaming setup screen',
        'crypto/finans'            => 'trading screens finance city',
        'konspirationsmiljø'       => 'surveillance camera dark city',
        'LGBTQ+-aktivisme'         => 'pride parade rainbow crowd',
        'akademisk venstrefløj'    => 'university library books students',
        'håndværkerkultur'         => 'craftsman workshop tools working',
        'wellness/spiritualitet'   => 'yoga meditation nature calm',
        'fodboldkultur'            => 'football stadium crowd fans',
        'landbrugsmiljø'           => 'farmer field tractor rural',
        'motorcykelmiljø'          => 'motorcycle road trip biker',
        'muslimsk miljø'           => 'mosque prayer community gathering',
        'kristen frikirke'         => 'church congregation worship community',
        'techbro'                  => 'startup office laptop coding',
        'antifeministisk'          => 'traditional family outdoor rural',
        'feministisk'              => 'women march protest empowerment',
        'mainstream'               => 'neighborhood street people everyday',
        'kulturelite'              => 'art gallery museum opening night',
        'foreningsdanmark'         => 'sports club community event volunteers',
    ];

    public function handle(): int
    {
        $key = config('services.unsplash.access_key');
        if (!$key) {
            $this->error('UNSPLASH_ACCESS_KEY not set');
            return 1;
        }

        // Start from existing pool so categories that return 0 fresh results keep their old covers
        $path = storage_path('app/covers/subculture_covers.json');
        $seed = resource_path('data/subculture_covers.json');
        $out = is_file($path)
            ? (json_decode(file_get_contents($path), true) ?: [])
            : (is_file($seed) ? (json_decode(file_get_contents($seed), true) ?: []) : []);
        $existingPool = $out;

        $page = (int) ($this->option('page') ?: rand(1, 20));
        $this->line("Fetching page {$page} of Unsplash results...");

        foreach (self::MAP as $sub => $query) {
            $res = Http::withHeaders(['Authorization' => "Client-ID $key"])
                ->get('https://api.unsplash.com/search/photos', [
                    'query' => $query,
                    'per_page' => self::PER_SUBCULTURE,
                    'orientation' => 'landscape',
                    'page' => $page,
                ]);

            if (!$res->ok()) {
                Log::warning("covers:refresh failed for {$sub}: HTTP {$res->status()}");
                continue;
            }

            $fresh = array_slice(array_map(fn ($r) => [
                'id' => $r['id'],
                'url' => $r['urls']['regular'],
                'thumb' => $r['urls']['small'],
                'author' => $r['user']['name'],
                'author_url' => $r['user']['links']['html'],
                'alt' => $r['alt_description'] ?? '',
            ], $res->json('results') ?? []), 0, self::PER_SUBCULTURE);

            // Merge fresh results into the existing pool, dedupe by photo id, cap at CAP
            if (!empty($fresh)) {
                $merged = array_merge($out[$sub] ?? [], $fresh);
                $byId = [];
                foreach ($merged as $c) $byId[$c['id']] = $c;
                $out[$sub] = array_slice(array_values($byId), 0, self::CAP);
                $added = count($out[$sub]) - count($existingPool[$sub] ?? []);
                $this->line("  {$sub}: " . count($out[$sub]) . " (+{$added} new)");
            } else {
                $this->line("  {$sub}: kept existing (" . count($out[$sub] ?? []) . ')');
            }
        }

        // Fallbacks
        $res = Http::withHeaders(['Authorization' => "Client-ID $key"])
            ->get('https://api.unsplash.com/search/photos', [
                'query' => 'blurred city bokeh lights',
                'per_page' => 10,
                'orientation' => 'landscape',
                'page' => $page,
            ]);
        if ($res->ok()) {
            $freshFb = array_map(fn ($r) => [
                'id' => $r['id'],
                'url' => $r['urls']['regular'],
                'thumb' => $r['urls']['small'],
                'author' => $r['user']['name'],
                'author_url' => $r['user']['links']['html'],
                'alt' => $r['alt_description'] ?? '',
            ], $res->json('results') ?? []);
            $mergedFb = array_merge($out['_fallback'] ?? [], $freshFb);
            $fbById = [];
            foreach ($mergedFb as $c) $fbById[$c['id']] = $c;
            $out['_fallback'] = array_slice(array_values($fbById), 0, 10);
        }

        $dir = storage_path('app/covers');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $path = $dir . '/subculture_covers.json';
        file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $total = array_sum(array_map('count', $out));
        $pct = min(100, (int) round($total / self::TARGET * 100));
        $bar = str_repeat('█', (int) ($pct / 5)) . str_repeat('░', 20 - (int) ($pct / 5));
        $this->info("Total: {$total} / " . self::TARGET . " [{$bar}] {$pct}%");
        if ($total < self::TARGET) {
            $this->line("  → Run again after quota resets to accumulate more (use --page=N to force a page)");
        } else {
            $this->info("  ✓ Target reached!");
        }
        return 0;
    }
}
