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
     * Pick a deterministic cover for a persona. Looks for matches against any sampled facet
     * name in the blueprint dimensions; falls back to _fallback list otherwise. Keyed by the
     * persona's id so the same persona always sees the same cover.
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
        if (empty($candidates) && !empty($data['_fallback'])) {
            $candidates = $data['_fallback'];
        }
        if (empty($candidates)) return null;

        $seed = crc32((string) ($persona['id'] ?? ''));
        return $candidates[$seed % count($candidates)];
    }
}
