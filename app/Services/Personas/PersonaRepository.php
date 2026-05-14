<?php

namespace App\Services\Personas;

use App\Models\Persona;
use App\Models\Population;
use Illuminate\Support\Collection;

class PersonaRepository
{
    private PersonaResolver $resolver;

    public function __construct(private ?int $populationId = null, ?PersonaResolver $resolver = null)
    {
        $this->resolver = $resolver ?? new PersonaResolver();
    }

    public function forPopulation(Population $population): static
    {
        return new static($population->id, $this->resolver);
    }

    private function query(): \Illuminate\Database\Eloquent\Builder
    {
        $q = Persona::query();
        if ($this->populationId !== null) {
            $q->where('population_id', $this->populationId);
        }
        return $q;
    }

    public function all(): array
    {
        return $this->query()->get()
            ->map(fn ($p) => $this->resolver->resolve($p->toFullArray()))
            ->all();
    }

    public function find(string $id): ?array
    {
        $p = Persona::find($id);
        return $p ? $this->resolver->resolve($p->toFullArray()) : null;
    }

    public function filter(string $q = ''): Collection
    {
        $personas = $this->query()->get()->map(fn ($p) => $this->resolver->resolve($p->toFullArray()));

        if ($q === '') return $personas;

        $needle = mb_strtolower(trim($q));
        return $personas->filter(function (array $p) use ($needle) {
            $haystack = mb_strtolower(implode(' ', array_filter([
                $p['name']      ?? '',
                $p['bio']       ?? '',
                $p['narrative'] ?? '',
                $p['demographics']['occupation_hint'] ?? '',
                $p['demographics']['region']          ?? '',
                $p['demographics']['family']          ?? '',
                collect($p['dimensions'] ?? [])
                    ->map(fn ($d) => trim(($d['dimension'] ?? '').' '.($d['facet'] ?? '').' '.($d['text'] ?? '')))
                    ->implode(' '),
            ])));
            return $haystack !== '' && mb_strpos($haystack, $needle) !== false;
        })->values();
    }
}
