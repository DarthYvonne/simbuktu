<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\Llm\LlmRouter;
use App\Services\Personas\PersonaRepository;
use App\Services\PromptRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AnalyseController extends Controller
{
    public function __construct(
        private PersonaRepository $repo,
        private LlmRouter $llm,
        private PromptRepository $prompts,
    ) {
    }

    public function index(Request $request)
    {
        $course = Auth::user()->currentCourse();
        $posts = Post::where('course_id', $course?->id)->orderByDesc('created_at')->get(['id', 'author_name', 'body', 'round', 'status']);
        $postId = $request->query('post', $posts->first()?->id);
        $post = $postId ? Post::find($postId) : null;

        $data = $post ? $this->analyse($post) : null;

        return view('analyse.index', [
            'posts' => $posts,
            'post' => $post,
            'data' => $data,
            'sentiment' => ($post?->intelligence ?? [])['sentiment'] ?? null,
        ]);
    }

    private function analyse(Post $post): array
    {
        $exposures = $post->exposures()->get();
        $comments = $post->comments()->get();

        $byRound = [];
        $maxRound = max($post->round, 1);
        for ($r = 1; $r <= $maxRound; $r++) {
            $roundExposures = $exposures->where('round', $r);
            $reactions = $roundExposures->filter(fn ($e) => str_starts_with($e->action, 'react_') || $e->action === 'share' || $e->action === 'comment')->count();
            $byRound[$r] = [
                'round' => $r,
                'exposed' => $roundExposures->count(),
                'reactions' => $reactions,
            ];
        }
        $byRound = array_values($byRound);

        // Trim trailing empty rounds — keep up to 3 as "dying" context
        $lastActive = -1;
        foreach ($byRound as $i => $r) {
            if ($r['exposed'] > 0 || $r['reactions'] > 0) $lastActive = $i;
        }
        if ($lastActive >= 0) {
            $byRound = array_slice($byRound, 0, min(count($byRound), $lastActive + 1 + 3));
        }

        // Engagers = everyone who commented, reacted, or shared
        $commenterIds = $comments->pluck('persona_id')->filter()->unique();
        $reactorIds = $exposures->filter(fn ($e) => str_starts_with($e->action, 'react_'))->pluck('persona_id')->unique();
        $sharerIds = $exposures->where('action', 'share')->pluck('persona_id')->unique();
        $engagerIds = $commenterIds->merge($reactorIds)->merge($sharerIds)->unique();

        $engagers = $engagerIds->map(fn ($pid) => $this->repo->find($pid))->filter()->values();

        // Facet distribution across engagers — group sampled facets per dimension.
        $facetCounts = [];
        foreach ($engagers as $persona) {
            foreach ($persona['dimensions'] ?? [] as $d) {
                $key = ($d['dimension'] ?? '') . ' · ' . ($d['facet'] ?? '');
                $facetCounts[$key] = ($facetCounts[$key] ?? 0) + 1;
            }
        }
        arsort($facetCounts);

        // Reaction breakdown (from post.reactions JSON aggregation)
        $reactions = $post->reactions ?? [];
        arsort($reactions);

        // Demographics across all engagers
        $ageBuckets = ['16-24'=>0,'25-34'=>0,'35-44'=>0,'45-54'=>0,'55-64'=>0,'65+'=>0];
        $genders = [];
        $regionCounts = [];
        foreach ($engagers as $p) {
            $age = $p['demographics']['age'] ?? null;
            if (is_numeric($age)) {
                $bucket = $age < 25 ? '16-24' : ($age < 35 ? '25-34' : ($age < 45 ? '35-44' : ($age < 55 ? '45-54' : ($age < 65 ? '55-64' : '65+'))));
                $ageBuckets[$bucket]++;
            }
            $g = $p['demographics']['gender'] ?? null;
            if ($g) $genders[$g] = ($genders[$g] ?? 0) + 1;
            $r = $p['demographics']['region'] ?? null;
            if ($r) $regionCounts[$r] = ($regionCounts[$r] ?? 0) + 1;
        }
        arsort($regionCounts);

        return [
            'rounds' => $byRound,
            'facets' => array_slice($facetCounts, 0, 12, true),
            'reactions' => $reactions,
            'age_buckets' => $ageBuckets,
            'genders' => $genders,
            'regions' => $regionCounts,
            'totals' => [
                'exposures' => $exposures->count(),
                'comments' => $comments->count(),
                'unique_commenters' => $commenterIds->count(),
                'unique_reactors' => $reactorIds->count(),
                'unique_sharers' => $sharerIds->count(),
                'unique_engagers' => $engagerIds->count(),
                'max_thread_depth' => $this->maxThreadDepth($comments),
                'shares' => $post->shares ?? 0,
                'reactions_total' => array_sum($reactions),
            ],
        ];
    }

    public function spreadGraph(int $postId)
    {
        $post = Post::findOrFail($postId);
        $exposures = $post->exposures()->orderBy('round')->orderBy('created_at')->get();
        if ($exposures->isEmpty()) return response()->json(['nodes' => [], 'edges' => [], 'legend' => []]);

        // Index exposures
        $byRound = [];
        $byPersona = [];
        foreach ($exposures as $e) {
            $byRound[$e->round][] = $e;
            $byPersona[$e->persona_id] = $e;
        }

        // Pre-fetch friends for everyone exposed (one query)
        $allIds = $exposures->pluck('persona_id')->unique()->all();
        $friendsOf = []; // pid => [friend ids]
        foreach ($allIds as $pid) $friendsOf[$pid] = \App\Models\Friendship::friendIdsOf($pid);

        // Reactor/sharer index per round
        $engagersByRound = []; // round => [persona_id]
        foreach ($exposures as $e) {
            $isEngager = $e->action === 'share' || str_starts_with($e->action, 'react_') || $e->action === 'comment';
            if ($isEngager) $engagersByRound[$e->round][] = $e->persona_id;
        }

        // Attribute each exposure
        $attributions = []; // persona_id => ['type' => via_friend|discovery, 'source' => ?persona_id]
        foreach ($exposures as $e) {
            $persona = $this->repo->find($e->persona_id);
            if (!$persona) continue;
            $r = $e->round;

            if ($r > 1) {
                $prev = $engagersByRound[$r - 1] ?? [];
                $intersect = array_values(array_intersect($prev, $friendsOf[$e->persona_id] ?? []));
                if (!empty($intersect)) {
                    $attributions[$e->persona_id] = ['type' => 'via_friend', 'source' => $intersect[0]];
                    continue;
                }
            }
            $attributions[$e->persona_id] = ['type' => 'discovery', 'source' => null];
        }

        // Build nodes + edges
        $colors = ['via_friend' => '#22c55e', 'discovery' => '#9ca3af', 'post' => '#e11d48'];
        $nodes = [];
        $edges = [];

        // Pseudo-root for the post itself
        $nodes[] = ['id' => 'POST', 'label' => '📣 Opslag', 'color' => $colors['post'], 'size' => 18, 'attribution' => 'post', 'action' => null, 'round' => 0];

        foreach ($exposures as $e) {
            $persona = $this->repo->find($e->persona_id);
            if (!$persona) continue;
            $attr = $attributions[$e->persona_id] ?? ['type' => 'discovery', 'source' => null];
            $isEngager = $e->action === 'share' || str_starts_with($e->action, 'react_') || $e->action === 'comment';
            $size = $isEngager ? 12 : 6;
            if ($e->action === 'share') $size = 16;
            if ($e->action === 'comment') $size = 14;

            $nodes[] = [
                'id' => $e->persona_id,
                'label' => $persona['name'],
                'image' => !empty($persona['image_file']) ? url('/simulation/profiler/'.$persona['id'].'/thumb') : null,
                'color' => $colors[$attr['type']],
                'size' => $size,
                'attribution' => $attr['type'],
                'action' => $e->action,
                'round' => $e->round,
                'url' => url('/simulation/profiler/'.$e->persona_id),
            ];

            if ($attr['type'] === 'via_friend' && $attr['source']) {
                $edges[] = ['id' => 'e'.$e->id, 'source' => $attr['source'], 'target' => $e->persona_id, 'attribution' => 'via_friend'];
            } else {
                $edges[] = ['id' => 'en'.$e->id, 'source' => 'POST', 'target' => $e->persona_id, 'attribution' => 'discovery'];
            }
        }

        return response()->json([
            'nodes' => $nodes,
            'edges' => $edges,
            'legend' => [
                'via_friend' => ['color' => $colors['via_friend'], 'label' => 'Via ven (graf)'],
                'discovery' => ['color' => $colors['discovery'], 'label' => 'Tilfældig discovery'],
            ],
        ]);
    }

    public function sentiment(int $postId)
    {
        $post = Post::findOrFail($postId);
        $comments = $post->comments()->get(['id', 'persona_name', 'body']);

        if ($comments->isEmpty()) {
            return response()->json(['error' => 'Ingen kommentarer at analysere endnu.'], 422);
        }

        $list = $comments->map(fn ($c) => "[{$c->id}] {$c->persona_name}: ".str_replace(["\r","\n"], ' ', (string) $c->body))->implode("\n");

        $prompt = $this->prompts->render('sentiment.analyse', [
            'post_text' => $post->body,
            'comments_list' => $list,
        ]);

        try {
            $raw = $this->llm->generateText($prompt, null, ['prompt_key' => 'sentiment.analyse']);
            $json = preg_replace('/^```(?:json)?\s*|\s*```\s*$/m', '', trim($raw));
            if (preg_match('/\{.*\}/s', $json, $m)) $json = $m[0];
            $parsed = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            Log::warning("Sentiment analyse failed for post {$postId}: {$e->getMessage()} — raw: ".substr($raw ?? '', 0, 500));
            return response()->json(['error' => 'Analysen fejlede: '.$e->getMessage()], 500);
        }

        $stanceById = [];
        foreach (($parsed['classifications'] ?? []) as $c) {
            $id = (int) ($c['id'] ?? 0);
            $stance = $c['stance'] ?? 'neutral';
            if (!in_array($stance, ['pro','con','neutral'], true)) $stance = 'neutral';
            if ($id) $stanceById[$id] = $stance;
        }

        $buckets = ['pro' => [], 'con' => [], 'neutral' => []];
        foreach ($comments as $c) {
            $stance = $stanceById[$c->id] ?? 'neutral';
            $buckets[$stance][] = ['id' => $c->id, 'persona_name' => $c->persona_name, 'body' => $c->body];
        }

        $result = [
            'summary' => $parsed['summary'] ?? '',
            'totals' => [
                'pro' => count($buckets['pro']),
                'con' => count($buckets['con']),
                'neutral' => count($buckets['neutral']),
                'total' => $comments->count(),
            ],
            'buckets' => $buckets,
            'generated_at' => now()->toIso8601String(),
        ];

        $intel = $post->intelligence ?? [];
        $intel['sentiment'] = $result;
        $post->intelligence = $intel;
        $post->save();

        return response()->json($result);
    }

    private function maxThreadDepth($comments): int
    {
        $byId = $comments->keyBy('id');
        $max = 0;
        foreach ($comments as $c) {
            $d = 0; $cur = $c;
            while ($cur->parent_id && isset($byId[$cur->parent_id])) {
                $d++; $cur = $byId[$cur->parent_id];
                if ($d > 50) break;
            }
            $max = max($max, $d);
        }
        return $max;
    }
}
