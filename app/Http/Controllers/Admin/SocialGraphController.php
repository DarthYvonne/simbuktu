<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BuildSocialGraphJob;
use App\Models\Friendship;
use App\Models\Population;
use App\Services\Personas\PersonaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SocialGraphController extends Controller
{
    public function __construct(private PersonaRepository $repo)
    {
    }

    public function generate(Population $population)
    {
        $repo         = $this->repo->forPopulation($population);
        $personaCount = count($repo->all());
        $edgeCount    = Friendship::count();
        $runId        = session('graph_run_id');
        $progress     = $runId ? Cache::get("graph:run:{$runId}") : null;
        $stats        = $runId ? Cache::get("graph:stats:{$runId}") : null;

        return view('admin.personas.graph_generate', compact(
            'population', 'edgeCount', 'personaCount', 'progress', 'stats'
        ));
    }

    public function build(Request $request, Population $population)
    {
        $params = [
            'base_friend_count'  => (int)   $request->input('base_friend_count', 80),
            'min_friends'        => (int)   $request->input('min_friends', 15),
            'max_friends'        => (int)   $request->input('max_friends', 200),
            'bridge_percentage'  => (float) $request->input('bridge_percentage', 3),
            'political_weight'   => (float) $request->input('political_weight', 0.08),
            'subculture_weight'  => (float) $request->input('subculture_weight', 0.25),
            'personality_weight' => (float) $request->input('personality_weight', 0.25),
            'demographics_weight'=> (float) $request->input('demographics_weight', 0.30),
            'heritage_weight'    => (float) $request->input('heritage_weight', 0.07),
            'bridge_bonus'       => (float) $request->input('bridge_bonus', 0.10),
            'noise_percentage'   => (float) $request->input('noise_percentage', 7),
        ];

        $runId = (string) Str::uuid();
        session(['graph_run_id' => $runId]);
        BuildSocialGraphJob::dispatch($runId, $params, $population->id);
        return redirect("/simulation/admin/populations/{$population->id}/personas/graph");
    }

    public function status(Population $population)
    {
        $runId = session('graph_run_id');
        if (!$runId) return response()->json(['phase' => 'idle']);
        $progress = Cache::get("graph:run:{$runId}", ['phase' => 'idle']);
        $stats    = Cache::get("graph:stats:{$runId}");
        return response()->json([
            'progress' => $progress,
            'stats'    => $stats,
            'edges'    => Friendship::count(),
        ]);
    }

    public function view(Population $population)
    {
        $repo = $this->repo->forPopulation($population);
        return view('admin.personas.graph_view', [
            'population'   => $population,
            'edgeCount'    => Friendship::count(),
            'personaCount' => count($repo->all()),
        ]);
    }

    public function data(Population $population)
    {
        $repo     = $this->repo->forPopulation($population);
        $personas = $repo->all();
        $byId     = [];
        foreach ($personas as $p) $byId[$p['id']] = $p;

        $friendships = Friendship::select(['persona_id_1', 'persona_id_2'])->get();

        $colorMap = [];
        $palette  = ['#1877f2','#22c55e','#e11d48','#f59e0b','#7c3aed','#06b6d4','#ec4899','#10b981','#f97316','#8b5cf6','#64748b','#eab308','#3b82f6','#ef4444','#14b8a6'];
        foreach ($personas as $p) {
            $sub = $p['subcultures'][0] ?? 'mainstream';
            if (!isset($colorMap[$sub])) $colorMap[$sub] = $palette[count($colorMap) % count($palette)];
        }

        $nodes = [];
        foreach ($personas as $p) {
            $sub    = $p['subcultures'][0] ?? 'mainstream';
            $nodes[] = [
                'id'         => $p['id'],
                'label'      => $p['name'] ?? 'Ukendt',
                'image'      => !empty($p['image_file']) ? url("/simulation/admin/populations/{$population->id}/personas/{$p['id']}/thumb") : null,
                'color'      => $colorMap[$sub],
                'subculture' => $sub,
                'age'        => $p['demographics']['age'] ?? null,
                'url'        => url("/simulation/admin/populations/{$population->id}/personas/{$p['id']}"),
            ];
        }

        $edges = [];
        foreach ($friendships as $i => $f) {
            if (!isset($byId[$f->persona_id_1]) || !isset($byId[$f->persona_id_2])) continue;
            $edges[] = ['id' => 'e'.$i, 'source' => $f->persona_id_1, 'target' => $f->persona_id_2];
        }

        return response()->json(['nodes' => $nodes, 'edges' => $edges, 'legend' => $colorMap]);
    }
}
