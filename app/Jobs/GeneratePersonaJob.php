<?php

namespace App\Jobs;

use App\Models\Blueprint;
use App\Models\Persona;
use App\Models\Population;
use App\Services\Llm\GeminiClient;
use App\Services\Personas\BlueprintFacetSampler;
use App\Services\Personas\DemographicsSampler;
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

    public bool $skipImage = false;
    public ?int $populationId = null;
    public ?int $blueprintId = null;

    public function __construct(bool $skipImage = false, ?int $populationId = null, ?int $blueprintId = null)
    {
        $this->skipImage    = $skipImage;
        $this->populationId = $populationId;
        $this->blueprintId  = $blueprintId;
    }

    public function handle(): void
    {
        $population = $this->populationId ? Population::find($this->populationId) : null;
        $blueprint  = $this->blueprintId  ? Blueprint::find($this->blueprintId)   : null;

        $config   = $population ? $population->resolvedConfig() : config('personas');
        $imageDir = $population ? $population->imagePath() : config('personas.image_path');

        $demographics = (new DemographicsSampler($config))->sample();

        $sampledFacets = [];
        $personalityBlock = '';
        if ($blueprint) {
            $facetSampler     = new BlueprintFacetSampler($blueprint);
            $sampledFacets    = $facetSampler->sample();
            $personalityBlock = $facetSampler->renderBlock($sampledFacets);
        }

        $prompts = new PromptRepository();
        if ($population?->narrative_prompt) {
            $prompts = $prompts->withOverride('persona.narrative', $population->narrative_prompt);
        }

        $gemini   = new GeminiClient(config('gemini'));
        $narrator = new NarrativeBuilder($gemini, $prompts);
        $imager   = new ImageGenerator($gemini, $prompts);

        $cachePrefix = $this->populationId ? "personas:gen:{$this->populationId}" : 'personas:gen';

        try {
            $narrative = $narrator->build([
                'id'                => $demographics['id'],
                'demographics'      => $demographics,
                'personality_block' => $personalityBlock,
            ]);
        } catch (\Throwable $e) {
            Cache::increment("{$cachePrefix}:errors");
            throw $e;
        }

        $imageFile = null;
        if (!$this->skipImage && !empty($narrative['image_prompt'])) {
            if (!is_dir($imageDir)) mkdir($imageDir, 0775, true);
            $outputPath = "{$imageDir}/{$demographics['id']}.png";
            $ok = $imager->generate($narrative['image_prompt'], $outputPath, $demographics['id']);
            $imageFile = $ok ? "{$demographics['id']}.png" : null;
            if ($ok) Thumbnails::path($demographics['id'], 128, $imageDir);
        }

        $personaData = [
            'id'                  => $demographics['id'],
            'demographics'        => $demographics,
            'personality_block'   => $personalityBlock,
            'dimensions'          => $sampledFacets,
            'name'                => $narrative['name']                ?? null,
            'bio'                 => $narrative['bio']                 ?? null,
            'narrative'           => $narrative['narrative']           ?? null,
            'inner_contradiction' => $narrative['inner_contradiction'] ?? null,
            'older_posts'         => $narrative['older_posts']         ?? [],
            'image_prompt'        => $narrative['image_prompt']        ?? null,
            'image_file'          => $imageFile,
        ];

        Persona::create([
            'id'            => $demographics['id'],
            'population_id' => $this->populationId,
            'blueprint_id'  => $this->blueprintId,
            'name'          => $narrative['name'] ?? 'Unavngivet',
            'bio'           => $narrative['bio'] ?? null,
            'image_file'    => $imageFile,
            'age'           => $demographics['age'],
            'gender'        => $demographics['gender'],
            'region'        => $demographics['region'],
            'persona_data'  => $personaData,
        ]);

        Cache::increment("{$cachePrefix}:done");
    }

    public function failed(\Throwable $e): void
    {
        $cachePrefix = $this->populationId ? "personas:gen:{$this->populationId}" : 'personas:gen';
        Cache::increment("{$cachePrefix}:errors");
    }
}
