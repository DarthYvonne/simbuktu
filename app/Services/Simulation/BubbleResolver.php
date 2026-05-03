<?php

namespace App\Services\Simulation;

use App\Services\Personas\PersonaRepository;

class BubbleResolver
{
    public function __construct(private PersonaRepository $personas)
    {
    }

    /**
     * Pick N personas to expose this round, based on which round we're in
     * and the post's algorithm config.
     */
    public function pickExposures(int $round, int $n, array $algoConfig, array $alreadyExposedIds, array $seedSubcultures = []): array
    {
        $all = collect($this->personas->all())->whereNotIn('id', $alreadyExposedIds);

        $tightness = $algoConfig['bubble_tightness'] ?? 0.7;
        $adjacentRound = $algoConfig['adjacent_unlock_round'] ?? 3;
        $opposingRound = $algoConfig['opposing_unlock_round'] ?? 5;

        $allowedSubs = $seedSubcultures;
        if ($round >= $adjacentRound) {
            $allowedSubs = array_merge($allowedSubs, $this->adjacent($seedSubcultures));
        }
        if ($round >= $opposingRound) {
            $allowedSubs = []; // unlock all
        }

        if (!empty($allowedSubs)) {
            $inBubble = $all->filter(fn ($p) => !empty(array_intersect($p['subcultures'] ?? [], $allowedSubs)));
            $outBubble = $all->reject(fn ($p) => !empty(array_intersect($p['subcultures'] ?? [], $allowedSubs)));

            $bubbleQuota = (int) round($n * $tightness);
            $picked = $inBubble->shuffle()->take($bubbleQuota)
                ->merge($outBubble->shuffle()->take($n - $bubbleQuota));
        } else {
            $picked = $all->shuffle()->take($n);
        }

        return $picked->values()->all();
    }

    private function adjacent(array $subs): array
    {
        $map = [
            'manosfære/redpill' => ['antifeministisk', 'fitness/biohacking', 'gaming', 'crypto/finans'],
            'klima-aktivisme' => ['akademisk venstrefløj', 'feministisk', 'LGBTQ+-aktivisme'],
            'håndværkerkultur' => ['boomer-Facebook', 'jæger/friluft', 'motorcykelmiljø'],
            'boomer-Facebook' => ['håndværkerkultur', 'jæger/friluft', 'foreningsdanmark'],
            'akademisk venstrefløj' => ['kulturelite', 'feministisk', 'klima-aktivisme'],
            'gaming' => ['techbro', 'manosfære/redpill', 'crypto/finans'],
            'wellness/spiritualitet' => ['feministisk', 'klima-aktivisme'],
            'muslimsk miljø' => ['kristen frikirke'], // both religious
        ];
        $out = [];
        foreach ($subs as $s) {
            $out = array_merge($out, $map[$s] ?? []);
        }
        return array_unique($out);
    }
}
