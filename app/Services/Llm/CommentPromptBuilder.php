<?php

namespace App\Services\Llm;

use App\Services\Personas\PersonaResolver;
use App\Services\PromptRepository;

class CommentPromptBuilder
{
    public function __construct(private ?PromptRepository $prompts = null, private ?PersonaResolver $resolver = null)
    {
        $this->prompts ??= new PromptRepository();
        $this->resolver ??= new PersonaResolver();
    }

    public function build(array $persona, string $postText, array $existingComments = [], ?array $replyTo = null, ?string $imageDescription = null, ?array $linkPreview = null, ?\App\Models\Course $course = null): string
    {
        $existingContext = '';
        if (!empty($existingComments)) {
            $existingContext = "\nKOMMENTARER DU KAN SE INDTIL VIDERE:\n";
            foreach ($existingComments as $c) {
                $prefix = !empty($c['from_student']) ? '[STUDERENDE] ' : '';
                $existingContext .= "- {$prefix}{$c['author']}: \"{$c['text']}\"\n";
            }
        }

        $replyContext = '';
        if ($replyTo) {
            $who = !empty($replyTo['from_student']) ? "STUDERENDE {$replyTo['author']}" : $replyTo['author'];
            $replyContext = "\nDU SVARER PÅ DENNE KOMMENTAR FRA {$who}: \"{$replyTo['text']}\"\n";
        }

        $mediaContext = '';
        if ($imageDescription) {
            $mediaContext .= "\n[BILLEDE VEDHÆFTET — du ser dette: {$imageDescription}]\n";
        }
        if ($linkPreview && !empty($linkPreview['title'])) {
            $desc = $linkPreview['description'] ? " — {$linkPreview['description']}" : '';
            $mediaContext .= "\n[LINK VEDHÆFTET: \"{$linkPreview['title']}\"{$desc} (fra {$linkPreview['site_name']})]\n";
        }

        return $this->prompts->render('comment.compose', [
            'persona_name'        => $persona['name'] ?? '',
            'attributes_block'    => $this->resolver->buildAttributesBlock($persona['dimensions'] ?? []),
            'narrative'           => $persona['narrative'] ?? '',
            'personality_block'   => $persona['personality_block'] ?? '',
            'post_text'           => $postText,
            'media_context'       => $mediaContext,
            'existing_context'    => $existingContext,
            'reply_context'       => $replyContext,
            'length_target'       => $this->targetLength($persona, $replyTo !== null),
            'current_context'     => $course ? app(\App\Services\News\NewsContextService::class)->current($course) : '',
        ]);
    }

    private function targetLength(array $persona, bool $isReply): string
    {
        [$min, $max] = [10, 40];
        if ($isReply) { $max = max($min + 2, (int) ($max * 0.7)); }
        $target = random_int($min, max($min, $max));
        return (string) $target;
    }
}
