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

    public function generate(string $personaImagePrompt, string $outputPath, ?string $personaId = null): bool
    {
        $fullPrompt = $this->prompts->render('image.profile', [
            'persona_image_prompt' => $personaImagePrompt,
        ]);

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
}
