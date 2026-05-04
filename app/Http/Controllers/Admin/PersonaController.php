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

        $q         = trim((string) $request->query('q', ''));
        $region    = $request->query('region');
        $ageBucket = $request->query('age');

        $personas = $repo->filter($q, $region, $ageBucket);

        $ageBuckets = ['16-24','25-34','35-44','45-54','55-64','65-79','80-99'];
        $ageDist    = array_fill_keys($ageBuckets, 0);
        $regionDist = [];
        foreach ($all as $p) {
            $a = $p['demographics']['age'] ?? null;
            if (is_numeric($a)) {
                foreach ($ageBuckets as $b) {
                    [$min, $max] = array_map('intval', explode('-', $b));
                    if ($a >= $min && $a <= $max) { $ageDist[$b]++; break; }
                }
            }
            $r = $p['demographics']['region'] ?? null;
            if ($r) $regionDist[$r] = ($regionDist[$r] ?? 0) + 1;
        }
        arsort($regionDist);

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
            'courseBlueprint'=> $course?->blueprint,
            'activityCount'  => $activityCount,
            'personas'       => $personas,
            'count'          => $personas->count(),
            'total'          => $total,
            'q'              => $q,
            'region'         => $region,
            'age'            => $ageBucket,
            'regions'        => collect($all)->pluck('demographics.region')->unique()->filter()->sort()->values(),
            'ageDist'        => $ageDist,
            'regionDist'     => $regionDist,
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

        $blueprintId = \Illuminate\Support\Facades\Auth::user()?->currentCourse()?->blueprint_id;
        if (!$blueprintId) {
            return back()->with('error', 'Vælg en personlighed for kurset, før du kan generere personas.');
        }

        $prefix = "personas:gen:{$population->id}";
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
