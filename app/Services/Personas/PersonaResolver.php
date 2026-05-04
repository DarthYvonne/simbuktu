<?php

namespace App\Services\Personas;

use App\Models\Blueprint;

/**
 * Resolves a persona's dimension/facet picks against the live blueprint.
 *
 * Stored persona keeps the IDs (dimension_id + facet_id). Text, weight,
 * show_on_profile and personality_block are read live from the blueprint
 * each time — so editing a facet in the admin updates every persona that
 * picked it, and deleted facets simply drop off the persona.
 *
 * Personas without IDs (legacy data generated before stable IDs existed)
 * are returned unchanged and continue using their frozen stored fields.
 */
class PersonaResolver
{
    /** @var array<int, ?Blueprint> */
    private array $blueprintCache = [];

    public function resolve(array $persona, ?int $blueprintId = null): array
    {
        $stored = $persona['dimensions'] ?? [];
        $hasIds = false;
        foreach ($stored as $d) {
            if (!empty($d['facet_id'])) { $hasIds = true; break; }
        }
        if (!$hasIds) {
            return $persona; // legacy persona — keep stored text as-is
        }

        $bpId = $blueprintId ?? ($persona['blueprint_id'] ?? null);
        $blueprint = $this->loadBlueprint($bpId);
        if (!$blueprint) {
            return $persona;
        }

        $paramsById = [];
        foreach ($blueprint->parameters ?? [] as $param) {
            if (!empty($param['id'])) $paramsById[$param['id']] = $param;
        }

        $resolved = [];
        foreach ($stored as $d) {
            $param = $paramsById[$d['dimension_id'] ?? null] ?? null;
            if (!$param) continue;
            $facet = null;
            foreach ($param['facets'] ?? [] as $f) {
                if (($f['id'] ?? null) === ($d['facet_id'] ?? null)) { $facet = $f; break; }
            }
            if (!$facet) continue;
            $resolved[] = [
                'dimension_id'    => $param['id'],
                'facet_id'        => $facet['id'],
                'dimension'       => $param['name'] ?? '',
                'facet'           => $facet['name'] ?? '',
                'text'            => $facet['text'] ?? '',
                'type'            => $param['type'] ?? 'personality',
                'value'           => $d['value'] ?? null, // sticky exact value (rolled at gen time)
                'show_on_profile' => (bool) ($param['show_on_profile'] ?? false),
            ];
        }

        $persona['dimensions'] = $resolved;
        $persona['personality_block'] = $this->buildPersonalityBlock($resolved);
        $persona['demographics'] = array_merge(
            $persona['demographics'] ?? [],
            $this->buildDemographicsMap($resolved),
        );
        return $persona;
    }

    /**
     * Back-compat view of sampled demographic dimensions as a flat map keyed
     * by Danish dimension name (alder, køn, region…) — and additionally by
     * the legacy field names many views still read.
     */
    public function buildDemographicsMap(array $resolvedDimensions): array
    {
        $aliases = [
            'alder'     => 'age',
            'køn'       => 'gender',
            'kon'       => 'gender',
            'region'    => 'region',
            'bytype'    => 'city_type',
            'uddannelse'=> 'education',
            'herkomst'  => 'heritage',
            'familie'   => 'family',
            'erhverv'   => 'occupation_hint',
            'indkomst'  => 'income_bracket',
        ];
        $out = [];
        foreach ($resolvedDimensions as $r) {
            if (($r['type'] ?? 'personality') !== 'demographic') continue;
            $key = mb_strtolower(trim($r['dimension'] ?? ''));
            if ($key === '') continue;
            $val = $this->displayValue($r);
            if ($val === '') $val = null;
            $out[$key] = $val;
            if (isset($aliases[$key])) $out[$aliases[$key]] = $val;
        }
        return $out;
    }

    public function buildPersonalityBlock(array $resolvedDimensions): string
    {
        $lines = [];
        foreach ($resolvedDimensions as $r) {
            if (($r['type'] ?? 'personality') !== 'personality') continue;
            $text = trim((string) ($r['text'] ?? ''));
            if ($text !== '') $lines[] = $text;
        }
        return $lines ? '- ' . implode("\n- ", $lines) : '';
    }

    /** Bullet list of demographic-typed dimensions: "- Alder: 14\n- Region: Hovedstaden" */
    public function buildAttributesBlock(array $resolvedDimensions): string
    {
        $lines = [];
        foreach ($resolvedDimensions as $r) {
            if (($r['type'] ?? 'personality') !== 'demographic') continue;
            $dim = trim((string) ($r['dimension'] ?? ''));
            $val = $this->displayValue($r);
            if ($dim !== '' && $val !== '') $lines[] = "{$dim}: {$val}";
        }
        return $lines ? '- ' . implode("\n- ", $lines) : '';
    }

    /** Persona-stored exact value if set, else the live facet name. */
    public function displayValue(array $resolvedDimension): string
    {
        $v = $resolvedDimension['value'] ?? null;
        if ($v !== null && $v !== '') return (string) $v;
        return trim((string) ($resolvedDimension['facet'] ?? ''));
    }

    private function loadBlueprint(?int $id): ?Blueprint
    {
        if ($id === null) return null;
        if (!array_key_exists($id, $this->blueprintCache)) {
            $this->blueprintCache[$id] = Blueprint::find($id);
        }
        return $this->blueprintCache[$id];
    }
}
