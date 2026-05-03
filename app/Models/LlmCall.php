<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlmCall extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'cost_usd' => 'float',
    ];
}
