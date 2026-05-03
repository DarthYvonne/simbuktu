<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalityRule extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'bool'];
}
