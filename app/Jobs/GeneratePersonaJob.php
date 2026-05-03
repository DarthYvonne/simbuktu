<?php

namespace App\Jobs;

use App\Models\Persona;
use App\Models\Population;
use App\Services\Llm\GeminiClient;
use App\Services\Personas\AttributeSampler;
use App\Services\Personas\ImageGenerator;
use App\Services\Personas\NarrativeBuilder;
use App\Services\Personas\Thumbnails;
use App\Services\PromptRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;
use Illuminate\Support\Facades\Cache;

class GeneratePersonaJob implements ShouldQueue
{
    use FoundationQueueable;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(
        public bool $skipImage = false,
        public ?int $populationId = null,
    ) {}

    public function handle(): void
    {
        $population = $this->populationId ? Population::find($this->populationId) : null;

        $config = $population ? $population->resolvedConfig() : config('personas');
        $imageDir = $population ? $population->imagePath() : config('personas.image_path');

        $sampler = new AttributeSampler($config);
        $gemini  = new GeminiClient(config('gemini'));

        $prompts = new PromptRepository();
        if ($population?->narrative_prompt) {
            $prompts = $prompts->withOverride('persona.narrative', $population->narrative_prompt);
        }

        $narrator = new NarrativeBuilder($gemini, $prompts);
        $imager   = new ImageGenerator($gemini, $prompts);

        $cachePrefix = $this->populationId ? "personas:gen:{$this->populationId}" : 'personas:gen';

        $persona = $sampler->sample();
        try {
            $persona = array_merge($persona, $narrator->build($persona));
        } catch (\Throwable $e) {
            Cache::increment("{$cachePrefix}:errors");
            throw $e;
        }

        if (!$this->skipImage && !empty($persona['image_prompt'])) {
            if (!is_dir($imageDir)) mkdir($imageDir, 0775, true);
            $outputPath = "{$imageDir}/{$persona['id']}.png";
            $ok = $imager->generate($persona['image_prompt'], $outputPath, $persona['id']);
            $persona['image_file'] = $ok ? "{$persona['id']}.png" : null;
            // Regenerate thumb using population-scoped dir
            if ($ok) Thumbnails::path($persona['id'], 128, $imageDir);
        }

        Persona::create([
            'id'           => $persona['id'],
            'population_id' => $this->populationId,
            'name'         => $persona['name'],
            'bio'          => $persona['bio'] ?? null,
            'image_file'   => $persona['image_file'] ?? null,
            'age'          => $persona['demographics']['age'],
            'gender'       => $persona['demographics']['gender'],
            'region'       => $persona['demographics']['region'],
            'party'        => $persona['politics']['party'],
            'subcultures'  => $persona['subcultures'],
            'persona_data' => $persona,
        ]);

        Cache::increment("{$cachePrefix}:done");
    }

    public function failed(\Throwable $e): void
    {
        $cachePrefix = $this->populationId ? "personas:gen:{$this->populationId}" : 'personas:gen';
        Cache::increment("{$cachePrefix}:errors");
    }
}
