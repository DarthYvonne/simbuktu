<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsPage extends Model
{
    protected $fillable = ['parent_id', 'title', 'slug', 'content', 'sort_order', 'is_visible'];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
        'parent_id'  => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('title');
    }

    /** Top-level visible pages, eager-loaded with their visible children. */
    public static function menu()
    {
        return static::query()
            ->whereNull('parent_id')
            ->where('is_visible', true)
            ->with(['children' => fn ($q) => $q->where('is_visible', true)])
            ->orderBy('sort_order')
            ->get();
    }

    public function url(): string
    {
        if ($this->parent_id && $this->parent) {
            $parentSlug = trim($this->parent->slug, '/');
            $self       = trim($this->slug, '/');
            return '/'.($parentSlug !== '' ? "{$parentSlug}/{$self}" : $self);
        }
        return $this->slug === '' ? '/' : '/'.$this->slug;
    }
}
