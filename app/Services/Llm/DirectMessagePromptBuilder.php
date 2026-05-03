<?php

namespace App\Services\Llm;

use App\Services\PromptRepository;

class DirectMessagePromptBuilder
{
    public function __construct(private ?PromptRepository $prompts = null)
    {
        $this->prompts ??= new PromptRepository();
    }

    /**
     * @param array $persona Full persona array from repo
     * @param array $history List of ['role' => 'user'|'persona', 'body' => string]
     * @param string $newMessage The latest user message
     * @param string $senderName The sending user's display name
     * @param string|null $currentContext Optional newsroom context
     */
    public function build(array $persona, array $history, string $newMessage, string $senderName, ?string $currentContext = null, string $activityContext = ''): string
    {
        $d = $persona['demographics'];
        $bf = $persona['personality']['big_five'];

        $historyText = '';
        foreach ($history as $m) {
            $who = $m['role'] === 'persona' ? $persona['name'] : $senderName;
            $historyText .= "{$who}: \"{$m['body']}\"\n";
        }
        if ($historyText === '') {
            $historyText = "(ingen tidligere beskeder — dette er den første besked)\n";
        }

        return $this->prompts->render('persona.dm', [
            'persona_name' => $persona['name'],
            'age' => $d['age'],
            'occupation_hint' => $d['occupation_hint'],
            'region' => $d['region'],
            'narrative' => $persona['narrative'] ?? '',
            'inner_contradiction' => $persona['inner_contradiction'] ?? '',
            'party' => $persona['politics']['party'] ?? '',
            'value_axis' => $persona['politics']['value_axis'] ?? '',
            'economic_axis' => $persona['politics']['economic_axis'] ?? '',
            'big_five_O' => $bf['O'] ?? 0,
            'big_five_C' => $bf['C'] ?? 0,
            'big_five_E' => $bf['E'] ?? 0,
            'big_five_A' => $bf['A'] ?? 0,
            'big_five_N' => $bf['N'] ?? 0,
            'conflict_style' => $persona['personality']['conflict_style'] ?? '',
            'subcultures' => implode(', ', $persona['subcultures'] ?? []),
            'triggers' => implode(', ', $persona['triggers'] ?? []),
            'register' => $persona['language']['register'] ?? '',
            'spelling_quality' => $persona['language']['spelling_quality'] ?? '',
            'tone' => $persona['some_behavior']['tone'] ?? '',
            'sender_name' => $senderName,
            'history' => $historyText,
            'new_message' => $newMessage,
            'current_context' => $currentContext ?? '',
            'activity_context' => $activityContext,
        ]);
    }
}
