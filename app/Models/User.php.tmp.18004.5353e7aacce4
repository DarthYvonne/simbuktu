<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'is_admin', 'active_course_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'last_feed_visited_at' => 'datetime',
        ];
    }

    public function unreadFeedCount(): int
    {
        $course = $this->currentCourse();
        if (!$course) return 0;
        $postIds = \App\Models\Post::where('course_id', $course->id)->pluck('id');
        if ($postIds->isEmpty()) return 0;
        $since = $this->last_feed_visited_at;

        $comments = \App\Models\Comment::whereIn('post_id', $postIds)
            ->when($since, fn ($q) => $q->where('created_at', '>', $since))
            ->count();
        $engagements = \App\Models\PostExposure::whereIn('post_id', $postIds)
            ->where(function ($q) { $q->where('action', 'share')->orWhere('action', 'like', 'react_%'); })
            ->when($since, fn ($q) => $q->where('created_at', '>', $since))
            ->count();
        return $comments + $engagements;
    }

    public function unreadMessagesCount(): int
    {
        $course = $this->currentCourse();
        if (!$course) return 0;
        return \Illuminate\Support\Facades\DB::table('conversation_messages as m')
            ->join('conversations as c', 'c.id', '=', 'm.conversation_id')
            ->where('c.user_id', $this->id)
            ->where('c.course_id', $course->id)
            ->where('m.role', 'persona')
            ->where(function ($q) {
                $q->whereNull('c.last_seen_at')->orWhereColumn('m.created_at', '>', 'c.last_seen_at');
            })
            ->count();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CourseMembership::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_memberships')
            ->withPivot(['role', 'title', 'bio', 'linkedin', 'image_path', 'poster_name', 'poster_image_path']);
    }

    public function activeCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'active_course_id');
    }

    public function currentCourse(): ?Course
    {
        if ($this->active_course_id) {
            $course = Course::find($this->active_course_id);
            if ($course && $this->hasAccessTo($course)) return $course;
        }
        return $this->courses()->first();
    }

    public function currentMembership(): ?CourseMembership
    {
        $course = $this->currentCourse();
        if (!$course) return null;
        return CourseMembership::where('user_id', $this->id)->where('course_id', $course->id)->first();
    }

    public function hasAccessTo(Course $course): bool
    {
        if ($this->is_admin) return true;
        return $this->memberships()->where('course_id', $course->id)->exists();
    }
}
