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
        $personas = $this->repo->filter($q);

        return view('profiler.index', [
            'personas' => $personas,
            'total'    => \App\Models\Persona::count(),
            'q'        => $q,
        ]);
    }

    public function show(string $id)
    {
        $persona = $this->repo->find($id);
        if (!$persona) {
            return response()->view('profiler.deleted', [], 404);
        }

        $friendIds = \App\Models\Friendship::friendIdsOf($id);
        // Resolve only the friends, not every persona in the database.
        $friends = $this->repo->findMany($friendIds);

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
            'personaCount' => \App\Models\Persona::count(),
            'edgeCount'    => \App\Models\Friendship::count(),
        ]);
    }

    public function graphData()
    {
        $personas = $this->repo->all();
        $friendships = \App\Models\Friendship::select(['persona_id_1', 'persona_id_2'])->get();

        // Color personas by their region (simple, demographic-based grouping).
        $colorMap = [];
        $palette = ['#1877f2','#22c55e','#e11d48','#f59e0b','#7c3aed','#06b6d4','#ec4899','#10b981','#f97316','#8b5cf6','#64748b','#eab308','#3b82f6','#ef4444','#14b8a6'];

        $nodes = [];
        foreach ($personas as $p) {
            $region = $p['demographics']['region'] ?? 'Ukendt';
            if (!isset($colorMap[$region])) $colorMap[$region] = $palette[count($colorMap) % count($palette)];
            $nodes[] = [
                'id'     => $p['id'],
                'label'  => $p['name'] ?? 'Ukendt',
                'image'  => !empty($p['image_file']) ? url('/simulation/profiler/'.$p['id'].'/thumb') : null,
                'color'  => $colorMap[$region],
                'region' => $region,
                'age'    => $p['demographics']['age'] ?? null,
                'url'    => url('/simulation/profiler/'.$p['id']),
            ];
        }

        $edges = [];
        foreach ($friendships as $i => $f) {
            $edges[] = ['id' => 'e'.$i, 'source' => $f->persona_id_1, 'target' => $f->persona_id_2];
        }

        return response()->json(['nodes' => $nodes, 'edges' => $edges, 'legend' => $colorMap]);
    }
}
