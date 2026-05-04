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

    public function filter(string $q = '', ?string $region = null, ?string $ageBucket = null): Collection
    {
        $query = $this->query();

        if ($q !== '') $query->where('name', 'like', "%{$q}%");
        if ($region)   $query->where('region', $region);
        if ($ageBucket) {
            [$min, $max] = array_map('intval', explode('-', $ageBucket));
            $query->whereBetween('age', [$min, $max]);
        }

        return $query->get()->map(fn ($p) => $this->resolver->resolve($p->toFullArray()));
    }
}
