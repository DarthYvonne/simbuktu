<?php

namespace App\Services\Personas;

use Illuminate\Support\Str;

/**
 * Samples a persona's demographic distribution (age, gender, region, city_type, education, heritage)
 * plus trivial derived fields (income_bracket, occupation_hint, family) used for narrative realism.
 *
 * No personality, no causality across attributes — those live in the Personlighedsstruktur (blueprint).
 */
class DemographicsSampler
{
    public function __construct(private array $config)
    {
    }

    public function sample(): array
    {
        $age       = $this->sampleAge();
        $gender    = $this->weighted($this->config['demographics']['gender']);
        $region    = $this->weighted($this->config['demographics']['region']);
        $cityType  = $this->weighted($this->config['demographics']['city_type']);
        $education = $this->weighted($this->config['demographics']['education']);
        $heritage  = $this->weighted($this->config['demographics']['heritage']);

        return [
            'id'              => (string) Str::uuid(),
            'age'             => $age,
            'gender'          => $gender,
            'region'          => $region,
            'city_type'       => $cityType,
            'education'       => $education,
            'heritage'        => $heritage,
            'income_bracket'  => $this->incomeFor($age, $education),
            'occupation_hint' => $this->occupationFor($age, $education),
            'family'          => $this->familyFor($age),
        ];
    }

    private function sampleAge(): int
    {
        $brackets = $this->config['demographics']['age_brackets'];
        $weights = array_column($brackets, 'weight');
        $idx = $this->weightedIndex($weights);
        [$min, $max] = $brackets[$idx]['range'];
        return random_int($min, $max);
    }

    private function incomeFor(int $age, string $education): string
    {
        if ($age < 22) return 'lavt (studerende/lærling)';
        if ($age >= 67) return 'pensionist';
        return match ($education) {
            'lang videregående'         => $this->weighted(['middel' => 30, 'højt' => 60, 'meget højt' => 10]),
            'mellemlang videregående'   => $this->weighted(['middel' => 60, 'højt' => 35, 'meget højt' => 5]),
            'erhvervsuddannelse'        => $this->weighted(['lavt' => 15, 'middel' => 65, 'højt' => 20]),
            default                     => $this->weighted(['lavt' => 40, 'middel' => 50, 'højt' => 10]),
        };
    }

    private function occupationFor(int $age, string $education): string
    {
        if ($age < 20) return 'gymnasie/lærling';
        if ($age >= 67) return 'pensionist';
        return match ($education) {
            'folkeskole'                => 'ufaglært/SOSU/lager',
            'gymnasial'                 => 'studerende/butik',
            'erhvervsuddannelse'        => 'håndværker/SOSU/butik/transport',
            'kort videregående'         => 'tekniker/laborant',
            'mellemlang videregående'   => 'lærer/sygeplejerske/pædagog/socialrådgiver',
            'lang videregående'         => 'akademiker (jurist/økonom/ingeniør/forsker)',
            default                     => 'arbejder',
        };
    }

    private function familyFor(int $age): string
    {
        if ($age < 23) return 'bor evt. hjemme/single';
        if ($age < 30) return $this->weighted(['single' => 50, 'kæreste' => 35, 'samboende' => 15]);
        if ($age < 50) return $this->weighted(['gift m. børn' => 45, 'samboende m. børn' => 20, 'fraskilt' => 15, 'single' => 10, 'gift uden børn' => 10]);
        if ($age < 67) return $this->weighted(['gift voksne børn' => 40, 'fraskilt' => 25, 'samboende' => 15, 'single' => 20]);
        return $this->weighted(['gift pensionist' => 50, 'enke/enkemand' => 25, 'fraskilt' => 15, 'single' => 10]);
    }

    private function weighted(array $weights): string
    {
        $keys = array_keys($weights);
        $w = array_values($weights);
        return $keys[$this->weightedIndex($w)];
    }

    private function weightedIndex(array $weights): int
    {
        $sum = array_sum($weights);
        $r = mt_rand(1, max(1, (int) $sum));
        $acc = 0;
        foreach ($weights as $i => $w) {
            $acc += $w;
            if ($r <= $acc) return $i;
        }
        return count($weights) - 1;
    }
}
