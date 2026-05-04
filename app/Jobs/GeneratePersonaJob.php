<?php

namespace App\Jobs;

use App\Models\Blueprint;
use App\Models\Persona;
use App\Models\Population;
use App\Services\Llm\GeminiClient;
use App\Services\Personas\BlueprintFacetSampler;
use App\Services\Personas\ImageGenerator;
use App\Services\Personas\NarrativeBuilder;
use App\Services\Personas\PersonaResolver;
use App\Services\Personas\Thumbnails;
use App\Services\PromptRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

        $imageDir = $population ? $population->imagePath() : config('personas.image_path');
        $personaId = (string) Str::uuid();

        $sampledFacets = [];
        $personalityBlock = '';
        if ($blueprint) {
            $facetSampler     = new BlueprintFacetSampler($blueprint);
            $sampledFacets    = $facetSampler->sample();
            $personalityBlock = $facetSampler->renderBlock($sampledFacets);
        }

        $resolver = new PersonaResolver();
        $attributesBlock = $resolver->buildAttributesBlock($sampledFacets);

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
                'id'                => $personaId,
                'attributes_block'  => $attributesBlock,
                'personality_block' => $personalityBlock,
            ]);
        } catch (\Throwable $e) {
            Cache::increment("{$cachePrefix}:errors");
            throw $e;
        }

        $imageFile = null;
        if (!$this->skipImage && !empty($narrative['image_prompt'])) {
            if (!is_dir($imageDir)) mkdir($imageDir, 0775, true);
            $outputPath = "{$imageDir}/{$personaId}.png";
            $ok = $imager->generate($narrative['image_prompt'], $outputPath, $personaId);
            $imageFile = $ok ? "{$personaId}.png" : null;
            if ($ok) Thumbnails::path($personaId, 128, $imageDir);
        }

        // Derive scalar columns (used for filtering UI) from sampled demographic dimensions, by name.
        $scalars = $this->extractScalarColumns($sampledFacets);

        $personaData = [
            'id'                => $personaId,
            'personality_block' => $personalityBlock,
            'dimensions'        => $sampledFacets,
            'name'              => $narrative['name']         ?? null,
            'bio'               => $narrative['bio']          ?? null,
            'narrative'         => $narrative['narrative']    ?? null,
            'older_posts'       => $narrative['older_posts']  ?? [],
            'image_prompt'      => $narrative['image_prompt'] ?? null,
            'image_file'        => $imageFile,
        ];

        Persona::create([
            'id'            => $personaId,
            'population_id' => $this->populationId,
            'blueprint_id'  => $this->blueprintId,
            'name'          => $narrative['name'] ?? 'Unavngivet',
            'bio'           => $narrative['bio'] ?? null,
            'image_file'    => $imageFile,
            'age'           => $scalars['age'],
            'gender'        => $scalars['gender'],
            'region'        => $scalars['region'],
            'persona_data'  => $personaData,
        ]);

        Cache::increment("{$cachePrefix}:done");
    }

    /**
     * Extract age/gender/region scalar columns from sampled demographic dimensions
     * by matching on dimension name. Returns nulls when the blueprint omits them.
     */
    private function extractScalarColumns(array $sampledFacets): array
    {
        $byDim = [];
        foreach ($sampledFacets as $f) {
            if (($f['type'] ?? 'personality') !== 'demographic') continue;
            $byDim[mb_strtolower(trim($f['dimension'] ?? ''))] = $f['facet'] ?? null;
        }
        $age = null;
        if (!empty($byDim['alder']) && preg_match('/\d+/', $byDim['alder'], $m)) {
            $age = (int) $m[0];
        }
        return [
            'age'    => $age,
            'gender' => $byDim['køn'] ?? $byDim['kon'] ?? null,
            'region' => $byDim['region'] ?? null,
        ];
    }

    public function failed(\Throwable $e): void
    {
        $cachePrefix = $this->populationId ? "personas:gen:{$this->populationId}" : 'personas:gen';
        Cache::increment("{$cachePrefix}:errors");
    }
}
