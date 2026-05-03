<?php

namespace App\Services\Simulation;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Models\PostExposure;
use App\Services\Llm\CommentPromptBuilder;
use App\Services\Llm\LlmRouter;
use App\Services\Personas\PersonaRepository;
use Illuminate\Support\Facades\Log;

class RoundRunner
{
    public function __construct(
        private PersonaRepository $personas,
        private ViralityScorer $scorer,
        private BatchReactionDecider $batchDecider,
        private TriggerResolver $triggerResolver,
        private CommentPromptBuilder $promptBuilder,
        private LlmRouter $llm,
        private CommentInterpreter $interpreter,
    ) {
    }

    public function tick(Post $post): array
    {
        if ($post->status !== 'active') return ['skipped' => true];

        $post->increment('round');
        $post->last_ticked_at = now();
        $round = $post->round;
        $algo = $post->algorithm_config ?? \App\Http\Controllers\Admin\AlgorithmController::configFor($post->course);

        $alreadyExposedIds = $post->exposures()->pluck('persona_id')->all();
        $existingComments = $post->comments()->count();

        // Exposure size (grows if trending)
        $base = $algo['base_exposure_per_round'] ?? 15;
        $growth = $algo['exposure_growth_factor'] ?? 1.5;
        $threshold = $algo['trending_threshold'] ?? 20;
        $size = $existingComments >= $threshold
            ? (int) round($base * $growth)
            : $base;

        // Random discovery pool + graph spread from last round's engagers
        $discoveryExposures = $this->triggerResolver->pickExposures($size, $alreadyExposedIds);
        $graphExposures = $this->getGraphSpreadPersonas($post, $round, $algo, $alreadyExposedIds);

        // Merge, dedupe (graph first — friends are more "salient")
        $seenIds = [];
        $newExposures = [];
        foreach (array_merge($graphExposures, $discoveryExposures) as $p) {
            if (!isset($seenIds[$p['id']])) {
                $newExposures[] = $p;
                $seenIds[$p['id']] = true;
            }
        }

        // Personas who got a reply to their own comment — likely to come back
        $notified = $this->getNotifiedPersonas($post, $round);

        $stats = ['exposed' => 0, 'liked' => 0, 'commented' => 0, 'shared' => 0, 'replied' => 0];
        return $this->runBatched($post, $round, $algo, $newExposures, $notified, $stats);
    }

    private function getNotifiedPersonas(Post $post, int $currentRound): array
    {
        $recent = Comment::where('post_id', $post->id)
            ->where('round', $currentRound - 1)
            ->whereNotNull('parent_id')
            ->with('parent')
            ->get();

        $notified = [];
        $seen = [];
        foreach ($recent as $reply) {
            $parent = $reply->parent;
            if (!$parent || !$parent->persona_id || in_array($parent->persona_id, $seen)) continue;
            $persona = $this->personas->find($parent->persona_id);
            if (!$persona) continue;

            $returnProb = config('simulation.notification_return_probability', 0.8);
            if (mt_rand(0, 100) / 100 > $returnProb) continue;

            $notified[] = ['persona' => $persona, 'reply_to' => $reply, 'type' => 'reply'];
            $seen[] = $parent->persona_id;
        }

        // Personas whose comments got user reactions since last tick
        $reactedCommentIds = CommentReaction::whereHas('comment', fn ($q) => $q->where('post_id', $post->id)->whereNotNull('persona_id'))
            ->where('created_at', '>=', $post->last_ticked_at ?? now()->subHour())
            ->pluck('comment_id')
            ->unique();

        if ($reactedCommentIds->isNotEmpty()) {
            $reactedComments = Comment::whereIn('id', $reactedCommentIds)
                ->whereNotNull('persona_id')
                ->get();

            foreach ($reactedComments as $comment) {
                if (in_array($comment->persona_id, $seen)) continue;
                $persona = $this->personas->find($comment->persona_id);
                if (!$persona) continue;

                $returnProb = config('simulation.notification_return_probability', 0.8);
                if (mt_rand(0, 100) / 100 > $returnProb) continue;

                $notified[] = ['persona' => $persona, 'reply_to' => $comment, 'type' => 'reaction'];
                $seen[] = $comment->persona_id;
            }
        }

        return $notified;
    }

