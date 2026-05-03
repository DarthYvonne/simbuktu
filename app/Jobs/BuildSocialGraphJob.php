<?php

namespace App\Jobs;

use App\Services\Personas\PersonaRepository;
use App\Services\Personas\SocialGraphBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class BuildSocialGraphJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 900;

    public function __construct(
        public string $runId,
        public array $params,
        public ?int $populationId = null,
    ) {}

    public function handle(): void
    {
        $repo    = new PersonaRepository($this->populationId);
        $builder = new SocialGraphBuilder($repo);

        Cache::put("graph:run:{$this->runId}", ['phase' => 'starting', 'done' => 0, 'total' => 1], 3600);
        try {
            $stats = $builder->build($this->params, "graph:run:{$this->runId}");
            Cache::put("graph:stats:{$this->runId}", $stats, 3600);
            Cache::put("graph:run:{$this->runId}", ['phase' => 'done', 'done' => 1, 'total' => 1], 3600);
        } catch (\Throwable $e) {
            Cache::put("graph:run:{$this->runId}", ['phase' => 'error', 'error' => $e->getMessage(), 'done' => 0, 'total' => 1], 3600);
            throw $e;
        }
    }
}
