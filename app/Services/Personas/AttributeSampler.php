<?php

namespace App\Services\Personas;

use Illuminate\Support\Str;

class AttributeSampler
{
    public function __construct(private array $config)
    {
    }

    public function sample(): array
    {
        $age = $this->sampleAge();
        $gender = $this->weighted($this->config['demographics']['gender']);
        $region = $this->weighted($this->config['demographics']['region']);
        $cityType = $this->weighted($this->config['demographics']['city_type']);
        $education = $this->sampleEducation($age);
        $heritage = $this->weighted($this->config['demographics']['heritage']);

        $bigFive = $this->sampleBigFive();
        $authoritarianism = $this->sampleAuthoritarianism($age, $education);
        $subcultures = $this->sampleSubcultures($age, $gender, $education, $region);
        $this->modulatePersonality($subcultures, $bigFive);

        $party = $this->sampleParty($age, $education, $region, $subcultures);
        $valueAxis = $this->sampleValueAxis($age, $education, $authoritarianism, $heritage);
        $economicAxis = $this->sampleEconomicAxis($education, $party);

        $conflictStyle = $this->sampleConflictStyle($bigFive, $subcultures);
        $languageRegister = $this->sampleLanguageRegister($education, $age);
        $triggers = $this->sampleTriggers($subcultures, $valueAxis, 2 + random_int(0, 2));

        $postFrequency = $this->sampleEnum(['lurker', 'sjælden poster', 'ugentlig poster', 'daglig poster'], [25, 35, 25, 15]);
        $tone = $this->sampleEnum(
            ['passiv scroller', 'liker', 'kommenterer pænt', 'debattør', 'troll', 'shitstorm-deltager'],
            [20, 25, 25, 20, 5, 5]
        );

        return [
            'id' => (string) Str::uuid(),
            'demographics' => [
                'age' => $age,
                'gender' => $gender,
                'region' => $region,
                'city_type' => $cityType,
                'education' => $education,
                'heritage' => $heritage,
                'income_bracket' => $this->sampleIncome($age, $education),
                'occupation_hint' => $this->occupationHint($age, $education, $gender),
                'family' => $this->sampleFamily($age),
            ],
            'personality' => [
                'big_five' => $bigFive,
                'authoritarianism' => $authoritarianism,
                'conflict_style' => $conflictStyle,
            ],
            'politics' => [
                'party' => $party,
                'economic_axis' => $economicAxis,
                'value_axis' => $valueAxis,
                'engagement_level' => $this->sampleEnum(['apatisk', 'lav', 'middel', 'høj', 'aktivist'], [20, 35, 30, 12, 3]),
            ],
            'subcultures' => $subcultures,
            'some_behavior' => [
                'post_frequency' => $postFrequency,
                'tone' => $tone,
                'sharing' => $this->sampleEnum(['deler sjældent', 'deler nyheder', 'deler oprørt', 'deler alt'], [40, 30, 20, 10]),
            ],
            'triggers' => $triggers,
            'language' => [
                'register' => $languageRegister,
                'spelling_quality' => $this->sampleSpellingQuality($education),
            ],
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

    private function sampleEducation(int $age): string
    {
        $base = $this->config['demographics']['education'];
        // Younger people: still in study, skew lower
        if ($age < 22) {
            $base['folkeskole'] *= 2;
            $base['gymnasial'] *= 3;
            $base['lang videregående'] *= 0.1;
            $base['mellemlang videregående'] *= 0.3;
        }
        if ($age >= 65) {
            $base['folkeskole'] *= 1.8;
            $base['lang videregående'] *= 0.6;
        }
        return $this->weighted($base);
    }

    private function sampleBigFive(): array
    {
        return [
            'O' => $this->normalScore(),
            'C' => $this->normalScore(),
            'E' => $this->normalScore(),
            'A' => $this->normalScore(),
            'N' => $this->normalScore(),
        ];
    }

    private function normalScore(): int
    {
        // crude normal-ish dist 1-10, mean ~5.5
        $r = (random_int(0, 100) + random_int(0, 100) + random_int(0, 100)) / 3;
        return max(1, min(10, (int) round($r / 10)));
    }

    private function sampleAuthoritarianism(int $age, string $education): string
    {
        $weights = ['lav' => 33, 'middel' => 34, 'høj' => 33];
        if (in_array($education, ['lang videregående', 'mellemlang videregående'])) {
            $weights['lav'] += 20;
            $weights['høj'] -= 15;
        }
        if ($age >= 60) {
            $weights['høj'] += 15;
        }
        return $this->weighted($weights);
    }

    private function sampleSubcultures(int $age, string $gender, string $education, string $region): array
    {
        $src = $this->config['subcultures'];
        $weights = array_is_list($src) ? array_fill_keys($src, 5) : $src;

        $bump = function (string $key, int $delta) use (&$weights) {
            if (array_key_exists($key, $weights)) {
                $weights[$key] = max(0, ($weights[$key] ?? 0) + $delta);
            }
        };
        $set = function (string $key, int $value) use (&$weights) {
            if (array_key_exists($key, $weights)) $weights[$key] = $value;
        };

        if ($age < 30) {
            $bump('gaming', 15); $bump('LGBTQ+-aktivisme', 8); $bump('klima-aktivisme', 10);
            $bump('fitness/biohacking', 8); $bump('crypto/finans', 6);
            $set('boomer-Facebook', 0); $set('foreningsdanmark', 1);
        }
        if ($age >= 55) {
            $bump('boomer-Facebook', 25); $bump('jæger/friluft', 6);
            $bump('foreningsdanmark', 10); $bump('kristen frikirke', 3);
            $set('gaming', 1); $set('LGBTQ+-aktivisme', 2); $set('techbro', 1);
        }
        if ($gender === 'mand' && $age < 35) {
            $bump('manosfære/redpill', 8); $bump('fitness/biohacking', 8);
            $bump('gaming', 10); $bump('crypto/finans', 6); $bump('techbro', 5);
        }
        if ($gender === 'kvinde') {
            $bump('wellness/spiritualitet', 10); $bump('feministisk', 8);
            $set('manosfære/redpill', 0);
        }
        if ($education === 'erhvervsuddannelse') {
            $bump('håndværkerkultur', 15); $bump('motorcykelmiljø', 5);
        }
        if (in_array($education, ['lang videregående', 'mellemlang videregående'])) {
            $bump('akademisk venstrefløj', 10); $bump('kulturelite', 8);
        }
        if ($region === 'Hovedstaden') {
            $bump('Christiania-miljø', 5); $bump('kulturelite', 6);
        }

        $weights = array_filter($weights, fn ($w) => $w > 0);
        if (empty($weights)) return [];

        $picked = [];
        $count = min(random_int(1, 2), count($weights));
        for ($i = 0; $i < $count; $i++) {
            $name = $this->weighted($weights);
            if (in_array($name, $picked)) continue;
            $picked[] = $name;
            $weights[$name] = 0;
        }

        if (random_int(1, 100) <= 7 && !empty($picked)) {
            $allKeys = array_keys(array_is_list($src) ? array_flip($src) : $src);
            if (!empty($allKeys)) {
                $picked[array_rand($picked)] = $allKeys[array_rand($allKeys)];
                $picked = array_unique($picked);
            }
        }

        return array_values($picked);
    }

    private function modulatePersonality(array $subcultures, array &$bigFive): void
    {
        foreach ($subcultures as $sub) {
            match ($sub) {
                'manosfære/redpill' => [$bigFive['A'] = max(1, $bigFive['A'] - 2), $bigFive['N'] = min(10, $bigFive['N'] + 1)],
                'klima-aktivisme' => [$bigFive['O'] = min(10, $bigFive['O'] + 2), $bigFive['A'] = min(10, $bigFive['A'] + 1)],
                'troll', 'antifeministisk' => $bigFive['A'] = max(1, $bigFive['A'] - 2),
                'akademisk venstrefløj' => $bigFive['O'] = min(10, $bigFive['O'] + 2),
                'kristen frikirke' => $bigFive['C'] = min(10, $bigFive['C'] + 1),
                default => null,
            };
        }
    }

    private function sampleParty(int $age, string $education, string $region, array $subcultures): string
    {
        if ($age < 18) return 'for ung til at stemme';

        $w = ['A' => 18, 'V' => 14, 'M' => 8, 'SF' => 8, 'DD' => 9, 'LA' => 6, 'K' => 4,
              'EL' => 7, 'RV' => 4, 'DF' => 5, 'NB' => 2, 'Æ' => 4, 'stemmer ikke' => 11];
        if (in_array($education, ['lang videregående', 'mellemlang videregående'])) {
            $w['SF'] += 6; $w['EL'] += 5; $w['RV'] += 6; $w['Æ'] += 4;
            $w['DD'] -= 5; $w['DF'] -= 3;
        }
        if ($education === 'erhvervsuddannelse') {
            $w['DD'] += 6; $w['DF'] += 3; $w['V'] += 3;
            $w['EL'] -= 3; $w['RV'] -= 2;
        }
        if ($region === 'Hovedstaden') {
            $w['SF'] += 3; $w['EL'] += 4; $w['Æ'] += 3;
        }
        if (in_array($region, ['Syddanmark', 'Midtjylland', 'Nordjylland'])) {
            $w['V'] += 3; $w['DD'] += 2;
        }
        if (in_array('manosfære/redpill', $subcultures) || in_array('Trump-sympati (DK)', $subcultures)) {
            $w['DD'] += 8; $w['NB'] += 6;
        }
        if (in_array('klima-aktivisme', $subcultures)) {
            $w['SF'] += 6; $w['EL'] += 5; $w['Æ'] += 8;
        }
        if (in_array('akademisk venstrefløj', $subcultures)) {
            $w['EL'] += 8; $w['SF'] += 4;
        }
        if ($age < 25) {
            $w['stemmer ikke'] += 8;
        }
        return $this->weighted(array_filter($w, fn ($v) => $v > 0));
    }

    private function sampleValueAxis(int $age, string $education, string $authoritarianism, string $heritage): int
    {
        $base = 0;
        if (in_array($education, ['lang videregående', 'mellemlang videregående'])) {
            $base -= 2;
        }
        if ($education === 'erhvervsuddannelse' || $education === 'folkeskole') {
            $base += 2;
        }
        if ($age >= 60) {
            $base += 2;
        }
        if ($authoritarianism === 'høj') {
            $base += 3;
        }
        if ($authoritarianism === 'lav') {
            $base -= 3;
        }
        if ($heritage !== 'dansk') {
            $base -= 2;
        }
        return max(-5, min(5, $base + random_int(-2, 2)));
    }

    private function sampleEconomicAxis(string $education, string $party): int
    {
        $byParty = ['EL' => -4, 'SF' => -3, 'A' => -2, 'Æ' => -1, 'RV' => 0, 'M' => 1,
                    'V' => 2, 'K' => 2, 'LA' => 4, 'NB' => 3, 'DD' => 1, 'DF' => 0, 'stemmer ikke' => 0];
        $base = $byParty[$party] ?? 0;
        return max(-5, min(5, $base + random_int(-1, 1)));
    }

    private function sampleConflictStyle(array $bigFive, array $subcultures): string
    {
        $styles = $this->config['conflict_styles'];
        $w = array_fill_keys($styles, 10);
        if ($bigFive['A'] <= 3) { $w['direkte konfronterende'] += 15; $w['trollende'] += 8; }
        if ($bigFive['A'] >= 8) { $w['undvigende'] += 10; }
        if ($bigFive['N'] >= 7) { $w['passiv-aggressiv'] += 8; }
        if (in_array('akademisk venstrefløj', $subcultures)) { $w['saglig-insisterende'] += 12; }
        if (in_array('manosfære/redpill', $subcultures)) { $w['trollende'] += 10; $w['direkte konfronterende'] += 8; }
        return $this->weighted($w);
    }

    private function sampleLanguageRegister(string $education, int $age): string
    {
        $w = ['akademisk' => 5, 'dagligdags' => 30, 'slang-tungt' => 8, 'dialektfarvet' => 8, 'emoji-rigt' => 10, 'VERSALER' => 3];
        if (in_array($education, ['lang videregående', 'mellemlang videregående'])) {
            $w['akademisk'] += 25;
        }
        if ($age < 25) {
            $w['slang-tungt'] += 15; $w['emoji-rigt'] += 10;
        }
        if ($age >= 60) {
            $w['VERSALER'] += 10; $w['dagligdags'] += 10;
        }
        return $this->weighted($w);
    }

    private function sampleSpellingQuality(string $education): string
    {
        return match ($education) {
            'lang videregående', 'mellemlang videregående' => 'fejlfrit',
            'kort videregående', 'gymnasial' => 'få fejl',
            'erhvervsuddannelse' => 'nogle fejl',
            default => 'mange fejl',
        };
    }

    private function sampleTriggers(array $subcultures, int $valueAxis, int $count): array
    {
        $all = $this->config['trigger_topics'];
        $w = array_fill_keys($all, 5);
        if ($valueAxis > 1) {
            foreach (['indvandring', 'islam', 'bandekriminalitet', 'woke', 'kongehuset'] as $t) { $w[$t] += 8; }
        }
        if ($valueAxis < -1) {
            foreach (['klima', 'metoo', 'kønsidentitet', 'akademikere'] as $t) { $w[$t] += 8; }
        }
        foreach ($subcultures as $sub) {
            match ($sub) {
                'manosfære/redpill' => array_walk($w, fn (&$v, $k) => $v += in_array($k, ['metoo', 'kønsidentitet', 'maskulinitet', 'woke']) ? 10 : 0),
                'klima-aktivisme' => $w['klima'] += 15,
                'håndværkerkultur' => $w['akademikere'] += 8,
                'landbrugsmiljø' => $w['landbruget'] += 12,
                'jæger/friluft' => $w['dyrevelfærd'] += 8,
                'muslimsk miljø' => array_walk($w, fn (&$v, $k) => $v += in_array($k, ['Israel/Palæstina', 'islam']) ? 10 : 0),
                default => null,
            };
        }
        $picked = [];
        for ($i = 0; $i < $count; $i++) {
            $t = $this->weighted($w);
            if (!in_array($t, $picked)) { $picked[] = $t; $w[$t] = 0; }
        }
        return $picked;
    }

    private function sampleIncome(int $age, string $education): string
    {
        if ($age < 22) return 'lavt (studerende/lærling)';
        if ($age >= 67) return 'pensionist';
        return match ($education) {
            'lang videregående' => $this->weighted(['middel' => 30, 'højt' => 60, 'meget højt' => 10]),
            'mellemlang videregående' => $this->weighted(['middel' => 60, 'højt' => 35, 'meget højt' => 5]),
            'erhvervsuddannelse' => $this->weighted(['lavt' => 15, 'middel' => 65, 'højt' => 20]),
            default => $this->weighted(['lavt' => 40, 'middel' => 50, 'højt' => 10]),
        };
    }

    private function occupationHint(int $age, string $education, string $gender): string
    {
        if ($age < 20) return 'gymnasie/lærling';
        if ($age >= 67) return 'pensionist';
        return match ($education) {
            'folkeskole' => 'ufaglært/SOSU/lager',
            'gymnasial' => 'studerende/butik',
            'erhvervsuddannelse' => 'håndværker/SOSU/butik/transport',
            'kort videregående' => 'tekniker/laborant',
            'mellemlang videregående' => 'lærer/sygeplejerske/pædagog/socialrådgiver',
            'lang videregående' => 'akademiker (jurist/økonom/ingeniør/forsker)',
        };
    }

    private function sampleFamily(int $age): string
    {
        if ($age < 23) return 'bor evt. hjemme/single';
        if ($age < 30) return $this->weighted(['single' => 50, 'kæreste' => 35, 'samboende' => 15]);
        if ($age < 50) return $this->weighted(['gift m. børn' => 45, 'samboende m. børn' => 20, 'fraskilt' => 15, 'single' => 10, 'gift uden børn' => 10]);
        if ($age < 67) return $this->weighted(['gift voksne børn' => 40, 'fraskilt' => 25, 'samboende' => 15, 'single' => 20]);
        return $this->weighted(['gift pensionist' => 50, 'enke/enkemand' => 25, 'fraskilt' => 15, 'single' => 10]);
    }

    private function sampleEnum(array $values, array $weights): string
    {
        return $values[$this->weightedIndex($weights)];
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
