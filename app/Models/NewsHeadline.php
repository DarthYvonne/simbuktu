<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsHeadline extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
    ];
}
