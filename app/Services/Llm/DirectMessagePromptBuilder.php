<?php

namespace App\Services\Llm;

use App\Services\BlueprintPrompts;
use App\Services\Personas\PersonaResolver;
use App\Services\PromptRepository;

class DirectMessagePromptBuilder
{
    public function __construct(private ?PromptRepository $prompts = null, private ?PersonaResolver $resolver = null)
    {
        $this->prompts ??= new PromptRepository();
        $this->resolver ??= new PersonaResolver();
    }

    public function build(array $persona, array $history, string $newMessage, string $senderName, ?string $currentContext = null, string $activityContext = ''): string
    {
        $historyText = '';
        foreach ($history as $m) {
            $who = $m['role'] === 'persona' ? $persona['name'] : $senderName;
            $historyText .= "{$who}: \"{$m['body']}\"\n";
        }
        if ($historyText === '') {
            $historyText = "(ingen tidligere beskeder — dette er den første besked)\n";
        }

        $prompts = BlueprintPrompts::overlay($this->prompts, $persona['blueprint_id'] ?? null);
        return $prompts->render('persona.dm', [
            'persona_name'        => $persona['name'] ?? '',
            'attributes_block'    => $this->resolver->buildAttributesBlock($persona['dimensions'] ?? []),
            'narrative'           => $persona['narrative'] ?? '',
            'personality_block'   => $persona['personality_block'] ?? '',
            'sender_name'         => $senderName,
            'history'             => $historyText,
            'new_message'         => $newMessage,
            'current_context'     => $currentContext ?? '',
            'activity_context'    => $activityContext,
        ]);
    }
}
