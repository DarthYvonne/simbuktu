<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePersonaJob;
use App\Models\Persona;
use App\Models\Population;
use App\Services\Personas\PersonaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PersonaController extends Controller
{
    public function __construct(private PersonaRepository $repo)
    {
    }

    public function index(Request $request, Population $population)
    {
        $repo  = $this->repo->forPopulation($population);
        $all   = $repo->all();
        $total = count($all);

        $q = trim((string) $request->query('q', ''));

        $personas = $repo->filter($q);

        $genPrefix = "personas:gen:{$population->id}";
        $genQueued = (int) Cache::get("{$genPrefix}:queued", 0);
        $genDone   = (int) Cache::get("{$genPrefix}:done", 0);
        $genErrors = (int) Cache::get("{$genPrefix}:errors", 0);
        $generating = $genQueued > 0 && ($genDone + $genErrors) < $genQueued;

        $course        = \Illuminate\Support\Facades\Auth::user()?->currentCourse();
        $activityCount = 0;
        if ($course) {
            $postIds       = \App\Models\Post::where('course_id', $course->id)->pluck('id');
            $activityCount = \Illuminate\Support\Facades\DB::table('comments')->whereIn('post_id', $postIds)->count()
                           + \Illuminate\Support\Facades\DB::table('post_exposures')->whereIn('post_id', $postIds)->count();
        }

        return view('admin.personas.index', [
            'population'     => $population,
            'allPopulations' => Population::withCount('personas')->orderBy('name')->get(),
            'course'         => $course,
            'courseBlueprint'=> $population->blueprint,
            'activityCount'  => $activityCount,
            'personas'       => $personas,
            'count'          => $personas->count(),
            'total'          => $total,
            'q'              => $q,
            'generating'     => $generating,
            'genQueued'      => $genQueued,
            'genDone'        => $genDone + $genErrors,
        ]);
    }

    public function show(Population $population, string $id)
    {
        $repo    = $this->repo->forPopulation($population);
        $all     = $repo->all();
        $persona = collect($all)->firstWhere('id', $id);
        abort_unless($persona, 404);

        $friendIds = \App\Models\Friendship::friendIdsOf($id);
        $byId = [];
        foreach ($all as $p) $byId[$p['id']] = $p;
        $friends = array_values(array_filter(array_map(fn ($fid) => $byId[$fid] ?? null, $friendIds)));

        $cover = app(\App\Services\CoverPicker::class)->pickFor($persona);
        $personaModel = \App\Models\Persona::find($id);
        $course = \Illuminate\Support\Facades\Auth::user()?->currentCourse();
        $courseBlueprint = $course?->blueprint;

        $samplePost = '[OPSLAGET som personaen ville reagere på indsættes her]';
        $commentPrompt = app(\App\Services\Llm\CommentPromptBuilder::class)
            ->build($persona, $samplePost, [], null, null, null, $course);
        $dmPrompt = app(\App\Services\Llm\DirectMessagePromptBuilder::class)
            ->build($persona, [], '[BESKEDEN fra brugeren indsættes her]', 'Bruger');

        return view('admin.personas.show', [
            'population'      => $population,
            'p'               => $persona,
            'friends'         => $friends,
            'cover'           => $cover,
            'personaBp'       => $personaModel?->blueprint_id,
            'courseBlueprint' => $courseBlueprint,
            'commentPrompt'   => $commentPrompt,
            'dmPrompt'        => $dmPrompt,
        ]);
    }

    public function edit(Population $population, string $id)
    {
        $personaModel = Persona::where('population_id', $population->id)->findOrFail($id);
        $persona      = $this->repo->forPopulation($population)->find($id);
        abort_unless($persona, 404);

        $blueprint = $personaModel->blueprint;
        $paramsById = [];
        foreach ($blueprint?->parameters ?? [] as $param) {
            if (!empty($param['id'])) $paramsById[$param['id']] = $param;
        }

        return view('admin.personas.edit', [
            'population' => $population,
            'p'          => $persona,
            'personaModel' => $personaModel,
            'paramsById' => $paramsById,
        ]);
    }

    public function update(Request $request, Population $population, string $id)
    {
        $personaModel = Persona::where('population_id', $population->id)->findOrFail($id);

        $data = $request->validate([
            'name'      => 'required|string|max:120',
            'bio'       => 'nullable|string|max:500',
            'narrative' => 'nullable|string|max:5000',
            'age'       => 'nullable|integer|min:1|max:120',
            'region'    => 'nullable|string|max:80',
            'facets'    => 'array',
            'facets.*'  => 'string',
        ]);

        $personaData = $personaModel->persona_data ?? [];
        $personaData['name']      = $data['name'];
        $personaData['bio']       = $data['bio'] ?? '';
        $personaData['narrative'] = $data['narrative'] ?? '';

        $blueprint = $personaModel->blueprint;
        $paramsById = [];
        foreach ($blueprint?->parameters ?? [] as $param) {
            if (!empty($param['id'])) $paramsById[$param['id']] = $param;
        }

        $dimensions = $personaData['dimensions'] ?? [];
        $pickedFacets = $data['facets'] ?? [];
        $changedDimensionIds = [];
        foreach ($dimensions as &$dim) {
            $dimId = $dim['dimension_id'] ?? null;
            $newFacetId = $pickedFacets[$dimId] ?? null;
            if (!$dimId || !$newFacetId) continue;
            if (($dim['facet_id'] ?? null) === $newFacetId) continue;
            $param = $paramsById[$dimId] ?? null;
            if (!$param) continue;
            $facet = null;
            foreach ($param['facets'] ?? [] as $f) {
                if (($f['id'] ?? null) === $newFacetId) { $facet = $f; break; }
            }
            if (!$facet) continue;
            $dim['facet_id'] = $facet['id'];
            $dim['facet']    = $facet['name'] ?? '';
            $dim['text']     = $facet['text'] ?? '';
            $dim['value']    = null; // clear sticky exact value — let facet name be the new display
            $changedDimensionIds[] = $dimId;
        }
        unset($dim);
        $personaData['dimensions'] = $dimensions;

        // Drop coherence flags for dimensions the user just edited — they've acted on them.
        if (!empty($personaData['coherence_flags']) && $changedDimensionIds) {
            $personaData['coherence_flags'] = array_values(array_filter(
                $personaData['coherence_flags'],
                fn ($f) => !in_array($f['dimension_id'] ?? null, $changedDimensionIds, true),
            ));
        }

        $personaModel->name = $data['name'];
        $personaModel->bio  = $data['bio'] ?? '';
        if (array_key_exists('age', $data) && $data['age'] !== null) {
            $personaModel->age = $data['age'];
        }
        if (array_key_exists('region', $data) && $data['region'] !== null) {
            $personaModel->region = $data['region'];
        }
        $personaModel->persona_data = $personaData;
        $personaModel->save();

        return redirect("/simulation/admin/populations/{$population->id}/personas/{$id}")
            ->with('success', 'Persona opdateret.');
    }

    public function acceptCoherence(Population $population, string $id)
    {
        $persona = Persona::where('population_id', $population->id)->findOrFail($id);
        $data = $persona->persona_data ?? [];
        $data['coherence_flags'] = [];
        $persona->persona_data = $data;
        $persona->save();
        return back()->with('success', 'Træk accepteret.');
    }

    public function image(Population $population, string $id): BinaryFileResponse
    {
        $path = $population->imagePath() . "/{$id}.png";
        abort_unless(is_file($path), 404);
        return response()->file($path);
    }

    public function thumb(Population $population, string $id): BinaryFileResponse
    {
        $path = \App\Services\Personas\Thumbnails::path($id, 128, $population->imagePath());
        abort_unless($path && is_file($path), 404);
        return response()->file($path, ['Cache-Control' => 'public, max-age=86400']);
    }

    public function generate(Request $request, Population $population)
    {
        $count      = max(1, min(50, (int) $request->input('count', 10)));
        $skipImages = (bool) $request->input('skip_images', false);

        if (!config('gemini.api_key')) {
            return back()->with('error', 'GEMINI_API_KEY ikke sat i .env');
        }

        $blueprintId = $population->blueprint?->id;
        if (!$blueprintId) {
            return back()->with('error', 'Denne population har ingen personlighed endnu.');
        }

        $prefix = "personas:gen:{$population->id}";
        // Clear any prior cancellation flag — this is a fresh batch.
        Cache::forget("{$prefix}:cancelled");
        Cache::put("{$prefix}:queued",     $count, 3600);
        Cache::put("{$prefix}:done",       0,      3600);
        Cache::put("{$prefix}:errors",     0,      3600);
        Cache::put("{$prefix}:started_at", now()->toIso8601String(), 3600);

        for ($i = 0; $i < $count; $i++) {
            GeneratePersonaJob::dispatch($skipImages, $population->id, $blueprintId);
        }

        return back()->with('success', "{$count} personas sat i kø — generering kører i baggrunden. Siden refresher automatisk.");
    }

    public function status(Population $population)
    {
        $prefix = "personas:gen:{$population->id}";
        return response()->json([
            'queued' => (int) Cache::get("{$prefix}:queued",  0),
            'done'   => (int) Cache::get("{$prefix}:done",    0),
            'errors' => (int) Cache::get("{$prefix}:errors",  0),
            'total'  => Persona::where('population_id', $population->id)->count(),
        ]);
    }

    public function cancelGeneration(Population $population)
    {
        $prefix = "personas:gen:{$population->id}";
        // Flag tells already-queued jobs to bail when they pick up.
        Cache::put("{$prefix}:cancelled", true, 3600);
        // Clear UI counters so the progress card disappears.
        Cache::forget("{$prefix}:queued");
        Cache::forget("{$prefix}:done");
        Cache::forget("{$prefix}:errors");
        Cache::forget("{$prefix}:started_at");
        return back()->with('success', 'Generering annulleret. Igangværende jobs stoppes når de bliver hentet fra køen.');
    }

    public function destroy(Population $population, string $id)
    {
        $persona = Persona::find($id);
        if ($persona) {
            $img = $population->imagePath() . "/{$id}.png";
            if (is_file($img)) unlink($img);
            $persona->delete();
        }
        return redirect("/simulation/admin/populations/{$population->id}/personas")->with('success', 'Persona slettet.');
    }

    public function clear(Population $population)
    {
        $dir = $population->imagePath();
        if (is_dir($dir)) {
            foreach (glob("{$dir}/*.png") as $f) unlink($f);
        }
        Persona::where('population_id', $population->id)->delete();
        return redirect("/simulation/admin/populations/{$population->id}/personas")->with('success', 'Alle personas slettet.');
    }
}
