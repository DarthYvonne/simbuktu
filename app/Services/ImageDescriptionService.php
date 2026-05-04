<?php

namespace App\Services;

use App\Services\BlueprintPrompts;
use App\Services\Llm\GeminiClient;
use App\Services\PromptRepository;
use Illuminate\Support\Facades\Log;

class ImageDescriptionService
{
    public function describe(string $absolutePath, ?int $courseId = null, ?int $blueprintId = null): ?string
    {
        try {
            $gemini = new GeminiClient(config('gemini'));
            $repo = BlueprintPrompts::overlay(app(PromptRepository::class), $blueprintId);
            $prompt = $repo->body('image.describe_post');
            $context = ['prompt_key' => 'image.describe_post'];
            if ($courseId !== null) $context['course_id'] = $courseId;
            return trim($gemini->describeImage($absolutePath, $prompt, null, $context));
        } catch (\Throwable $e) {
            Log::warning("Image description failed: {$e->getMessage()}");
            return null;
        }
    }
}
