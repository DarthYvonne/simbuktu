<?php

namespace App\Services\Personas;

use App\Services\Llm\GeminiClient;
use App\Services\PromptRepository;

class NarrativeBuilder
{
    public function __construct(private GeminiClient $gemini, private ?PromptRepository $prompts = null)
    {
        $this->prompts ??= new PromptRepository();
    }

    public function build(array $persona): array
    {
        $prompt = $this->prompt($persona);

        $schema = [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'bio' => ['type' => 'string'],
                'narrative' => ['type' => 'string'],
                'older_posts' => [
                    'type' => 'array',
                    'minItems' => 3,
                    'maxItems' => 5,
                    'items' => ['type' => 'string'],
                ],
                'image_prompt' => ['type' => 'string'],
            ],
            'required' => ['name', 'bio', 'narrative', 'older_posts', 'image_prompt'],
        ];

        $json = $this->gemini->generateText($prompt, config('gemini.narrative_model'), $schema, [
            'prompt_key' => 'persona.narrative',
            'persona_id' => $persona['id'] ?? null,
        ]);
        return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }

    private function prompt(array $p): string
    {
        $d = $p['demographics'] ?? [];
        return $this->prompts->render('persona.narrative', [
            'age'               => $d['age']             ?? '',
            'gender'            => $d['gender']          ?? '',
            'region'            => $d['region']          ?? '',
            'city_type'         => $d['city_type']       ?? '',
            'education'         => $d['education']       ?? '',
            'occupation_hint'   => $d['occupation_hint'] ?? '',
            'income_bracket'    => $d['income_bracket']  ?? '',
            'family'            => $d['family']          ?? '',
            'heritage'          => $d['heritage']        ?? '',
            'personality_block' => $p['personality_block'] ?? '',
        ]);
    }
}
