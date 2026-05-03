<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    protected $guarded = [];
    protected $casts = ['placeholders' => 'array'];
}
