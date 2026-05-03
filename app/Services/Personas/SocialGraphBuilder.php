<?php

namespace App\Services\Personas;

use App\Models\Friendship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SocialGraphBuilder
{
    public function __construct(private PersonaRepository $repo)
    {
    }

    /**
     * Rebuild the entire graph from scratch.
     */
    public function build(array $params = [], ?string $progressKey = null): array
    {
        $params = array_merge([
            'base_friend_count' => 80,
            'min_friends' => 15,
            'max_friends' => 200,
            'bridge_percentage' => 3,       // % of personas tagged as bridges
            'political_weight' => 0.08,     // weak
            'subculture_weight' => 0.25,
            'personality_weight' => 0.25,
            'demographics_weight' => 0.30,
            'heritage_weight' => 0.07,
            'bridge_bonus' => 0.10,         // added to similarity if either end is a bridge
            'noise_percentage' => 7,        // % of edges that are random
        ], $params);

        $personas = $this->repo->all();
        $n = count($personas);
        if ($n < 2) return ['error' => 'Need at least 2 personas', 'edges' => 0];

        $setProgress = fn (string $phase, int $done, int $total) => $progressKey
            ? Cache::put($progressKey, ['phase' => $phase, 'done' => $done, 'total' => $total], 3600)
            : null;

        // Wipe existing friendships for this population's personas only
        $setProgress('clearing', 0, 1);
        $allIds = array_column($personas, 'id');
        if (!empty($allIds)) {
            DB::table('friendships')
                ->whereIn('persona_id_1', $allIds)
                ->orWhereIn('persona_id_2', $allIds)
                ->delete();
        }

        // Index personas
        $ids = array_map(fn ($p) => $p['id'], $personas);
        $byId = [];
        foreach ($personas as $p) $byId[$p['id']] = $p;

        // Compute target friend counts + bridge tags
        $targets = [];
        $bridges = [];
        $bridgeThreshold = $this->bridgeThreshold($personas, $params['bridge_percentage']);
        foreach ($personas as $p) {
            $targets[$p['id']] = $this->targetFriendCount($p, $params);
            $bridges[$p['id']] = $this->isBridge($p, $bridgeThreshold);
        }

        // Compute all pair similarities
        $pairs = [];
        $pairCount = $n * ($n - 1) / 2;
        $computed = 0;
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $a = $personas[$i]; $b = $personas[$j];
                $sim = $this->similarity($a, $b, $params);
                if ($bridges[$a['id']] || $bridges[$b['id']]) $sim += $params['bridge_bonus'];
                $pairs[] = [$a['id'], $b['id'], min(1.0, $sim)];
                $computed++;
                if ($computed % 50000 === 0) $setProgress('scoring', $computed, (int) $pairCount);
            }
        }

        // Sort descending by similarity
        $setProgress('sorting', 0, 1);
        usort($pairs, fn ($x, $y) => $y[2] <=> $x[2]);

        // Iterate and add edges where both sides accept + have quota
        $remaining = $targets;
        $edges = [];
        $edgeCount = 0;

        $setProgress('connecting', 0, count($pairs));
        foreach ($pairs as $i => [$a, $b, $sim]) {
            if ($remaining[$a] <= 0 || $remaining[$b] <= 0) { continue; }
            if (!$this->acceptsRequest($byId[$a], $byId[$b], $sim, $remaining[$a], $targets[$a])) continue;
            if (!$this->acceptsRequest($byId[$b], $byId[$a], $sim, $remaining[$b], $targets[$b])) continue;
            $edges[] = [$a, $b, $sim];
            $remaining[$a]--;
            $remaining[$b]--;
            $edgeCount++;
            if ($i % 10000 === 0) $setProgress('connecting', $i, count($pairs));
        }

        // Add noise — random edges bridging clusters
        $noiseTarget = (int) round($edgeCount * $params['noise_percentage'] / 100);
        $noiseAdded = 0;
        $attempts = 0;
        $existingKey = [];
        foreach ($edges as [$a, $b]) $existingKey["$a|$b"] = true;
        while ($noiseAdded < $noiseTarget && $attempts < $noiseTarget * 10) {
            $attempts++;
            $a = $ids[array_rand($ids)];
            $b = $ids[array_rand($ids)];
            if ($a === $b) continue;
            if ($a > $b) [$a, $b] = [$b, $a];
            if (isset($existingKey["$a|$b"])) continue;
            if ($remaining[$a] <= 0 || $remaining[$b] <= 0) continue;
            $edges[] = [$a, $b, 0.0];
            $existingKey["$a|$b"] = true;
            $remaining[$a]--;
            $remaining[$b]--;
            $noiseAdded++;
        }

        // Bulk insert
        $setProgress('inserting', 0, count($edges));
        $now = now()->toDateTimeString();
        $chunks = array_chunk($edges, 1000);
        $inserted = 0;
        foreach ($chunks as $chunk) {
            $rows = array_map(fn ($e) => [
                'persona_id_1' => $e[0],
                'persona_id_2' => $e[1],
                'similarity' => $e[2],
                'created_at' => $now,
            ], $chunk);
            DB::table('friendships')->insert($rows);
            $inserted += count($chunk);
            $setProgress('inserting', $inserted, count($edges));
        }

        $setProgress('done', count($edges), count($edges));

        return [
            'personas' => $n,
            'edges' => count($edges),
            'noise_edges' => $noiseAdded,
            'bridges' => array_sum(array_map(fn ($b) => $b ? 1 : 0, $bridges)),
            'avg_friends' => $n > 0 ? round(count($edges) * 2 / $n, 1) : 0,
            'loners' => count(array_filter($remaining, fn ($r, $id) => $r === $targets[$id], ARRAY_FILTER_USE_BOTH)),
        ];
    }

    public function addPersonaToGraph(array $persona): int
    {
        $params = [
            'base_friend_count' => 80, 'min_friends' => 15, 'max_friends' => 200,
            'bridge_percentage' => 3,
            'political_weight' => 0.08, 'subculture_weight' => 0.25,
            'personality_weight' => 0.25, 'demographics_weight' => 0.30,
            'heritage_weight' => 0.07, 'bridge_bonus' => 0.10,
        ];
        $others = array_filter($this->repo->all(), fn ($p) => $p['id'] !== $persona['id']);
        if (empty($others)) return 0;

        $target = $this->targetFriendCount($persona, $params);
        $bridgeThreshold = $this->bridgeThreshold($this->repo->all(), $params['bridge_percentage']);
        $isBridge = $this->isBridge($persona, $bridgeThreshold);

        $scored = [];
        foreach ($others as $o) {
            $sim = $this->similarity($persona, $o, $params);
            if ($isBridge || $this->isBridge($o, $bridgeThreshold)) $sim += $params['bridge_bonus'];
            $scored[] = [$o, min(1.0, $sim)];
        }
        usort($scored, fn ($x, $y) => $y[1] <=> $x[1]);

        $connected = 0;
        foreach ($scored as [$o, $sim]) {
            if ($connected >= $target) break;
            if (!$this->acceptsRequest($persona, $o, $sim, $target - $connected, $target)) continue;
            $existing = Friendship::friendIdsOf($o['id']);
            $oTarget = $this->targetFriendCount($o, $params);
            if (count($existing) >= $oTarget) continue;
            if (!$this->acceptsRequest($o, $persona, $sim, $oTarget - count($existing), $oTarget)) continue;
            Friendship::create([
                'persona_id_1' => $persona['id'],
                'persona_id_2' => $o['id'],
                'similarity' => $sim,
            ]);
            $connected++;
        }
        return $connected;
    }

    private function targetFriendCount(array $p, array $params): int
    {
        $bf = $p['personality']['big_five'];
        $base = $params['base_friend_count'];
        $bonusE = ($bf['E'] - 5) * 8;
        $bonusA = ($bf['A'] - 5) * 5;
        $bonusO = ($bf['O'] - 5) * 3;
        $penaltyN = ($bf['N'] - 5) * -4;
        $noise = random_int(-15, 15);
        return max($params['min_friends'], min($params['max_friends'], (int) round($base + $bonusE + $bonusA + $bonusO + $penaltyN + $noise)));
    }

    private function bridgeThreshold(array $personas, float $pct): float
    {
        // Bridge score = O value + centrality of politics (|value_axis| low) + subculture count
        $scores = [];
        foreach ($personas as $p) {
            $scores[] = $this->bridgeScore($p);
        }
        if (empty($scores)) return 999;
        rsort($scores);
        $idx = max(1, (int) floor(count($scores) * $pct / 100)) - 1;
        return $scores[$idx];
    }

    private function bridgeScore(array $p): float
    {
        $bf = $p['personality']['big_five'];
        $score = $bf['O'] * 1.0; // high openness
        $score += (5 - abs(($p['politics']['value_axis'] ?? 0))) * 0.5; // central politics
        $score += count($p['subcultures'] ?? []) * 1.2; // multiple subcultures
        return $score;
    }

    private function isBridge(array $p, float $threshold): bool
    {
        return $this->bridgeScore($p) >= $threshold;
    }

    private function similarity(array $a, array $b, array $params): float
    {
        // Demographics
        $ageSim = 1 - min(abs($a['demographics']['age'] - $b['demographics']['age']) / 30, 1);
        $eduMap = ['folkeskole' => 1, 'gymnasial' => 2, 'erhvervsuddannelse' => 2, 'kort videregående' => 3, 'mellemlang videregående' => 4, 'lang videregående' => 5];
        $eA = $eduMap[$a['demographics']['education']] ?? 2;
        $eB = $eduMap[$b['demographics']['education']] ?? 2;
        $eduSim = 1 - abs($eA - $eB) / 4;
        $regionSim = $a['demographics']['region'] === $b['demographics']['region'] ? 1 : 0;
        $demographics = ($ageSim * 0.5 + $eduSim * 0.3 + $regionSim * 0.2);

        // Subculture overlap
        $shared = array_intersect($a['subcultures'] ?? [], $b['subcultures'] ?? []);
        $subcultureSim = min(1, count($shared) * 0.5);

        // Political (weak)
        $vA = $a['politics']['value_axis'] ?? 0;
        $vB = $b['politics']['value_axis'] ?? 0;
        $eA2 = $a['politics']['economic_axis'] ?? 0;
        $eB2 = $b['politics']['economic_axis'] ?? 0;
        $politicalSim = (1 - abs($vA - $vB) / 10) * 0.6 + (1 - abs($eA2 - $eB2) / 10) * 0.4;

        // Big Five homophily on A, E, O
        $bfA = $a['personality']['big_five'];
        $bfB = $b['personality']['big_five'];
        $personalitySim = (
            (1 - abs($bfA['A'] - $bfB['A']) / 9) +
            (1 - abs($bfA['E'] - $bfB['E']) / 9) +
            (1 - abs($bfA['O'] - $bfB['O']) / 9)
        ) / 3;

        // Heritage
        $heritageSim = $a['demographics']['heritage'] === $b['demographics']['heritage'] ? 1 : 0.3;

        return $demographics * $params['demographics_weight']
            + $subcultureSim * $params['subculture_weight']
            + $politicalSim * $params['political_weight']
            + $personalitySim * $params['personality_weight']
            + $heritageSim * $params['heritage_weight'];
    }

    /**
     * A decides whether to accept B's friend request (or reach out).
     * High E + A = accepts more; running out of quota = accepts less.
     */
    private function acceptsRequest(array $self, array $other, float $similarity, int $remaining, int $target): bool
    {
        $bf = $self['personality']['big_five'];
        $base = 0.5;
        $base += (($bf['E'] - 5) / 5) * 0.3;
        $base += (($bf['A'] - 5) / 5) * 0.2;
        $base += $similarity * 0.4; // high similarity pulls acceptance up
        $base *= max(0.2, $remaining / max(1, $target)); // running out of room → less open
        return mt_rand(0, 100) / 100 < min(1.0, max(0.02, $base));
    }
}
