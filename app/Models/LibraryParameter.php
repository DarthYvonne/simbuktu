<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryParameter extends Model
{
    protected $guarded = [];
    protected $casts = ['facets' => 'array'];
}
