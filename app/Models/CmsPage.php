<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'sort_order', 'is_visible'];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function menu()
    {
        return static::query()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function url(): string
    {
        return $this->slug === '' ? '/' : '/'.$this->slug;
    }
}
