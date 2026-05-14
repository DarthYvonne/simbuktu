<?php

namespace App\Http\Controllers;

use App\Models\CommentReaction;
use App\Models\Post;
use App\Services\ImageDescriptionService;
use App\Services\LinkPreview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /** Cap the feed window. A class rarely scrolls past this; older posts stay in DB. */
    private const FEED_PAGE_SIZE = 100;

    public function __construct(
        private LinkPreview $linkPreview,
        private ImageDescriptionService $imageDescriber,
    ) {
    }

    public function index()
    {
        $user = Auth::user();
        $course = $user->currentCourse();
        $query = Post::where('course_id', $course?->id)->where('user_id', $user->id)->orderByDesc('created_at');
        return view('posts.index', [
            'posts' => $query->get(),
            'view' => 'mine',
            'participants' => collect(),
        ]);
    }

    public function allPosts()
    {
        $user = Auth::user();
        $course = $user->currentCourse();
        abort_unless($course, 403, 'Intet aktivt kursus.');

        $posts = Post::where('course_id', $course->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(self::FEED_PAGE_SIZE)
            ->get();

        $authorIds = $posts->pluck('user_id')->filter()->unique()->values();
        $participants = \App\Models\CourseMembership::where('course_id', $course->id)
            ->whereIn('user_id', $authorIds)
            ->with('user')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->user_id,
                'name' => $m->poster_name ?: ($m->user?->name ?? 'Ukendt'),
            ])
            ->sortBy(fn ($p) => mb_strtolower($p['name']))
            ->values();

        return view('posts.index', [
            'posts' => $posts,
            'view' => 'all',
            'participants' => $participants,
        ]);
    }

    public function publicFeed()
    {
        $user = Auth::user();
        $course = $user->currentCourse();
        if (!$course) {
            if ($user->is_admin) return redirect('/simulation/admin/courses');
            abort(403, 'Du er ikke tilmeldt noget kursus.');
        }
        $posts = Post::where('course_id', $course->id)
            ->orderByDesc('created_at')
            ->withCount('comments')
            ->limit(self::FEED_PAGE_SIZE)
            ->get();
        $alerts = $this->buildAlerts($user->id, $course->id);
        // Clear unread badge — user is looking at the feed now
        $user->last_feed_visited_at = now();
        $user->save();
        $canComment = (bool) (($course->algorithm_config ?? [])['students_can_comment'] ?? false);
        return view('feed', ['posts' => $posts, 'alerts' => $alerts, 'canComment' => $canComment]);
    }

    public function unreadCount()
    {
        $user = Auth::user();
        return response()->json([
            'unread' => $user ? $user->unreadFeedCount() : 0,
            'messages' => $user ? $user->unreadMessagesCount() : 0,
        ]);
    }

    public function markFeedSeen()
    {
        $user = Auth::user();
        if ($user) {
            $user->last_feed_visited_at = now();
            $user->save();
        }
        return response()->json(['ok' => true]);
    }

    public function reactionsDetails(Post $post)
    {
        $exposures = \App\Models\PostExposure::where('post_id', $post->id)
            ->where('action', 'like', 'react_%')
            ->orderByDesc('created_at')
            ->get();

        $repo = app(\App\Services\Personas\PersonaRepository::class);
        $attribution = $this->computeAttributions($post);
        $byType = ['like' => [], 'love' => [], 'haha' => [], 'wow' => [], 'sad' => [], 'angry' => []];
        foreach ($exposures as $e) {
            $type = substr($e->action, 6); // strip react_
            if (!isset($byType[$type])) continue;
            $p = $repo->find($e->persona_id);
            if (!$p) continue;
            $byType[$type][] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'has_image' => !empty($p['image_file']),
                'occupation' => $p['demographics']['occupation_hint'] ?? '',
                'age' => $p['demographics']['age'] ?? null,
                'why' => $attribution[$p['id']] ?? null,
            ];
        }

        $counts = array_map('count', $byType);
        $total = array_sum($counts);
        return response()->json([
            'total' => $total,
            'counts' => $counts,
            'personas' => $byType,
        ]);
    }

    private function buildAlerts(int $userId, int $courseId): array
    {
        $myPostIds = Post::where('user_id', $userId)->where('course_id', $courseId)->pluck('id', 'id');
        if ($myPostIds->isEmpty()) return [];

        $repo = app(\App\Services\Personas\PersonaRepository::class);
        $alerts = [];

        // Comments on my posts + replies to my comments
        $myCommentIds = \App\Models\Comment::whereIn('post_id', $myPostIds->keys())
            ->where('user_id', $userId)
            ->pluck('id', 'id');

        $comments = \App\Models\Comment::whereIn('post_id', $myPostIds->keys())
            ->orderByDesc('created_at')->limit(30)->get();
        foreach ($comments as $c) {
            if (!$c->persona_id) continue;
            $persona = $repo->find($c->persona_id);
            $isReplyToMe = $c->parent_id && $myCommentIds->has($c->parent_id);
            $alerts[] = [
                'type' => $isReplyToMe ? 'reply' : 'comment',
                'persona' => $persona,
                'persona_name' => $c->persona_name,
                'post_id' => $c->post_id,
                'body_snippet' => \Illuminate\Support\Str::limit(trim($c->body), 60),
                'time' => $c->created_at,
            ];
        }

        // Reactions + shares on my posts
        $engagements = \App\Models\PostExposure::whereIn('post_id', $myPostIds->keys())
            ->where(function ($q) { $q->where('action', 'share')->orWhere('action', 'like', 'react_%'); })
            ->orderByDesc('created_at')->limit(50)->get();

        foreach ($engagements as $e) {
            $persona = $repo->find($e->persona_id);
            if (!$persona) continue;
            $alerts[] = [
                'type' => $e->action === 'share' ? 'share' : 'react',
                'reaction_type' => str_starts_with($e->action, 'react_') ? substr($e->action, 6) : null,
                'persona' => $persona,
                'persona_name' => $persona['name'] ?? 'Ukendt',
                'post_id' => $e->post_id,
                'time' => $e->created_at,
            ];
        }

        // Sort by time desc, limit 30
        usort($alerts, fn ($a, $b) => $b['time'] <=> $a['time']);
        return array_slice($alerts, 0, 30);
    }

    public function feedData()
    {
        $user = Auth::user();
        $course = $user->currentCourse();
        if (!$course) return response()->json(['posts' => [], 'alerts' => []]);

        $posts = Post::where('course_id', $course->id)
            ->orderByDesc('created_at')
            ->withCount('comments')
            ->limit(self::FEED_PAGE_SIZE)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'reach' => $p->reach,
                'shares' => $p->shares ?? 0,
                'comments_count' => $p->comments_count,
                'reaction_total' => $p->reactionTotal(),
                'top_reactions' => $p->topReactions(3),
                'round' => $p->round,
            ]);

        $alerts = collect($this->buildAlerts($user->id, $course->id))->map(fn ($a) => [
            'type' => $a['type'],
            'reaction_type' => $a['reaction_type'] ?? null,
            'persona_id' => $a['persona']['id'] ?? null,
            'persona_name' => $a['persona_name'],
            'persona_has_image' => !empty($a['persona']['image_file']),
            'post_id' => $a['post_id'],
            'body_snippet' => $a['body_snippet'] ?? null,
            'time' => $a['time']->toIso8601String(),
            'time_human' => $a['time']->diffForHumans(),
        ]);

        return response()->json(['posts' => $posts, 'alerts' => $alerts]);
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required|string|min:3|max:2000',
            'author_name' => 'nullable|string|max:80',
            'image' => 'nullable|image|max:5120', // 5MB
        ]);

        $user = Auth::user();
        $membership = $user->currentMembership();
        $course = $user->currentCourse();
        if (!$course) abort(403, 'Intet aktivt kursus.');

        $posterName = $request->input('author_name') ?: ($membership?->poster_name) ?: $user->name;
        $posterImage = $membership?->poster_image_path;

        $data = [
            'course_id' => $course->id,
            'user_id' => $user->id,
            'author_name' => $posterName,
            'author_image_path' => $posterImage,
            'body' => $request->input('body'),
            'algorithm_config' => null,
            'status' => 'active',
            'started_at' => now(),
            'last_ticked_at' => now(), // so first tick waits a full round_duration_minutes
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $data['image_path'] = $path;
            $course = Auth::user()?->currentCourse();
            $data['image_description'] = $this->imageDescriber->describe(
                Storage::disk('public')->path($path),
                $course?->id,
                $course?->blueprint?->id,
            );
        } else {
            // Only fetch link preview if no image was uploaded
            $url = $this->linkPreview->extractFirstUrl($request->input('body'));
            if ($url) {
                $preview = $this->linkPreview->fetch($url);
                if ($preview) {
                    $data['link_url'] = $preview['url'];
                    $data['link_title'] = $preview['title'];
                    $data['link_description'] = $preview['description'];
                    $data['link_image'] = $preview['image'];
                    $data['link_site_name'] = $preview['site_name'];
                }
            }
        }

        $post = Post::create($data);

        return redirect("/simulation/posts/{$post->id}")->with('success', 'Opslag oprettet — simulering starter ved næste tick.');
    }

    public function linkPreview(Request $request)
    {
        $url = $request->input('url');
        if (!$url || !preg_match('#^https?://#i', $url)) {
            return response()->json(null);
        }
        return response()->json($this->linkPreview->fetch($url));
    }

    public function show(Post $post)
    {
        $post->load(['comments']);
        $canComment = (bool) (($post->course?->algorithm_config ?? [])['students_can_comment'] ?? false);
        return view('posts.show', ['post' => $post, 'canComment' => $canComment]);
    }

    public function feed(Request $request)
    {
        $post = Post::findOrFail($request->route('post'));
        $since = (int) $request->query('since', 0);

        $attribution = $this->computeAttributions($post);

        $rawComments = $post->comments()->where('id', '>', $since)->orderBy('id')->get();

        $studentUserIds = $rawComments->whereNotNull('user_id')->pluck('user_id')->unique()->all();
        $memberships = [];
        if ($studentUserIds && $post->course_id) {
            $memberships = \App\Models\CourseMembership::where('course_id', $post->course_id)
                ->whereIn('user_id', $studentUserIds)
                ->get()->keyBy('user_id');
        }

        $userId = Auth::id();
        $commentIds = $rawComments->pluck('id');
        $myReactions = CommentReaction::where('user_id', $userId)
            ->whereIn('comment_id', $commentIds)
            ->pluck('type', 'comment_id');

        $comments = $rawComments->map(function ($c) use ($attribution, $memberships, $myReactions) {
            $fromStudent = $c->user_id !== null;
            $name = $c->persona_name;
            $image = null;
            if ($fromStudent && ($m = $memberships[$c->user_id] ?? null)) {
                $name = $m->poster_name ?: $name;
                $image = $m->poster_image_path;
            }
            return [
                'id' => $c->id,
                'parent_id' => $c->parent_id,
                'persona_id' => $c->persona_id,
                'persona_name' => $name,
                'author_image' => $image,
                'body' => $c->body,
                'round' => $c->round,
                'likes' => $c->likes,
                'reactions' => $c->reactions ?? [],
                'my_reaction' => $myReactions[$c->id] ?? null,
                'created_at' => $c->created_at->toIso8601String(),
                'from_student' => $fromStudent,
                'why' => $c->persona_id ? ($attribution[$c->persona_id] ?? null) : null,
            ];
        });

        return response()->json([
            'post' => [
                'id' => $post->id,
                'round' => $post->round,
                'status' => $post->status,
                'reach' => $post->reach,
                'virality_score' => $post->virality_score,
                'comment_count' => $post->comments()->count(),
            ],
            'comments' => $comments,
        ]);
    }

    /**
     * For every persona who was exposed to this post, compute a human-readable
     * "why they saw it" string. Returns [persona_id => ['type','text','source_name']]
     */
    private function computeAttributions(Post $post): array
    {
        $exposures = $post->exposures()->orderBy('round')->orderBy('created_at')->get();
        if ($exposures->isEmpty()) return [];

        $repo = app(\App\Services\Personas\PersonaRepository::class);

        $engagersByRound = [];
        foreach ($exposures as $e) {
            if ($e->action === 'share' || $e->action === 'comment' || str_starts_with($e->action, 'react_')) {
                $engagersByRound[$e->round][] = $e->persona_id;
            }
        }

        $out = [];
        foreach ($exposures as $e) {
            $persona = $repo->find($e->persona_id);
            if (!$persona) continue;

            if ($e->round > 1) {
                $prev = $engagersByRound[$e->round - 1] ?? [];
                $friends = \App\Models\Friendship::friendIdsOf($e->persona_id);
                $via = array_values(array_intersect($prev, $friends));
                if (!empty($via)) {
                    $source = $repo->find($via[0]);
                    $out[$e->persona_id] = [
                        'type' => 'via_friend',
                        'text' => 'Jeg så opslaget fordi min ven ' . ($source['name'] ?? 'en ven') . ' delte eller reagerede på det',
                        'source_name' => $source['name'] ?? null,
                    ];
                    continue;
                }
            }

            $out[$e->persona_id] = [
                'type' => 'discovery',
                'text' => 'Jeg så opslaget via algoritmens tilfældige discovery',
                'source_name' => null,
            ];
        }
        return $out;
    }

    public function destroy(Post $post)
    {
        $user = Auth::user();
        if ($post->user_id !== $user->id && !$user->is_admin) {
            abort(403, 'Du kan kun slette egne opslag.');
        }
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }
        $post->delete();
        return redirect('/simulation/posts')->with('success', 'Opslag slettet.');
    }
}
