<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $guarded = [];
    protected $casts = ['reactions' => 'array'];

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

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFromStudent(): bool
    {
        return $this->user_id !== null;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }

    public function commentReactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }
}
