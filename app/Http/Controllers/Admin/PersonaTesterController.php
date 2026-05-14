<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\TestReactionJob;
use App\Models\Post;
use App\Services\Personas\PersonaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PersonaTesterController extends Controller
{
    public function __construct(
        private PersonaRepository $repo,
    ) {}

    public function index(Request $request)
    {
        $course     = Auth::user()->currentCourse();
        $population = $course?->population;
        $personas   = $population ? $this->repo->forPopulation($population)->all() : [];

        $q = trim((string) $request->query('q', ''));
        $postsQuery = Post::query()
            ->when($course, fn ($qb) => $qb->where('course_id', $course->id))
            ->latest('id');
        if ($q !== '') {
            $postsQuery->where('body', 'like', '%'.$q.'%');
        }
        $posts = $postsQuery->limit(300)->get(['id', 'body', 'image_path', 'author_name', 'created_at']);

        $batchId = session('tester_batch_id');
        $results = $batchId ? Cache::get("tester:results:{$batchId}", []) : [];
        $queued  = $batchId ? (int) Cache::get("tester:queued:{$batchId}", 0) : 0;
        $done    = $batchId ? (int) Cache::get("tester:done:{$batchId}", 0) : 0;
        if ($batchId) Cache::forget("tester:unread:{$batchId}");

        return view('admin.personas.tester', [
            'course'            => $course,
            'population'        => $population,
            'personas'          => $personas,
            'posts'             => $posts,
            'q'                 => $q,
            'selectedPersonaId' => $request->query('persona', session('tester_persona_id')),
            'selectedPostId'    => $request->query('post', session('tester_post_id')),
            'allModels'         => config('llm_models'),
            'availableModels'   => $this->availableModelIds(),
            'selectedModels'    => session('tester_models', $this->availableModelIds()),
            'batchId'           => $batchId,
            'results'           => $results,
            'queued'            => $queued,
            'done'              => $done,
        ]);
    }

    public function run(Request $request)
    {
        $request->validate([
            'persona_id' => 'required|string',
            'post_id'    => 'required|integer',
            'models'     => 'required|array|min:1',
            'models.*'   => 'string',
        ]);

        $course     = Auth::user()->currentCourse();
        $population = $course?->population;
        if (!$population) return back()->with('error', 'Ingen aktiv population.');

        $personas = $this->repo->forPopulation($population)->all();
        $persona  = collect($personas)->firstWhere('id', $request->input('persona_id'));
        if (!$persona) return back()->with('error', 'Persona ikke fundet.');

        $post = Post::where('course_id', $course->id)->find($request->input('post_id'));
        if (!$post) return back()->with('error', 'Opslag ikke fundet i dette kursus.');

        $available = $this->availableModelIds();
        $picked    = array_values(array_intersect($request->input('models'), $available));
        if (empty($picked)) return back()->with('error', 'Ingen gyldige modeller valgt (mangler API-nøgle?).');

        $batchId = (string) Str::uuid();
        Cache::put("tester:queued:{$batchId}", count($picked), 3600);
        Cache::put("tester:done:{$batchId}", 0, 3600);
        Cache::put("tester:results:{$batchId}", [], 3600);

        foreach ($picked as $modelId) {
            TestReactionJob::dispatch($batchId, $persona, $post->body, null, null, $modelId);
        }

        session([
            'tester_batch_id'   => $batchId,
            'tester_persona_id' => $persona['id'],
            'tester_post_id'    => $post->id,
            'tester_models'     => $picked,
        ]);

        return redirect('/simulation/admin/personas/tester?persona='.$persona['id'].'&post='.$post->id);
    }

    public function status()
    {
        $batchId = session('tester_batch_id');
        if (!$batchId) return response()->json(['queued' => 0, 'done' => 0, 'results' => []]);
        return response()->json([
            'queued'  => (int) Cache::get("tester:queued:{$batchId}", 0),
            'done'    => (int) Cache::get("tester:done:{$batchId}",   0),
            'results' => Cache::get("tester:results:{$batchId}", []),
        ]);
    }

    /**
     * Models whose provider API key is configured.
     */
    private function availableModelIds(): array
    {
        $hasKey = [
            'gemini'    => !empty(config('gemini.api_key')),
            'grok'      => !empty(config('services.grok.api_key')),
            'openai'    => !empty(config('services.openai.api_key')),
            'anthropic' => !empty(config('services.anthropic.api_key')),
        ];
        return array_keys(array_filter(
            config('llm_models'),
            fn ($m) => $hasKey[$m['provider']] ?? false,
        ));
    }
}
