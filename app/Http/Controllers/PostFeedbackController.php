<?php

namespace App\Http\Controllers;

use App\Models\CourseMembership;
use App\Models\Post;
use App\Models\PostFeedbackMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostFeedbackController extends Controller
{
    public function show(Post $post)
    {
        $this->authorize($post);

        $messages = $this->messagesFor($post);

        return view('posts.feedback', [
            'post' => $post,
            'messages' => $messages,
        ]);
    }

    public function fetch(Post $post)
    {
        $this->authorize($post);
        return response()->json(['messages' => $this->messagesFor($post)]);
    }

    public function send(Request $request, Post $post)
    {
        $this->authorize($post);
        $data = $request->validate(['body' => 'required|string|max:4000']);

        $user = Auth::user();
        $msg = PostFeedbackMessage::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => trim($data['body']),
        ]);

        return response()->json(['message' => $this->formatMessage($msg)]);
    }

    private function authorize(Post $post): void
    {
        $user = Auth::user();
        $course = $user?->currentCourse();
        if (!$course || $post->course_id !== $course->id) {
            abort(403, 'Du har ikke adgang til dette opslags feedback.');
        }
    }

    private function messagesFor(Post $post): array
    {
        return PostFeedbackMessage::with('user')
            ->where('post_id', $post->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => $this->formatMessage($m))
            ->all();
    }

    private function formatMessage(PostFeedbackMessage $m): array
    {
        $m->loadMissing('user');
        $membership = CourseMembership::where('user_id', $m->user_id)
            ->where('course_id', $m->post->course_id ?? null)
            ->first();

        return [
            'id' => $m->id,
            'body' => $m->body,
            'created_at' => $m->created_at->toIso8601String(),
            'created_at_human' => $m->created_at->diffForHumans(),
            'mine' => Auth::id() === $m->user_id,
            'user' => [
                'id' => $m->user_id,
                'name' => $m->user?->name ?? 'Ukendt',
                'image_url' => $membership?->image_path
                    ? \Illuminate\Support\Facades\Storage::url($membership->image_path)
                    : null,
                'is_instructor' => ($membership?->role ?? null) === 'instructor',
            ],
        ];
    }
}
