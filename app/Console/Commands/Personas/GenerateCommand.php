<?php

namespace App\Console\Commands\Personas;

use App\Services\Llm\GeminiClient;
use App\Services\Personas\AttributeSampler;
use App\Services\Personas\ImageGenerator;
use App\Services\Personas\NarrativeBuilder;
use Illuminate\Console\Command;

class GenerateCommand extends Command
{
    protected $signature = 'personas:generate {count=10 : antal personas} {--no-images : spring billede-generering over}';
    protected $description = 'Generér N personas med attributter, narrativ via Gemini, og profilbillede.';

    public function handle(): int
    {
        $count = (int) $this->argument('count');
        $skipImages = (bool) $this->option('no-images');

        if (!config('gemini.api_key')) {
            $this->error('GEMINI_API_KEY ikke sat i .env');
            return self::FAILURE;
        }

        $sampler = new AttributeSampler(config('personas'));
        $gemini = new GeminiClient(config('gemini'));
        $narrator = new NarrativeBuilder($gemini);
        $imager = new ImageGenerator($gemini);

        $outputDir = config('personas.output_path');
        $imageDir = config('personas.image_path');
        if (!is_dir($outputDir)) mkdir($outputDir, 0775, true);
        if (!is_dir($imageDir)) mkdir($imageDir, 0775, true);

        $personas = [];
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            $persona = $sampler->sample();
            try {
                $narrative = $narrator->build($persona);
                $persona = array_merge($persona, $narrative);
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("Narrative fail #{$i}: {$e->getMessage()}");
                continue;
            }

            if (!$skipImages && !empty($persona['image_prompt'])) {
                $imgPath = "{$imageDir}/{$persona['id']}.png";
                $ok = $imager->generate($persona['image_prompt'], $imgPath);
                $persona['image_file'] = $ok ? "{$persona['id']}.png" : null;
            }

            $personas[] = $persona;
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $jsonPath = "{$outputDir}/personas.json";
        file_put_contents($jsonPath, json_encode($personas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Genereret " . count($personas) . " personas → {$jsonPath}");
        $this->info("Billeder → {$imageDir}");
        $this->info("Inspicér: php artisan serve, og åbn /personas/inspect");

        return self::SUCCESS;
    }
}
