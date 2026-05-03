<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Population extends Model
{
    protected $guarded = [];
    protected $casts = ['config_overrides' => 'array'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class);
    }

    public function resolvedConfig(): array
    {
        $overrides = $this->config_overrides ?? [];
        $merged    = array_replace_recursive(config('personas'), $overrides);

        if (isset($overrides['demographics']['age_brackets'])) {
            $merged['demographics']['age_brackets'] = $overrides['demographics']['age_brackets'];
        }
        return $merged;
    }

    public function imagePath(): string
    {
        return storage_path("app/public/personas/{$this->id}");
    }
}
