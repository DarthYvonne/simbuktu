<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\ImageDescriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Run image-to-text on a post's uploaded image and store the result.
 *
 * Why a job: the Gemini Vision call takes 3–15s. Doing it inline in
 * PostController::store blocks a PHP-FPM worker for the entire post
 * creation request and is the most common student-facing endpoint
 * to do so. Description is non-essential metadata — the simulation
 * uses it for richer persona reactions but works fine without.
 */
class DescribeImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(public int $postId)
    {
    }

    public function handle(ImageDescriptionService $describer): void
    {
        $post = Post::find($this->postId);
        if (!$post || !$post->image_path) return;
        if ($post->image_description) return; // already set (e.g. re-dispatch)

        $absolute = Storage::disk('public')->path($post->image_path);
        if (!is_file($absolute)) {
            Log::warning('DescribeImageJob: image file missing', ['post_id' => $this->postId, 'path' => $post->image_path]);
            return;
        }

        $description = $describer->describe(
            $absolute,
            $post->course_id,
            $post->course?->blueprint?->id,
        );

        if ($description !== null && $description !== '') {
            $post->image_description = $description;
            $post->save();
        }
    }

    /**
     * Image descriptions are non-essential — the simulation handles a null
     * description gracefully. So a terminal failure is just logged; we don't
     * try to recover or surface it to the user.
     */
    public function failed(?\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::warning('DescribeImageJob failed terminally', [
            'post_id' => $this->postId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
