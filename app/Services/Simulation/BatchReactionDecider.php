<?php

namespace App\Services\Simulation;

use App\Models\Post;
use App\Services\Llm\LlmRouter;
use App\Services\Personas\PersonalityRuleRenderer;
use App\Services\PromptRepository;
use Illuminate\Support\Facades\Log;

class BatchReactionDecider
{
    public function __construct(
        private LlmRouter $llm,
        private PromptRepository $prompts,
        private PersonalityRuleRenderer $personality,
    ) {
    }

    /**
     * Ask the LLM to decide actions for a batch of exposed personas.
     * Returns [['persona_id' => ..., 'action' => ..., 'comment' => ?string], ...]
     */
    public function decideBatch(Post $post, array $personas, array $existingComments = []): array
    {
        if (empty($personas)) return [];

        // Build persona summaries: identity + personality rules as prose + topic triggers
        $personaList = [];
        foreach ($personas as $p) {
            $personaList[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'age' => $p['demographics']['age'],
                'job' => $p['demographics']['occupation_hint'],
                'personlighed' => $this->personality->render($p),
                'triggers' => $p['triggers'],
                'inner_contradiction' => $p['inner_contradiction'] ?? '',
            ];
        }

        $postContext = '';
        if (!empty($post->image_description)) {
            $postContext .= "[BILLEDE: {$post->image_description}]\n";
        }
        if ($post->link_title) {
            $postContext .= "[LINK: \"{$post->link_title}\" fra {$post->link_site_name}]\n";
        }

        $commentsContext = '';
        if (!empty($existingComments)) {
            $commentsContext = "EKSISTERENDE KOMMENTARER (det personas kan se):\n";
            foreach ($existingComments as $c) {
                $prefix = !empty($c['from_student']) ? '[STUDERENDE] ' : '';
                $commentsContext .= "- {$prefix}{$c['author']}: \"{$c['text']}\"\n";
            }
        }

        $prompt = $this->prompts->render('reaction.batch', [
            'post_text' => $post->body,
            'post_context' => $postContext,
            'existing_comments' => $commentsContext,
            'personas_json' => json_encode($personaList, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'current_context' => app(\App\Services\News\NewsContextService::class)->current($post->course),
        ]);

        try {
            $raw = $this->llm->generateText($prompt, null, [
                'prompt_key' => 'reaction.batch',
                'course_id' => $post->course_id,
                'post_id' => $post->id,
            ]);
            // Strip any markdown fences
            $raw = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($raw));
            $parsed = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
            return $parsed['decisions'] ?? [];
        } catch (\Throwable $e) {
            Log::warning("Batch reaction failed (post #{$post->id}, " . count($personas) . " personas): {$e->getMessage()}");
            return [];
        }
    }
}
