<?php

namespace App\Http\Controllers;

use App\Services\Personas\PersonaRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfilerController extends Controller
{
    public function __construct(private PersonaRepository $repo)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $sub = $request->query('subculture');
        $party = $request->query('party');
        $region = $request->query('region');
        $ageBucket = $request->query('age');

        $personas = $this->repo->filter($q, $sub, $party, $region, $ageBucket);

        $all = $this->repo->all();
        $subcultures = collect($all)->flatMap(fn ($p) => $p['subcultures'] ?? [])->unique()->sort()->values();
        $parties = collect($all)->pluck('politics.party')->unique()->filter()->sort()->values();
        $regions = collect($all)->pluck('demographics.region')->unique()->filter()->sort()->values();

        return view('profiler.index', [
            'personas' => $personas,
            'total' => count($all),
            'q' => $q,
            'subculture' => $sub,
            'party' => $party,
            'region' => $region,
            'age' => $ageBucket,
            'subcultures' => $subcultures,
            'parties' => $parties,
            'regions' => $regions,
        ]);
    }

    public function show(string $id)
    {
        $persona = $this->repo->find($id);
        abort_unless($persona, 404);

        $friendIds = \App\Models\Friendship::friendIdsOf($id);
        $all = $this->repo->all();
        $byId = [];
        foreach ($all as $p) $byId[$p['id']] = $p;
        $friends = array_values(array_filter(array_map(fn ($fid) => $byId[$fid] ?? null, $friendIds)));

        $cover = app(\App\Services\CoverPicker::class)->pickFor($persona);
        return view('profiler.show', ['p' => $persona, 'friends' => $friends, 'cover' => $cover]);
    }

    public function image(string $id): BinaryFileResponse
    {
        $persona = \App\Models\Persona::find($id);
        abort_unless($persona, 404);
        $dir = $persona->population?->imagePath() ?? config('personas.image_path');
        $path = "{$dir}/{$id}.png";
        abort_unless(is_file($path), 404);
        return response()->file($path);
    }

    public function thumb(string $id): BinaryFileResponse
    {
        $persona = \App\Models\Persona::find($id);
        abort_unless($persona, 404);
        $dir = $persona->population?->imagePath() ?? config('personas.image_path');
        $path = \App\Services\Personas\Thumbnails::path($id, 128, $dir);
        abort_unless($path && is_file($path), 404);
        return response()->file($path, ['Cache-Control' => 'public, max-age=86400']);
    }

    public function graph()
    {
        return view('profiler.graph', [
            'personaCount' => count($this->repo->all()),
            'edgeCount' => \App\Models\Friendship::count(),
        ]);
    }

    public function graphData()
    {
        $personas = $this->repo->all();
        $friendships = \App\Models\Friendship::select(['persona_id_1', 'persona_id_2'])->get();

        $colorMap = [];
        $palette = ['#1877f2','#22c55e','#e11d48','#f59e0b','#7c3aed','#06b6d4','#ec4899','#10b981','#f97316','#8b5cf6','#64748b','#eab308','#3b82f6','#ef4444','#14b8a6'];
        foreach ($personas as $p) {
            $sub = $p['subcultures'][0] ?? 'mainstream';
            if (!isset($colorMap[$sub])) $colorMap[$sub] = $palette[count($colorMap) % count($palette)];
        }

        $nodes = [];
        foreach ($personas as $p) {
            $sub = $p['subcultures'][0] ?? 'mainstream';
            $nodes[] = [
                'id' => $p['id'],
                'label' => $p['name'] ?? 'Ukendt',
                'image' => !empty($p['image_file']) ? url('/slophub/profiler/'.$p['id'].'/thumb') : null,
                'color' => $colorMap[$sub],
                'subculture' => $sub,
                'age' => $p['demographics']['age'] ?? null,
                'url' => url('/slophub/profiler/'.$p['id']),
            ];
        }

        $edges = [];
        foreach ($friendships as $i => $f) {
            $edges[] = ['id' => 'e'.$i, 'source' => $f->persona_id_1, 'target' => $f->persona_id_2];
        }

        return response()->json(['nodes' => $nodes, 'edges' => $edges, 'legend' => $colorMap]);
    }
}
