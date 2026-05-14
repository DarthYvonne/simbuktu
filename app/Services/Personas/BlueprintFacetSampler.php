<?php

namespace App\Services\Personas;

use App\Models\Blueprint;

/**
 * Samples one facet per dimension from a Blueprint, weighted by each facet's weight (% of population).
 * If the sum of weights is below 100, the gap represents personas that get no text from that dimension.
 *
 * Returns an ordered list of [{dimension, facet, text}] entries — one per dimension that produced a hit.
 */
class BlueprintFacetSampler
{
    public function __construct(private Blueprint $blueprint)
    {
    }

    /**
     * @return array<int, array{dimension: string, facet: string, text: string}>
     */
    public function sample(): array
    {
        $out = [];
        foreach ($this->blueprint->parameters ?? [] as $param) {
            $facet = $this->pickFacet($param['facets'] ?? []);
            if ($facet === null) continue;
            $out[] = [
                'dimension_id'    => $param['id'] ?? null,
                'facet_id'        => $facet['id'] ?? null,
                'dimension'       => $param['name'] ?? '',
                'facet'           => $facet['name'] ?? '',
                'text'            => $facet['text'] ?? '',
                'type'            => $param['type'] ?? 'personality',
                'tier'            => $param['tier'] ?? 'primary',
                'value'           => $this->resolveValue($facet['value'] ?? null),
                'show_on_profile' => (bool) ($param['show_on_profile'] ?? false),
            ];
        }
        return $out;
    }

    /**
     * Turn a facet value-pattern into the persona's stored exact value.
     * "16-24" → uniform random int in [16,24]. "42" → 42. Empty → null.
     * Anything else is passed through verbatim.
     */
    private function resolveValue(?string $pattern): int|string|null
    {
        if ($pattern === null) return null;
        $p = trim($pattern);
        if ($p === '') return null;
        if (preg_match('/^\d+$/', $p)) return (int) $p;
        if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $p, $m)) {
            $a = (int) $m[1]; $b = (int) $m[2];
            if ($a > $b) [$a, $b] = [$b, $a];
            return random_int($a, $b);
        }
        return $p;
    }

    /** Concatenate the sampled facet texts as a Danish bullet list. */
    public function renderBlock(array $sampled): string
    {
        $lines = array_filter(array_map(fn ($s) => trim($s['text'] ?? ''), $sampled));
        return $lines ? '- ' . implode("\n- ", $lines) : '';
    }

    /** @return array{name: string, text: string, weight: int}|null */
    private function pickFacet(array $facets): ?array
    {
        $total = 0;
        foreach ($facets as $f) $total += max(0, (int) ($f['weight'] ?? 0));
        if ($total <= 0) return null;

        // Roll against 100 — if total < 100, the gap means "no text".
        $cap = max(100, $total);
        $r = mt_rand(1, $cap);
        $acc = 0;
        foreach ($facets as $f) {
            $w = max(0, (int) ($f['weight'] ?? 0));
            if ($w === 0) continue;
            $acc += $w;
            if ($r <= $acc) return $f;
        }
        return null;
    }
}
