<?php

namespace App\Services\Personas;

use App\Models\PersonalityRule;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves a persona into a list of applicable personality-rule texts.
 *
 * Hybrid policy:
 *  - Categorical attributes (party, conflict_style, register, tone, engagement,
 *    education, authoritarianism): always included if the persona has the value.
 *  - Scale attributes (Big Five, value_axis, economic_axis): only when loud
 *    (Big Five ≥8 or ≤3, axes |value| ≥ 3).
 *  - Subcultures: one rule per subculture the persona is part of.
 */
class PersonalityRuleRenderer
{
    /** Ordered list of active rule texts for this persona. */
    public function rules(array $persona): array
    {
        $resolved = [];

        // age bucket
        $age = (int) ($persona['demographics']['age'] ?? 0);
        $resolved[] = ['age_bucket', $this->ageBucket($age)];

        // education (always)
        if ($edu = $persona['demographics']['education'] ?? null) {
            $resolved[] = ['education', $edu];
        }

        // Big Five (loud only)
        $bf = $persona['personality']['big_five'] ?? [];
        foreach (['O' => 'openness', 'C' => 'conscientiousness', 'E' => 'extraversion', 'A' => 'agreeableness', 'N' => 'neuroticism'] as $k => $attr) {
            $score = (int) ($bf[$k] ?? 5);
            if ($score >= 8) $resolved[] = [$attr, 'høj'];
            elseif ($score <= 3) $resolved[] = [$attr, 'lav'];
        }

        if ($auth = $persona['personality']['authoritarianism'] ?? null) {
            $resolved[] = ['authoritarianism', $auth];
        }

        if ($cs = $persona['personality']['conflict_style'] ?? null) {
            $resolved[] = ['conflict_style', $cs];
        }

        if ($party = $persona['politics']['party'] ?? null) {
            $resolved[] = ['party', $party];
        }

        $va = (int) ($persona['politics']['value_axis'] ?? 0);
        if ($va >= 3) $resolved[] = ['value_axis', 'konservativ'];
        elseif ($va <= -3) $resolved[] = ['value_axis', 'progressiv'];

        $ea = (int) ($persona['politics']['economic_axis'] ?? 0);
        if ($ea >= 3) $resolved[] = ['economic_axis', 'liberal'];
        elseif ($ea <= -3) $resolved[] = ['economic_axis', 'socialistisk'];

        if ($el = $persona['politics']['engagement_level'] ?? null) {
            $resolved[] = ['engagement_level', $el];
        }

        if ($reg = $persona['language']['register'] ?? null) {
            $resolved[] = ['register', $reg];
        }

        if ($tone = $persona['some_behavior']['tone'] ?? null) {
            $resolved[] = ['tone', $tone];
        }

        foreach (($persona['subcultures'] ?? []) as $sub) {
            $resolved[] = ['subculture', $sub];
        }

        return $this->lookup($resolved);
    }

    /** Render the rules as a single Danish bullet-list block. */
    public function render(array $persona): string
    {
        $rules = $this->rules($persona);
        if (empty($rules)) return '';
        return '- ' . implode("\n- ", $rules);
    }

    /** @param array<int, array{0:string,1:string}> $pairs */
    private function lookup(array $pairs): array
    {
        $map = $this->ruleMap();
        $out = [];
        foreach ($pairs as [$attr, $val]) {
            $key = $attr.'::'.$val;
            if (isset($map[$key])) $out[] = $map[$key];
        }
        return $out;
    }

    /** Cached map of attribute::value → rule_text, active only. */
    private function ruleMap(): array
    {
        return Cache::remember('personality_rule_map', 300, function () {
            $rows = PersonalityRule::where('is_active', true)
                ->whereNotNull('rule_text')
                ->where('rule_text', '!=', '')
                ->get(['attribute', 'value', 'rule_text']);
            $map = [];
            foreach ($rows as $r) {
                $map[$r->attribute.'::'.$r->value] = trim($r->rule_text);
            }
            return $map;
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('personality_rule_map');
    }

    private function ageBucket(int $age): string
    {
        if ($age < 25) return 'under_25';
        if ($age < 40) return '25_39';
        if ($age < 60) return '40_59';
        return '60_plus';
    }
}