    /**
     * Each persona sees the post and decides their own action via a batched LLM call.
     */
    private function runBatched(Post $post, int $round, array $algo, array $newExposures, array $notified, array $stats): array
    {
        if (empty($notified) && empty($newExposures)) {
            $post->reach = $post->exposures()->count();
            $post->virality_score = $this->scorer->score($post);
            $this->checkFinished($post, $round, $algo);
            $post->save();
            return $stats + ['llm_calls' => 0, 'round' => $round];
        }

        $visible = max(1, (int) ($algo['comments_visible_to_personas'] ?? 8));
        $existingCommentsArr = $post->comments()
            ->latest()->take($visible)->get()
            ->map(fn ($c) => [
                'author' => $c->persona_name,
                'text' => $c->body,
                'from_student' => $c->user_id !== null,
            ])
            ->reverse()->values()->all();

        $llmCalls = 0;
        $maxLlm = $algo['max_llm_calls_per_round'] ?? 25;

        // 1. Notified personas → individual interpret-then-maybe-reply path.
        $notifiedSeen = [];
        foreach ($notified as $notif) {
            $persona = $notif['persona'];
            $reply = $notif['reply_to'];
            $notifType = $notif['type'] ?? 'reply';
            $pid = $persona['id'];
            if (isset($notifiedSeen[$pid]) || $llmCalls >= $maxLlm) continue;
            $notifiedSeen[$pid] = true;

            if ($notifType === 'reaction') {
                // Persona's comment got a user reaction — write a follow-up comment on the post
                $body = $this->writeComment($post, $persona, null, $existingCommentsArr);
                if ($body === null || $body === '') continue;
                $llmCalls++;
                Comment::create([
                    'post_id' => $post->id,
                    'parent_id' => null,
                    'persona_id' => $pid,
                    'persona_name' => $persona['name'],
                    'body' => $body,
                    'round' => $round,
                ]);
                $stats['commented']++;
                continue;
            }

            $original = $reply->parent;
            if (!$original) continue;

            $action = $this->interpreter->decide($persona, $post, $original, $reply);
            $llmCalls++;
            $stats['exposed']++;

            if ($action === 'reply') {
                $body = $this->writeComment($post, $persona, $reply, $existingCommentsArr);
                if ($body === null || $body === '') continue;
                $llmCalls++;
                Comment::create([
                    'post_id' => $post->id,
                    'parent_id' => $reply->parent_id ?? $reply->id,
                    'persona_id' => $pid,
                    'persona_name' => $persona['name'],
                    'body' => $body,
                    'round' => $round,
                ]);
                $stats['replied']++;
            }
        }

        // 2. New exposures → batched action decision (skip personas already handled as notified).
        $newOnly = array_values(array_filter($newExposures, fn ($p) => !isset($notifiedSeen[$p['id']])));

        $personaById = [];
        foreach ($newOnly as $p) $personaById[$p['id']] = $p;

        $allDecisions = [];
        foreach (array_chunk($newOnly, 20) as $chunk) {
            if ($llmCalls >= $maxLlm) break;
            $decisions = $this->batchDecider->decideBatch($post, $chunk, $existingCommentsArr);
            $llmCalls++;
            foreach ($decisions as $d) { $allDecisions[$d['persona_id'] ?? ''] = $d; }
        }

        foreach ($allDecisions as $pid => $d) {
            $persona = $personaById[$pid] ?? null;
            if (!$persona) continue;
            $action = $d['action'] ?? 'scroll';

            PostExposure::create([
                'post_id' => $post->id,
                'persona_id' => $pid,
                'round' => $round,
                'action' => $action,
            ]);
            $stats['exposed']++;

            if ($action === 'comment') {
                $body = $this->writeComment($post, $persona, null, $existingCommentsArr);
                if ($body === null || $body === '') continue;
                $llmCalls++;
                Comment::create([
                    'post_id' => $post->id,
                    'parent_id' => null,
                    'persona_id' => $pid,
                    'persona_name' => $persona['name'],
                    'body' => $body,
                    'round' => $round,
                ]);
                $stats['commented']++;
            } elseif ($action === 'share') {
                $post->increment('shares');
                $stats['shared']++;
            } elseif (str_starts_with($action, 'react_')) {
                $type = substr($action, 6);
                $reactions = $post->reactions ?? [];
                $reactions[$type] = ($reactions[$type] ?? 0) + 1;
                $post->reactions = $reactions;
                $stats['liked']++;
            }
        }

        $this->reactToStudentComments($post, $round, $stats);
        $llmCalls += $this->replyToStudentComments($post, $round, $algo, $llmCalls, $maxLlm, $existingCommentsArr, $stats);

        $post->reach = $post->exposures()->count();
        $post->virality_score = $this->scorer->score($post);
        $this->checkFinished($post, $round, $algo);
        $post->save();

        return $stats + ['llm_calls' => $llmCalls, 'round' => $round];
    }

