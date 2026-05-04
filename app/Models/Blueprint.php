<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blueprint extends Model
{
    protected $guarded = [];
    protected $casts = [
        'parameters'       => 'array',
        'prompt_overrides' => 'array',
    ];

    /** Prompt keys that this blueprint controls — all driven by personality+attributes. */
    public const PROMPT_KEYS = [
        'persona.narrative',
        'comment.compose',
        'comment.interpret',
        'persona.dm',
        'reaction.batch',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
