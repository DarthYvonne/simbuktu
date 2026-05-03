<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\LibraryParameter;

return new class extends Migration
{
    public function up(): void
    {
        $row = LibraryParameter::where('category', 'subkultur')->where('name', 'Subkultur')->first();
        if (!$row) return;

        $facets = $row->facets;
        $n = count($facets);
        if ($n === 0) return;

        $base = intdiv(100, $n);
        $remainder = 100 - $base * $n;
        foreach ($facets as $i => &$f) {
            $f['weight'] = $base + ($i < $remainder ? 1 : 0);
        }
        unset($f);
        $row->update(['facets' => $facets]);
    }

    public function down(): void
    {
    }
};