    private function writeComment(Post $post, array $persona, ?Comment $replyTo, array $existingCommentsArr): ?string
    {
        try {
            $replyToData = $replyTo ? [
                'author' => $replyTo->persona_name ?: ($replyTo->user?->name ?? 'En bruger'),
                'text' => $replyTo->body,
                'from_student' => $replyTo->user_id !== null,
            ] : null;
            $linkPreview = $post->link_url ? [
                'title' => $post->link_title,
                'description' => $post->link_description,
                'site_name' => $post->link_site_name,
            ] : null;
            $prompt = $this->promptBuilder->build(
                $persona,
                $post->body,
                $existingCommentsArr,
                $replyToData,
                $post->image_description,
                $linkPreview,
                $post->course,
            );
            $modelId = ($post->algorithm_config['comment_model'] ?? null)
                ?? ($post->course?->algorithm_config['comment_model'] ?? null);
            return trim($this->llm->generateText($prompt, $modelId, [
                'prompt_key' => 'comment.compose',
                'course_id' => $post->course_id,
                'post_id' => $post->id,
                'persona_id' => $persona['id'],
            ]));
        } catch (\Throwable $e) {
            Log::warning("Comment gen fail for {$persona['id']}: {$e->getMessage()}");
            return null;
        }
    }

    private function reactToStudentComments(Post $post, int $round, array &$stats): void
    {
        $studentComments = $post->comments()
            ->whereNotNull('user_id')
            ->whereNull('persona_id')
            ->where('round', '>=', max(1, $round - 2))
            ->get();

        if ($studentComments->isEmpty()) return;

        $engagedIds = $post->exposures()
            ->whereIn('action', ['comment', 'share', 'react_like', 'react_love', 'react_haha', 'react_wow', 'react_sad', 'react_angry'])
            ->pluck('persona_id')
            ->unique()
            ->all();

        if (empty($engagedIds)) return;

        $weights = ['like' => 40, 'love' => 20, 'haha' => 15, 'wow' => 10, 'sad' => 8, 'angry' => 7];
        $totalWeight = array_sum($weights);

        foreach ($studentComments as $comment) {
            $alreadyReacted = CommentReaction::where('comment_id', $comment->id)
                ->whereNotNull('persona_id')
                ->pluck('persona_id')
                ->all();
            $candidates = array_diff($engagedIds, $alreadyReacted);
            if (empty($candidates)) continue;

            shuffle($candidates);
            $reactCount = 0;

            foreach ($candidates as $pid) {
                if (mt_rand(1, 100) > 25) continue;

                $roll = mt_rand(1, $totalWeight);
                $cumulative = 0;
                $type = 'like';
                foreach ($weights as $t => $w) {
                    $cumulative += $w;
                    if ($roll <= $cumulative) { $type = $t; break; }
                }

                CommentReaction::create([
                    'comment_id' => $comment->id,
                    'user_id' => null,
                    'persona_id' => $pid,
                    'type' => $type,
                ]);
                $reactCount++;
                if ($reactCount >= 5) break;
            }

            if ($reactCount > 0) {
                $counts = CommentReaction::where('comment_id', $comment->id)
                    ->selectRaw("type, count(*) as cnt")
                    ->groupBy('type')
                    ->pluck('cnt', 'type')
                    ->toArray();
                $comment->update(['reactions' => empty($counts) ? null : $counts]);
            }
        }
    }

