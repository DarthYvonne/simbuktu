<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $guarded = [];
    protected $casts = [
        'algorithm_config' => 'array',
        'reactions' => 'array',
        'intelligence' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'last_ticked_at' => 'datetime',
    ];

    public function reactionTotal(): int
    {
        return array_sum($this->reactions ?? []);
    }

    public function topReactions(int $n = 3): array
    {
        $r = $this->reactions ?? [];
        arsort($r);
        return array_slice(array_keys($r), 0, $n);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Kladde',
            'active' => 'Aktivt',
            'finished' => 'Dødt',
            default => $this->status,
        };
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at');
    }

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Course::class);
    }

    /**
     * Current poster avatar — always resolved live from the author's Mig profile
     * for this course, so changes there immediately show on all their posts.
     */
    public function currentAuthorImage(): ?string
    {
        if (!$this->user_id || !$this->course_id) return null;
        $m = CourseMembership::where('user_id', $this->user_id)
            ->where('course_id', $this->course_id)
            ->first();
        return $m?->poster_image_path;
    }

    public function currentAuthorName(): string
    {
        if (!$this->user_id || !$this->course_id) return $this->author_name ?: 'Anonym';
        $m = CourseMembership::where('user_id', $this->user_id)
            ->where('course_id', $this->course_id)
            ->first();
        return $m?->poster_name ?: ($this->user?->name ?? ($this->author_name ?: 'Anonym'));
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function exposures(): HasMany
    {
        return $this->hasMany(PostExposure::class);
    }

    public function topLevelComments(): HasMany
    {
        return $this->comments()->whereNull('parent_id');
    }
}
