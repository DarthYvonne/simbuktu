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
        $d = $persona['demographics'];
        $bf = $persona['personality']['big_five'];

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
            'persona_name' => $persona['name'],
            'age' => $d['age'],
            'occupation_hint' => $d['occupation_hint'],
            'region' => $d['region'],
            'narrative' => $persona['narrative'] ?? '',
            'inner_contradiction' => $persona['inner_contradiction'] ?? '',
            'party' => $persona['politics']['party'],
            'value_axis' => $persona['politics']['value_axis'],
            'economic_axis' => $persona['politics']['economic_axis'],
            'big_five_O' => $bf['O'],
            'big_five_C' => $bf['C'],
            'big_five_E' => $bf['E'],
            'big_five_A' => $bf['A'],
            'big_five_N' => $bf['N'],
            'conflict_style' => $persona['personality']['conflict_style'],
            'subcultures' => implode(', ', $persona['subcultures']),
            'triggers' => implode(', ', $persona['triggers']),
            'register' => $persona['language']['register'],
            'spelling_quality' => $persona['language']['spelling_quality'],
            'tone' => $persona['some_behavior']['tone'],
            'post_text' => $postText,
            'media_context' => $mediaContext,
            'existing_context' => $existingContext,
            'reply_context' => $replyContext,
            'length_target' => $this->targetLength($persona, $replyTo !== null),
            'current_context' => $course ? app(\App\Services\News\NewsContextService::class)->current($course) : '',
        ]);
    }

    private function targetLength(array $persona, bool $isReply): string
    {
        $style = $persona['personality']['conflict_style'] ?? 'undvigende';
        $tone = $persona['some_behavior']['tone'] ?? 'kommenterer pænt';
        $register = $persona['language']['register'] ?? 'dagligdags';

        [$min, $max] = match ($style) {
            'trollende' => [3, 18],
            'undvigende' => [4, 20],
            'passiv-aggressiv' => [8, 35],
            'direkte konfronterende' => [12, 50],
            'saglig-insisterende' => [40, 180],
            default => [10, 40],
        };

        if ($tone === 'liker' || $tone === 'kommenterer pænt') { $min = max(2, (int)($min * 0.4)); $max = (int)($max * 0.5); }
        if ($tone === 'troll') { $min = 2; $max = min($max, 15); }
        if ($tone === 'shitstorm-deltager') { $max = (int)($max * 1.4); }

        if ($register === 'akademisk') { $max = (int)($max * 1.3); }
        if ($register === 'emoji-rigt' || $register === 'VERSALER' || $register === 'slang-tungt') {
            $max = (int)($max * 0.6);
        }

        if ($isReply) { $max = max($min + 2, (int)($max * 0.7)); }

        $target = random_int($min, max($min, $max));
        return $target <= 5 ? "{$target} (meget kort)" : (string) $target;
    }
}
