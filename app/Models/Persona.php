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
        'persona_data' => 'array',
    ];

    public function population(): BelongsTo
    {
        return $this->belongsTo(Population::class);
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function toFullArray(): array
    {
        $data = $this->persona_data ?? [];
        $data['blueprint_id'] = $this->blueprint_id;
        return $data;
    }
}