    private function replyToStudentComments(Post $post, int $round, array $algo, int $usedLlm, int $maxLlm, array $existingCommentsArr, array &$stats): int
    {
        $studentComments = $post->comments()
            ->whereNotNull('user_id')
            ->whereNull('persona_id')
            ->where('round', '>=', max(1, $round - 2))
            ->doesntHave('replies', 'and', fn ($q) => $q->whereNotNull('persona_id'))
            ->get();

        if ($studentComments->isEmpty()) return 0;

        $engagedIds = $post->exposures()
            ->whereIn('action', ['comment', 'share', 'react_like', 'react_love', 'react_haha', 'react_wow', 'react_sad', 'react_angry'])
            ->pluck('persona_id')
            ->unique()
            ->all();

        if (empty($engagedIds)) return 0;

        $llmCalls = 0;
        shuffle($engagedIds);

        foreach ($studentComments as $comment) {
            if ($usedLlm + $llmCalls >= $maxLlm) break;
            if (mt_rand(1, 100) > 40) continue;

            $pid = $engagedIds[array_rand($engagedIds)];
            $persona = $this->personas->find($pid);
            if (!$persona) continue;

            $body = $this->writeComment($post, $persona, $comment, $existingCommentsArr);
            if ($body === null || $body === '') continue;
            $llmCalls++;

            Comment::create([
                'post_id' => $post->id,
                'parent_id' => $comment->id,
                'persona_id' => $pid,
                'persona_name' => $persona['name'],
                'body' => $body,
                'round' => $round,
            ]);
            $stats['replied']++;
        }

        return $llmCalls;
    }

    private function checkFinished(Post $post, int $round, array $algo): void
    {
        if ($round >= ($algo['max_rounds'] ?? 30)) {
            $post->status = 'finished';
            $post->finished_at = now();
        } elseif ($this->isInactive($post, $algo['inactive_threshold_rounds'] ?? 5)) {
            $post->status = 'finished';
            $post->finished_at = now();
        }
    }

    private function getGraphSpreadPersonas(Post $post, int $round, array $algo, array $alreadyExposedIds): array
    {
        if ($round < 2) return [];

        $reactionRate = ($algo['reaction_spread_rate'] ?? 10) / 100;
        $shareRate = ($algo['share_spread_rate'] ?? 40) / 100;

        $previous = PostExposure::where('post_id', $post->id)
            ->where('round', $round - 1)
            ->whereIn('action', ['react_like', 'react_love', 'react_haha', 'react_wow', 'react_sad', 'react_angry', 'share'])
            ->get();

        $exposed = array_flip($alreadyExposedIds);
        $result = [];
        $seen = [];

        foreach ($previous as $ev) {
            $rate = $ev->action === 'share' ? $shareRate : $reactionRate;
            if ($rate <= 0) continue;

            $friendIds = \App\Models\Friendship::friendIdsOf($ev->persona_id);
            foreach ($friendIds as $fid) {
                if (isset($exposed[$fid]) || isset($seen[$fid])) continue;
                if (mt_rand(0, 100) / 100 > $rate) continue;
                $persona = $this->personas->find($fid);
                if ($persona) {
                    $result[] = $persona;
                    $seen[$fid] = true;
                }
            }
        }
        return $result;
    }

    private function isInactive(Post $post, int $threshold): bool
    {
        $cutoff = $post->round - $threshold;
        if ($cutoff < 1) return false;
        $recent = $post->comments()->where('round', '>', $cutoff)->count();
        return $recent === 0;
    }
}
