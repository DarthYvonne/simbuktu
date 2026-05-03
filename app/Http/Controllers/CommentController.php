<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\CourseMembership;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $user = Auth::user();
        $course = $post->course;

        if (!$course || $user->currentCourse()?->id !== $course->id) {
            abort(403, 'Du er ikke tilknyttet dette kursus.');
        }

        $algo = $course->algorithm_config ?? [];
        if (empty($algo['students_can_comment'])) {
            abort(403, 'Studerende-kommentarer er ikke aktiveret på dette kursus.');
        }

        $data = $request->validate([
            'body' => 'required|string|max:2000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        $parentId = null;
        if (!empty($data['parent_id'])) {
            $parent = Comment::find($data['parent_id']);
            if (!$parent || $parent->post_id !== $post->id) {
                abort(422, 'Ugyldig parent-kommentar.');
            }
            // One-level threading: if parent is already a reply, attach to its parent instead.
            $parentId = $parent->parent_id ?? $parent->id;
        }

        $membership = CourseMembership::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
        $posterName = $membership?->poster_name ?: $user->name;

        $comment = Comment::create([
            'post_id' => $post->id,
            'parent_id' => $parentId,
            'user_id' => $user->id,
            'persona_id' => null,
            'persona_name' => $posterName,
            'body' => trim($data['body']),
            'round' => $post->round,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'comment' => [
                    'id' => $comment->id,
                    'parent_id' => $comment->parent_id,
                    'persona_id' => null,
                    'persona_name' => $comment->persona_name,
                    'body' => $comment->body,
                    'round' => $comment->round,
                    'from_student' => true,
                ],
            ]);
        }
        return back();
    }

    public function react(Request $request, Comment $comment)
    {
        $user = Auth::user();
        $post = $comment->post;
        $course = $post?->course;

        if (!$course || $user->currentCourse()?->id !== $course->id) {
            abort(403);
        }

        $data = $request->validate([
            'type' => 'required|string|in:like,love,haha,wow,sad,angry',
        ]);

        $existing = CommentReaction::where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && $existing->type === $data['type']) {
            $existing->delete();
        } elseif ($existing) {
            $existing->update(['type' => $data['type']]);
        } else {
            CommentReaction::create([
                'comment_id' => $comment->id,
                'user_id' => $user->id,
                'type' => $data['type'],
            ]);
        }

        $counts = CommentReaction::where('comment_id', $comment->id)
            ->selectRaw("type, count(*) as cnt")
            ->groupBy('type')
            ->pluck('cnt', 'type')
            ->toArray();

        $comment->update(['reactions' => empty($counts) ? null : $counts]);

        $myReaction = CommentReaction::where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->value('type');

        return response()->json([
            'reactions' => $counts,
            'total' => array_sum($counts),
            'my_reaction' => $myReaction,
        ]);
    }
}
