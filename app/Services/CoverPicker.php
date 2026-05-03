<?php

namespace App\Services;

class CoverPicker
{
    private ?array $data = null;

    private function load(): array
    {
        if ($this->data !== null) return $this->data;
        $fresh = storage_path('app/covers/subculture_covers.json');
        $seed = resource_path('data/subculture_covers.json');
        $path = is_file($fresh) ? $fresh : $seed;
        $this->data = is_file($path)
            ? (json_decode(file_get_contents($path), true) ?: [])
            : [];
        return $this->data;
    }

    /**
     * Pick a deterministic cover for a persona. First looks for matches against any sampled
     * facet name in the blueprint dimensions (works for Subkultur dimension where facet
     * names match the JSON keys). If nothing matches, picks from the full cover pool so each
     * persona gets a different image rather than every one defaulting to the same fallback.
     * Keyed by the persona's id so the same persona always sees the same cover.
     */
    public function pickFor(array $persona): ?array
    {
        $data = $this->load();
        $candidates = [];
        foreach ($persona['dimensions'] ?? [] as $d) {
            $facet = $d['facet'] ?? null;
            if ($facet && !empty($data[$facet])) {
                foreach ($data[$facet] as $c) $candidates[] = $c;
            }
        }
        if (empty($candidates)) {
            // Pool every cover (including _fallback) so personas without a subculture match
            // still get a varied banner instead of the same one repeated.
            foreach ($data as $list) {
                if (!is_array($list)) continue;
                foreach ($list as $c) $candidates[] = $c;
            }
        }
        if (empty($candidates)) return null;

        $seed = crc32((string) ($persona['id'] ?? ''));
        return $candidates[$seed % count($candidates)];
    }
}
