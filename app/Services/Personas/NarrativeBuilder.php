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
                'inner_contradiction' => ['type' => 'string'],
                'older_posts' => [
                    'type' => 'array',
                    'minItems' => 3,
                    'maxItems' => 5,
                    'items' => ['type' => 'string'],
                ],
                'image_prompt' => ['type' => 'string'],
            ],
            'required' => ['name', 'bio', 'narrative', 'inner_contradiction', 'older_posts', 'image_prompt'],
        ];

        $json = $this->gemini->generateText($prompt, config('gemini.narrative_model'), $schema, [
            'prompt_key' => 'persona.narrative',
            'persona_id' => $persona['id'] ?? null,
        ]);
        return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }

    private function prompt(array $p): string
    {
        $d = $p['demographics'];
        $pol = $p['politics'];
        $bf = $p['personality']['big_five'];

        return $this->prompts->render('persona.narrative', [
            'age' => $d['age'],
            'gender' => $d['gender'],
            'region' => $d['region'],
            'city_type' => $d['city_type'],
            'education' => $d['education'],
            'occupation_hint' => $d['occupation_hint'],
            'income_bracket' => $d['income_bracket'],
            'family' => $d['family'],
            'heritage' => $d['heritage'],
            'party' => $pol['party'],
            'value_axis' => $pol['value_axis'],
            'economic_axis' => $pol['economic_axis'],
            'engagement_level' => $pol['engagement_level'],
            'big_five_O' => $bf['O'],
            'big_five_C' => $bf['C'],
            'big_five_E' => $bf['E'],
            'big_five_A' => $bf['A'],
            'big_five_N' => $bf['N'],
            'authoritarianism' => $p['personality']['authoritarianism'],
            'conflict_style' => $p['personality']['conflict_style'],
            'subcultures' => implode(', ', $p['subcultures']),
            'triggers' => implode(', ', $p['triggers']),
            'register' => $p['language']['register'],
            'spelling_quality' => $p['language']['spelling_quality'],
            'tone' => $p['some_behavior']['tone'],
        ]);
    }
}
