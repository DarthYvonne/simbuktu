<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Population;
use Illuminate\Support\Str;

class Course extends Model
{
    protected $guarded = [];
    protected $casts = ['algorithm_config' => 'array'];

    protected static function booted(): void
    {
        static::creating(function (self $course) {
            if (!$course->slug) $course->slug = Str::slug($course->name) . '-' . Str::lower(Str::random(4));
            if (!$course->invite_token) $course->invite_token = Str::random(32);
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CourseMembership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_memberships')->withPivot(['role', 'title']);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function population(): BelongsTo
    {
        return $this->belongsTo(Population::class);
    }

    public function inviteUrl(): string
    {
        return url("/simulation/invite/{$this->invite_token}");
    }
}
