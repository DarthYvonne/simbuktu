<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Persona extends Model
{
    protected $guarded = [];
    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'subcultures' => 'array',
        'persona_data' => 'array',
    ];

    public function population(): BelongsTo
    {
        return $this->belongsTo(Population::class);
    }

    public function toFullArray(): array
    {
        return $this->persona_data ?? [];
    }
}
