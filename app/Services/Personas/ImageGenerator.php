<?php

namespace App\Services\Personas;

use App\Services\Llm\GeminiClient;
use App\Services\PromptRepository;
use App\Services\Personas\Thumbnails;

class ImageGenerator
{
    public function __construct(private GeminiClient $gemini, private ?PromptRepository $prompts = null)
    {
        $this->prompts ??= new PromptRepository();
    }

    public function generate(string $personaImagePrompt, string $outputPath, ?string $personaId = null, ?int $age = null, ?string $gender = null): bool
    {
        // Force demographic facts into the image prompt so the LLM-generated
        // description can't accidentally drop them (e.g. 17-year-old rendered
        // as a 40-year-old because the narrative call wrote "young person").
        $subject = $this->buildSubjectLine($age, $gender);

        $fullPrompt = $this->prompts->render('image.profile', [
            'persona_image_prompt' => $personaImagePrompt,
            'persona_age'    => $age !== null ? (string) $age : '',
            'persona_gender' => $gender ?? '',
            'subject_line'   => $subject,
        ]);

        // Backward-compat: if the template (e.g. a personality's cached override
        // from before {{subject_line}} existed) didn't include the subject line,
        // prepend it so age/gender always reach the image model.
        if ($subject !== '' && mb_strpos($fullPrompt, $subject) === false) {
            $fullPrompt = $subject . ' ' . $fullPrompt;
        }

        try {
            $bytes = $this->gemini->generateImage($fullPrompt, null, [
                'prompt_key' => 'image.profile',
                'persona_id' => $personaId ?? pathinfo($outputPath, PATHINFO_FILENAME),
            ]);
            if (!is_dir(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0775, true);
            }
            file_put_contents($outputPath, $bytes);
            // Generate small thumbnail immediately for graph/list views
            $personaId = pathinfo($outputPath, PATHINFO_FILENAME);
            Thumbnails::path($personaId, 128);
            return true;
        } catch (\Throwable $e) {
            logger()->warning("Image generation failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Build an English subject line emphasising the persona's age + gender
     * so the image generator can't drift to a default adult.
     */
    private function buildSubjectLine(?int $age, ?string $gender): string
    {
        if ($age === null && $gender === null) return '';
        $g = match (mb_strtolower((string) $gender)) {
            'mand', 'm', 'male'   => 'man',
            'kvinde', 'k', 'female' => 'woman',
            default => 'person',
        };
        if ($age === null) return ucfirst($g) . '.';
        // Use the most descriptive bracket — image models latch onto these phrases.
        $bracket = match (true) {
            $age <= 12 => "Child, exactly {$age} years old",
            $age <= 17 => "Teenager, exactly {$age} years old, clearly under 18",
            $age <= 25 => "Young adult, exactly {$age} years old",
            $age <= 40 => "Adult, exactly {$age} years old",
            $age <= 60 => "Middle-aged adult, exactly {$age} years old",
            default     => "Senior, exactly {$age} years old",
        };
        return "{$bracket}, {$g}. Age-appropriate face and body.";
    }
}
