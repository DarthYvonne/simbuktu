<?php

namespace App\Services\Simulation;

use App\Services\Personas\PersonaRepository;

class TriggerResolver
{
    public function __construct(private PersonaRepository $personas)
    {
    }

    /**
     * Pick N personas for this round's random discovery pool. Trigger-relevance
     * is no longer decided here — each persona decides inside the batched LLM
     * call whether the post actually resonates with them.
     *
     * Picks N rows in SQL with ORDER BY RANDOM() LIMIT N, scoped to the post's
     * population. Avoids loading every persona into PHP memory.
     */
    public function pickExposures(int $n, array $alreadyExposedIds, ?int $populationId = null): array
    {
        return $this->personas->pickRandom($n, $alreadyExposedIds, $populationId);
    }
}
