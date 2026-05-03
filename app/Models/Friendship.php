<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (self $f) {
            if ($f->persona_id_1 > $f->persona_id_2) {
                [$f->persona_id_1, $f->persona_id_2] = [$f->persona_id_2, $f->persona_id_1];
            }
            if (!$f->created_at) $f->created_at = now();
        });
    }

    public static function friendIdsOf(string $personaId): array
    {
        $a = self::where('persona_id_1', $personaId)->pluck('persona_id_2');
        $b = self::where('persona_id_2', $personaId)->pluck('persona_id_1');
        return $a->merge($b)->values()->all();
    }
}
