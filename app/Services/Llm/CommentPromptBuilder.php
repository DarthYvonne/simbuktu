<?php

namespace App\Services\Llm;

use App\Services\PromptRepository;

class CommentPromptBuilder
{
    public function __construct(private ?PromptRepository $prompts = null)
    {
        $this->prompts ??= new PromptRepository();
    }

    public function build(array $persona, string $postText, array $existingComments = [], ?array $replyTo = null, ?string $imageDescription = null, ?array $linkPreview = null, ?\App\Models\Course $course = null): string
    {
        $d = $persona['demographics'] ?? [];

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
            'age'                 => $d['age']             ?? '',
            'occupation_hint'     => $d['occupation_hint'] ?? '',
            'region'              => $d['region']          ?? '',
            'narrative'           => $persona['narrative'] ?? '',
            'inner_contradiction' => $persona['inner_contradiction'] ?? '',
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
        // Length target derived from coarse cues in the personality block. Without typed
        // fields, we read keywords from the rendered text to bias toward shorter or longer
        // comments. Falls back to a moderate range.
        $block = mb_strtolower($persona['personality_block'] ?? '');

        [$min, $max] = [10, 40];
        if (str_contains($block, 'troll'))                                { $min = 2;  $max = 18;  }
        if (str_contains($block, 'undvigende'))                           { $min = 4;  $max = 20;  }
        if (str_contains($block, 'passiv-aggressiv'))                     { $min = 8;  $max = 35;  }
        if (str_contains($block, 'direkte konfronterende'))               { $min = 12; $max = 50;  }
        if (str_contains($block, 'saglig-insisterende'))                  { $min = 40; $max = 180; }
        if (str_contains($block, 'shitstorm'))                            { $max = (int) ($max * 1.4); }

        if (str_contains($block, 'akademisk'))                            { $max = (int) ($max * 1.3); }
        if (str_contains($block, 'emoji') || str_contains($block, 'versaler') || str_contains($block, 'slang')) {
            $max = (int) ($max * 0.6);
        }

        if ($isReply) { $max = max($min + 2, (int) ($max * 0.7)); }

        $target = random_int($min, max($min, $max));
        return $target <= 5 ? "{$target} (meget kort)" : (string) $target;
    }
}
