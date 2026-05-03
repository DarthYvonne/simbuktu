<?php

namespace App\Services\Personas;

use App\Models\Persona;
use App\Models\Population;
use Illuminate\Support\Collection;

class PersonaRepository
{
    public function __construct(private ?int $populationId = null)
    {
    }

    public function forPopulation(Population $population): static
    {
        return new static($population->id);
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
        return $this->query()->get()->map->toFullArray()->all();
    }

    public function find(string $id): ?array
    {
        return Persona::find($id)?->toFullArray();
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

        return $query->get()->map->toFullArray();
    }
}
