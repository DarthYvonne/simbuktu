<?php

namespace App\Services\Llm;

use App\Services\PromptRepository;

class DirectMessagePromptBuilder
{
    public function __construct(private ?PromptRepository $prompts = null)
    {
        $this->prompts ??= new PromptRepository();
    }

    public function build(array $persona, array $history, string $newMessage, string $senderName, ?string $currentContext = null, string $activityContext = ''): string
    {
        $d = $persona['demographics'] ?? [];

        $historyText = '';
        foreach ($history as $m) {
            $who = $m['role'] === 'persona' ? $persona['name'] : $senderName;
            $historyText .= "{$who}: \"{$m['body']}\"\n";
        }
        if ($historyText === '') {
            $historyText = "(ingen tidligere beskeder — dette er den første besked)\n";
        }

        return $this->prompts->render('persona.dm', [
            'persona_name'        => $persona['name'] ?? '',
            'age'                 => $d['age']             ?? '',
            'occupation_hint'     => $d['occupation_hint'] ?? '',
            'region'              => $d['region']          ?? '',
            'narrative'           => $persona['narrative'] ?? '',
            'inner_contradiction' => $persona['inner_contradiction'] ?? '',
            'personality_block'   => $persona['personality_block'] ?? '',
            'sender_name'         => $senderName,
            'history'             => $historyText,
            'new_message'         => $newMessage,
            'current_context'     => $currentContext ?? '',
            'activity_context'    => $activityContext,
        ]);
    }
}
