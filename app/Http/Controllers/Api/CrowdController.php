<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Persona;
use App\Models\Population;
use App\Models\Post;
use App\Services\Personas\PersonaRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Machine-to-machine "Crowd API" — lets an external app (Situation Room) drive
 * a simbuktu course as a crowd-reaction engine:
 *
 *   POST /api/crowd/courses/{course}/posts       push a timeline item -> crowd reacts
 *   GET  /api/crowd/courses/{course}/reactions   poll new persona comments (cursor-based)
 *   GET  /api/crowd/courses/{course}/personas    list the crowd (for interviews)
 *   GET  /api/crowd/populations                  list populations (for picking)
 *   GET  /api/crowd/personas/{id}/avatar         persona portrait (png)
 *
 * The course (with its bound population) is created once in the simbuktu admin
 * UI; this API never mutates course config. Reactions are produced by the
 * existing `simulation:tick` scheduler — pushing a post just makes it active.
 */
class CrowdController extends Controller
{
    /** Max comments returned per reactions poll. Caller pages with ?since=<cursor>. */
    private const REACTIONS_PAGE = 300;

    /** Default / max personas returned when listing a crowd. */
    private const PERSONAS_DEFAULT = 50;
    private const PERSONAS_MAX = 200;

    /** Populations available to bind to a session. */
    public function populations(): JsonResponse
    {
        $populations = Population::query()
            ->withCount('personas')
            ->orderBy('name')
            ->get()
            ->map(fn (Population $p) => [
                'id' => $p->id,
                'name' => $p->name ?? "Population {$p->id}",
                'persona_count' => $p->personas_count,
            ]);

        return response()->json(['populations' => $populations]);
    }

    /** Push a timeline item into the course as an active post; the crowd reacts on the next tick. */
    public function post(Request $request, string $course): JsonResponse
    {
        $courseModel = Course::findOrFail((int) $course);

        $data = $request->validate([
            'body' => 'required|string|min:1|max:5000',
            'author_name' => 'nullable|string|max:120',
        ]);

        $post = Post::create([
            'course_id' => $courseModel->id,
            'user_id' => null,
            'author_name' => $data['author_name'] ?? 'Situation Room',
            'body' => $data['body'],
            'algorithm_config' => null,
            'status' => 'active',
            'started_at' => now(),
            'last_ticked_at' => now(), // first tick waits one full round, matching student posts
        ]);

        return response()->json([
            'post_id' => $post->id,
            'status' => $post->status,
        ], 201);
    }

    /**
     * Poll new persona reactions across every post in the course. Cursor-based:
     * pass the previous response's `cursor` as ?since= to get only newer comments.
     * Student/human comments (user_id set) are excluded — this is the crowd only.
     */
    public function reactions(Request $request, string $course): JsonResponse
    {
        $courseModel = Course::findOrFail((int) $course);
        $since = (int) $request->query('since', 0);

        $postIds = $courseModel->posts()->pluck('id');

        $comments = $postIds->isEmpty()
            ? collect()
            : Comment::whereIn('post_id', $postIds)
                ->whereNull('user_id')           // crowd only — drop human/student comments
                ->where('id', '>', $since)
                ->orderBy('id')
                ->limit(self::REACTIONS_PAGE)
                ->get();

        $items = $comments->map(fn (Comment $c) => [
            'id' => $c->id,
            'post_id' => $c->post_id,
            'parent_id' => $c->parent_id,
            'persona_id' => $c->persona_id,
            'persona_name' => $c->persona_name,
            'avatar_url' => $c->persona_id ? url("/api/crowd/personas/{$c->persona_id}/avatar") : null,
            'body' => $c->body,
            'round' => $c->round,
            'likes' => $c->likes,
            'reactions' => $c->reactions ?? [],
            'created_at' => $c->created_at->toIso8601String(),
        ]);

        // Per-post engagement summary so the caller can show "N people reacting".
        $posts = $courseModel->posts()
            ->withCount('comments')
            ->get()
            ->map(fn (Post $p) => [
                'post_id' => $p->id,
                'status' => $p->status,
                'round' => $p->round,
                'reach' => $p->reach,
                'shares' => $p->shares ?? 0,
                'comments_count' => $p->comments_count,
                'reaction_total' => $p->reactionTotal(),
                'top_reactions' => $p->topReactions(3),
            ]);

        return response()->json([
            'cursor' => $comments->max('id') ?: $since,
            'comments' => $items,
            'posts' => $posts,
        ]);
    }

    /** List the course's crowd — for picking a persona to interview. */
    public function personas(Request $request, string $course): JsonResponse
    {
        $courseModel = Course::findOrFail((int) $course);
        $populationId = $courseModel->population_id;
        abort_unless($populationId, 422, 'Course has no population bound.');

        $limit = min((int) $request->query('limit', self::PERSONAS_DEFAULT), self::PERSONAS_MAX);

        $ids = Persona::where('population_id', $populationId)
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id')
            ->all();

        $resolved = app(PersonaRepository::class)->findMany($ids);

        $personas = collect($resolved)->map(fn (array $p) => [
            'id' => $p['id'],
            'name' => $p['name'] ?? 'Ukendt',
            'avatar_url' => url("/api/crowd/personas/{$p['id']}/avatar"),
            'has_image' => !empty($p['image_file']),
            'occupation' => $p['demographics']['occupation_hint'] ?? null,
            'age' => $p['demographics']['age'] ?? null,
            'region' => $p['demographics']['region'] ?? null,
        ])->values();

        return response()->json(['personas' => $personas]);
    }

    /** Serve a persona's portrait. Mirrors ProfilerController::image but token-gated, not session-gated. */
    public function avatar(string $id): BinaryFileResponse
    {
        $persona = Persona::find($id);
        abort_unless($persona, 404);

        $dir = $persona->population?->imagePath() ?? config('personas.image_path');
        $path = "{$dir}/{$id}.png";
        abort_unless(is_file($path), 404);

        return response()->file($path, ['Cache-Control' => 'public, max-age=86400']);
    }
}
