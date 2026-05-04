<?php

namespace App\Services\Simulation;

use App\Models\Comment;
use App\Models\Post;
use App\Services\Llm\LlmRouter;
use App\Services\News\NewsContextService;
use App\Services\Personas\PersonaResolver;
use App\Services\PromptRepository;
use Illuminate\Support\Facades\Log;

class CommentInterpreter
{
    public function __construct(
        private LlmRouter $llm,
        private PromptRepository $prompts,
        private PersonaResolver $resolver,
    ) {
    }

    /**
     * Decide whether the persona replies to the given reply on their original comment.
     * Returns 'reply' or 'ignore'.
     */
    public function decide(array $persona, Post $post, Comment $original, Comment $reply): string
    {
        $course = $post->course;
        $current = $course ? app(NewsContextService::class)->current($course) : '';

        $prompt = $this->prompts->render('comment.interpret', [
            'persona_name'        => $persona['name'] ?? '',
            'attributes_block'    => $this->resolver->buildAttributesBlock($persona['dimensions'] ?? []),
            'narrative'           => $persona['narrative'] ?? '',
            'personality_block'   => $persona['personality_block'] ?? '',
            'original_comment'    => $original->body,
            'replier_name'        => $reply->persona_name,
            'replier_is_student'  => $reply->user_id !== null ? ' [STUDERENDE]' : '',
            'reply_text'          => $reply->body,
            'post_text'           => $post->body,
            'current_context'     => $current,
        ]);

        try {
            $modelId = ($post->algorithm_config['comment_model'] ?? null)
                ?? ($post->course?->algorithm_config['comment_model'] ?? null);
            $raw = $this->llm->generateText($prompt, $modelId, [
                'prompt_key' => 'comment.interpret',
                'course_id' => $post->course_id,
                'post_id' => $post->id,
                'persona_id' => $persona['id'],
            ]);
            $raw = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($raw));
            $parsed = json_decode($raw, true);
            $action = $parsed['action'] ?? 'ignore';
            return $action === 'reply' ? 'reply' : 'ignore';
        } catch (\Throwable $e) {
            Log::warning("Comment interpret fail for {$persona['id']}: {$e->getMessage()}");
            return 'ignore';
        }
    }
}
