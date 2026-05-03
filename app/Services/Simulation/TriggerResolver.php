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
     */
    public function pickExposures(int $n, array $alreadyExposedIds): array
    {
        if ($n <= 0) return [];
        $all = collect($this->personas->all())->whereNotIn('id', $alreadyExposedIds)->values();
        if ($all->isEmpty()) return [];
        return $all->shuffle()->take($n)->all();
    }
}
