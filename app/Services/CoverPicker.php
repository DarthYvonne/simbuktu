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
     * Pick a deterministic cover for a persona based on their subcultures.
     * Returns ['url' => ..., 'thumb' => ..., 'author' => ..., 'author_url' => ...] or null.
     */
    public function pickFor(array $persona): ?array
    {
        $data = $this->load();
        $candidates = [];
        foreach ($persona['subcultures'] ?? [] as $s) {
            if (!empty($data[$s])) {
                foreach ($data[$s] as $c) $candidates[] = $c;
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
