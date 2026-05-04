<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('blueprints')->get() as $row) {
            $params = json_decode($row->parameters ?: '[]', true) ?? [];
            $changed = false;
            foreach ($params as $i => $p) {
                if (empty($p['id'])) {
                    $params[$i]['id'] = (string) Str::uuid();
                    $changed = true;
                }
                if (empty($p['type'])) {
                    $params[$i]['type'] = 'personality';
                    $changed = true;
                }
                foreach (($p['facets'] ?? []) as $j => $f) {
                    if (empty($f['id'])) {
                        $params[$i]['facets'][$j]['id'] = (string) Str::uuid();
                        $changed = true;
                    }
                }
            }
            if ($changed) {
                DB::table('blueprints')->where('id', $row->id)->update([
                    'parameters' => json_encode($params, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach (DB::table('library_parameters')->get() as $row) {
            $facets = json_decode($row->facets ?: '[]', true) ?? [];
            $changed = false;
            foreach ($facets as $j => $f) {
                if (empty($f['id'])) {
                    $facets[$j]['id'] = (string) Str::uuid();
                    $changed = true;
                }
            }
            if ($changed) {
                DB::table('library_parameters')->where('id', $row->id)->update([
                    'facets'     => json_encode($facets, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // No-op: UUIDs are additive and harmless to leave behind.
    }
};
