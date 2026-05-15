<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\TestReactionJob;
use App\Models\Blueprint;
use App\Models\LibraryParameter;
use App\Models\Population;
use App\Models\Post;
use App\Services\Personas\PersonaRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PopulationController extends Controller
{
    // ------------------------- Population list / CRUD -------------------------

    public function index()
    {
        $course = Auth::user()?->currentCourse();
        if ($course?->population_id) {
            return redirect("/simulation/admin/populations/{$course->population_id}/personas");
        }

        $populations   = Population::withCount('personas')->orderBy('name')->get();
        $activityCount = 0;
        if ($course) {
            $postIds       = Post::where('course_id', $course->id)->pluck('id');
            $activityCount = DB::table('comments')->whereIn('post_id', $postIds)->count()
                           + DB::table('post_exposures')->whereIn('post_id', $postIds)->count();
        }
        return view('admin.populations.index', compact('populations', 'course', 'activityCount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'clone_from'  => 'nullable|integer|exists:populations,id',
        ]);
        $cloneFrom = $data['clone_from'] ?? null;
        unset($data['clone_from']);
        $data['created_by'] = Auth::id();
        $population = Population::create($data);

        $this->createBlueprintForPopulation($population, $cloneFrom ? Population::find($cloneFrom) : null);

        return redirect("/simulation/admin/populations/{$population->id}/personlighed")
            ->with('success', 'Population oprettet.');
    }

    public function show(Population $population)
    {
        return view('admin.populations.show', compact('population'));
    }

    public function update(Request $request, Population $population)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $population->update($data);
        return back()->with('success', 'Population opdateret.');
    }

    public function destroy(Population $population)
    {
        $imageDir = $population->imagePath();
        if (is_dir($imageDir)) {
            foreach (glob("{$imageDir}/*.{png,jpg}", GLOB_BRACE) as $f) unlink($f);
            @rmdir("{$imageDir}/thumbs");
            @rmdir($imageDir);
        }
        $population->delete(); // cascade drops the blueprint via FK
        return redirect('/simulation/admin/populations')->with('success', 'Population slettet.');
    }

    // ------------------------- Demografi (unchanged) -------------------------

    public function demografi(Population $population)
    {
        $defaults  = config('personas')['demographics'];
        $overrides = $population->config_overrides['demographics'] ?? [];
        $effective = array_replace_recursive($defaults, $overrides);
        return view('admin.populations.demografi', compact('population', 'defaults', 'overrides', 'effective'));
    }

    public function saveDemografi(Request $request, Population $population)
    {
        $data = $request->validate([
            'age_brackets'        => 'array',
            'age_brackets.*.min'  => 'required_with:age_brackets|integer|min:0|max:120',
            'age_brackets.*.max'  => 'required_with:age_brackets|integer|min:0|max:120',
            'age_brackets.*.weight' => 'required_with:age_brackets|numeric|min:0|max:1000',
            'gender'              => 'array',
            'gender.*'            => 'numeric|min:0|max:1000',
            'region'              => 'array',
            'region.*'            => 'numeric|min:0|max:1000',
            'city_type'           => 'array',
            'city_type.*'         => 'numeric|min:0|max:1000',
            'education'           => 'array',
            'education.*'         => 'numeric|min:0|max:1000',
            'heritage'            => 'array',
            'heritage.*'          => 'numeric|min:0|max:1000',
        ]);

        $defaults    = config('personas')['demographics'];
        $allOverride = $population->config_overrides ?? [];
        $demoOverride = [];

        if (!empty($data['age_brackets'])) {
            $brackets = array_values(array_map(fn ($b) => [
                'range'  => [(int) $b['min'], (int) $b['max']],
                'weight' => 0 + $b['weight'],
            ], $data['age_brackets']));
            $brackets = array_values(array_filter($brackets, fn ($b) => $b['range'][1] >= $b['range'][0]));
            if ($brackets !== $defaults['age_brackets']) {
                $demoOverride['age_brackets'] = $brackets;
            }
        }

        foreach (['gender', 'region', 'city_type', 'education', 'heritage'] as $dim) {
            if (!isset($data[$dim])) continue;
            $values = [];
            foreach ($defaults[$dim] as $key => $defaultWeight) {
                $values[$key] = isset($data[$dim][$key]) ? 0 + $data[$dim][$key] : 0;
            }
            $defaultsFloat = array_map(fn ($v) => 0 + $v, $defaults[$dim]);
            if ($values !== $defaultsFloat) {
                $demoOverride[$dim] = $values;
            }
        }

        if (!empty($demoOverride)) {
            $allOverride['demographics'] = $demoOverride;
        } else {
            unset($allOverride['demographics']);
        }

        $population->update(['config_overrides' => empty($allOverride) ? null : $allOverride]);
        return back()->with('success', 'Demografi gemt.');
    }

    public function resetDemografiDimension(Population $population, string $dim)
    {
        $allowed = ['age_brackets', 'gender', 'region', 'city_type', 'education', 'heritage'];
        abort_unless(in_array($dim, $allowed, true), 404);

        $allOverride = $population->config_overrides ?? [];
        if (isset($allOverride['demographics'][$dim])) {
            unset($allOverride['demographics'][$dim]);
            if (empty($allOverride['demographics'])) {
                unset($allOverride['demographics']);
            }
        }
        $population->update(['config_overrides' => empty($allOverride) ? null : $allOverride]);
        return redirect("/simulation/admin/populations/{$population->id}/demografi")->with('success', 'Nulstillet til global standard.');
    }

    // ------------------------- Personlighed (1:1 blueprint) -------------------------

    public function personlighedDimensioner(Population $population)
    {
        $blueprint = $this->ensureBlueprint($population);
        $library = LibraryParameter::orderBy('sort_order')->orderBy('name')
            ->get(['id', 'category', 'name', 'description', 'facets']);
        $urlBase = $this->personlighedBase($population);
        return view('admin.blueprints.edit', compact('blueprint', 'library', 'population', 'urlBase'));
    }

    public function updatePersonlighedDimensioner(Request $request, Population $population)
    {
        $blueprint = $this->ensureBlueprint($population);
        $data = $request->validate([
            'name'                              => 'nullable|string|max:255',
            'description'                       => 'nullable|string',
            'parameters'                        => 'array',
            'parameters.*.id'                   => 'nullable|string|max:64',
            'parameters.*.name'                 => 'required|string|max:64',
            'parameters.*.description'          => 'nullable|string|max:500',
            'parameters.*.type'                 => 'nullable|in:personality,demographic',
            'parameters.*.tier'                 => 'nullable|in:primary,secondary',
            'parameters.*.library_parameter_id' => 'nullable|integer',
            'parameters.*.show_on_profile'      => 'nullable|boolean',
            'parameters.*.facets'               => 'required|array|min:1',
            'parameters.*.facets.*.id'          => 'nullable|string|max:64',
            'parameters.*.facets.*.name'        => 'required|string|max:64',
            'parameters.*.facets.*.text'        => 'nullable|string|max:5000',
            'parameters.*.facets.*.weight'      => 'nullable|integer|min:0|max:100',
            'parameters.*.facets.*.value'       => 'nullable|string|max:64',
        ]);
        $errors = [];
        $data['parameters'] = array_values(array_map(function ($p, $i) use (&$errors) {
            $facets = array_values(array_map(
                fn ($f) => [
                    'id'     => $f['id'] ?? (string) Str::uuid(),
                    'name'   => trim($f['name']),
                    'text'   => trim((string) ($f['text'] ?? '')),
                    'weight' => (int) ($f['weight'] ?? 0),
                    'value'  => trim((string) ($f['value'] ?? '')) ?: null,
                ],
                $p['facets']
            ));
            $sum = array_sum(array_column($facets, 'weight'));
            if ($sum > 100) {
                $errors["parameters.$i.facets"] = 'Summen af vægte for "'.($p['name'] ?: 'unavngivet').'" overstiger 100 % (er '.$sum.' %).';
            }
            return [
                'id'                   => $p['id'] ?? (string) Str::uuid(),
                'name'                 => trim($p['name']),
                'description'          => $p['description'] ?? null,
                'type'                 => $p['type'] ?? 'personality',
                'tier'                 => $p['tier'] ?? 'primary',
                'library_parameter_id' => $p['library_parameter_id'] ?? null,
                'show_on_profile'      => (bool) ($p['show_on_profile'] ?? false),
                'facets'               => $facets,
            ];
        }, $data['parameters'] ?? [], array_keys($data['parameters'] ?? [])));
        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
        $update = ['parameters' => $data['parameters']];
        if (!empty($data['name']))        $update['name'] = $data['name'];
        if (isset($data['description']))  $update['description'] = $data['description'];
        $blueprint->update($update);

        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Personlighed gemt.');
    }

    public function personlighedOm(Population $population)
    {
        $blueprint = $this->ensureBlueprint($population);
        $urlBase = $this->personlighedBase($population);
        return view('admin.blueprints.om', compact('blueprint', 'population', 'urlBase'));
    }

    public function updatePersonlighedOm(Request $request, Population $population)
    {
        $blueprint = $this->ensureBlueprint($population);
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $blueprint->update($data);
        return redirect("{$this->personlighedBase($population)}/om")->with('success', 'Gemt.');
    }

    public function personlighedPrompts(Population $population)
    {
        $blueprint = $this->ensureBlueprint($population);
        $defaults = (new \App\Services\PromptRepository())->defaults();
        $templateBodies = \App\Models\Prompt::whereIn('key', Blueprint::PROMPT_KEYS)
            ->pluck('body', 'key')->all();
        $rows = [];
        foreach (Blueprint::PROMPT_KEYS as $key) {
            $def = $defaults[$key] ?? null;
            if (!$def) continue;
            $override = $blueprint->prompt_overrides[$key] ?? null;
            $template = $templateBodies[$key] ?? ($def['body'] ?? '');
            $rows[] = [
                'key'         => $key,
                'name'        => $def['name'] ?? $key,
                'description' => $def['description'] ?? '',
                'template'    => $template,
                'current'     => $override ?: $template,
                'placeholders'=> $def['placeholders'] ?? [],
            ];
        }
        $urlBase = $this->personlighedBase($population);
        return view('admin.blueprints.prompts', compact('blueprint', 'rows', 'population', 'urlBase'));
    }

    public function updatePersonlighedPrompts(Request $request, Population $population)
    {
        $blueprint = $this->ensureBlueprint($population);
        $data = $request->validate([
            'prompts'   => 'array',
            'prompts.*' => 'nullable|string|max:20000',
        ]);
        $defaults = (new \App\Services\PromptRepository())->defaults();
        $overrides = $blueprint->prompt_overrides ?? [];
        foreach ($data['prompts'] ?? [] as $key => $body) {
            if (!in_array($key, Blueprint::PROMPT_KEYS, true)) continue;
            $body = trim((string) $body);
            $defaultBody = $defaults[$key]['body'] ?? '';
            if ($body === '' || $body === $defaultBody) {
                unset($overrides[$key]);
            } else {
                $overrides[$key] = $body;
            }
        }
        $blueprint->update(['prompt_overrides' => $overrides ?: null]);
        return back()->with('success', 'Prompts gemt.');
    }

    public function personlighedChat(Request $request, Population $population)
    {
        $blueprint = $this->ensureBlueprint($population);
        // Re-use the chat logic from BlueprintController (still living there for now).
        return app(BlueprintController::class)->chat($request, $blueprint);
    }

    public function personlighedPromote(Request $request, Population $population)
    {
        $blueprint = $this->ensureBlueprint($population);
        return app(BlueprintController::class)->promote($request, $blueprint);
    }

    // ------------------------- Test AI (under Personlighed) -------------------------

    public function personlighedTest(Request $request, Population $population)
    {
        $repo     = app(PersonaRepository::class);
        $personas = $repo->forPopulation($population)->all();
        $course   = Auth::user()?->currentCourse();

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
        $done    = $batchId ? (int) Cache::get("tester:done:{$batchId}",   0) : 0;
        if ($batchId) Cache::forget("tester:unread:{$batchId}");

        $urlBase = $this->personlighedBase($population);

        return view('admin.personas.tester', [
            'course'            => $course,
            'population'        => $population,
            'personas'          => $personas,
            'posts'             => $posts,
            'q'                 => $q,
            'selectedPersonaId' => $request->query('persona', session('tester_persona_id')),
            'selectedPostId'    => $request->query('post', session('tester_post_id')),
            'allModels'         => config('llm_models'),
            'availableModels'   => $this->availableTesterModelIds(),
            'selectedModels'    => session('tester_models', $this->availableTesterModelIds()),
            'batchId'           => $batchId,
            'results'           => $results,
            'queued'            => $queued,
            'done'              => $done,
            'urlBase'           => $urlBase,
        ]);
    }

    public function runPersonlighedTest(Request $request, Population $population)
    {
        $request->validate([
            'persona_id' => 'required|string',
            'post_id'    => 'required|integer',
            'models'     => 'required|array|min:1',
            'models.*'   => 'string',
        ]);

        $repo     = app(PersonaRepository::class);
        $personas = $repo->forPopulation($population)->all();
        $persona  = collect($personas)->firstWhere('id', $request->input('persona_id'));
        if (!$persona) return back()->with('error', 'Persona ikke fundet.');

        $course = Auth::user()?->currentCourse();
        $post = Post::when($course, fn ($q) => $q->where('course_id', $course->id))
            ->find($request->input('post_id'));
        if (!$post) return back()->with('error', 'Opslag ikke fundet.');

        $available = $this->availableTesterModelIds();
        $picked    = array_values(array_intersect($request->input('models'), $available));
        if (empty($picked)) return back()->with('error', 'Ingen gyldige modeller valgt (mangler API-nøgle?).');

        $batchId = (string) Str::uuid();
        Cache::put("tester:queued:{$batchId}", count($picked), 3600);
        Cache::put("tester:done:{$batchId}", 0, 3600);
        Cache::put("tester:results:{$batchId}", [], 3600);

        $linkPreview = $post->link_url ? [
            'title' => $post->link_title,
            'description' => $post->link_description,
            'site_name' => $post->link_site_name,
        ] : null;

        foreach ($picked as $modelId) {
            TestReactionJob::dispatch($batchId, $persona, $post->body, $post->image_description, $linkPreview, $modelId);
        }

        session([
            'tester_batch_id'   => $batchId,
            'tester_persona_id' => $persona['id'],
            'tester_post_id'    => $post->id,
            'tester_models'     => $picked,
        ]);

        return redirect("{$this->personlighedBase($population)}/test?persona={$persona['id']}&post={$post->id}");
    }

    public function statusPersonlighedTest(Population $population)
    {
        $batchId = session('tester_batch_id');
        if (!$batchId) return response()->json(['queued' => 0, 'done' => 0, 'results' => []]);
        return response()->json([
            'queued'  => (int) Cache::get("tester:queued:{$batchId}", 0),
            'done'    => (int) Cache::get("tester:done:{$batchId}",   0),
            'results' => Cache::get("tester:results:{$batchId}", []),
        ]);
    }

    // ------------------------- Kontekst -------------------------

    public function kontekst(Population $population)
    {
        return view('admin.populations.kontekst', compact('population'));
    }

    public function updateKontekst(Request $request, Population $population)
    {
        $data = $request->validate([
            'manual_context' => 'nullable|string|max:20000',
        ]);
        $population->update([
            'manual_context' => trim((string) ($data['manual_context'] ?? '')) ?: null,
            'context_mode'   => 'manual',
        ]);
        return back()->with('success', 'Kontekst gemt.');
    }

    // ------------------------- Internals -------------------------

    /**
     * Ensure the population has a blueprint row (defensive — every population should
     * own one, created on store(), but legacy data could be missing one).
     */
    private function ensureBlueprint(Population $population): Blueprint
    {
        if ($population->blueprint) return $population->blueprint;
        return $this->createBlueprintForPopulation($population);
    }

    /**
     * Create the blueprint for a population. If $clone is given, deep-copy its
     * parameters + prompt overrides; otherwise seed from defaults (library + DB
     * prompt templates), matching the old BlueprintController::store flow.
     */
    private function createBlueprintForPopulation(Population $population, ?Population $clone = null): Blueprint
    {
        $source = $clone?->blueprint;

        if ($source) {
            $parameters = $this->cloneParameters($source->parameters ?? []);
            $prompts    = $source->prompt_overrides ?? [];
            $name       = $population->name;
            $desc       = $source->description;
        } else {
            $defaults = LibraryParameter::where('default_in_new_blueprints', true)
                ->orderBy('sort_order')->orderBy('name')
                ->get(['id', 'name', 'description', 'type', 'facets']);
            $parameters = $defaults->map(fn ($lib) => [
                'id'                   => (string) Str::uuid(),
                'name'                 => $lib->name,
                'description'          => $lib->description,
                'type'                 => $lib->type ?? 'personality',
                'tier'                 => 'primary',
                'library_parameter_id' => $lib->id,
                'show_on_profile'      => false,
                'facets'               => array_map(
                    fn ($f) => [
                        'id'     => (string) Str::uuid(),
                        'name'   => $f['name'] ?? '',
                        'text'   => $f['text'] ?? '',
                        'weight' => (int) ($f['weight'] ?? 0),
                    ],
                    $lib->facets ?? []
                ),
            ])->all();

            // Seed all template prompts so the population is self-contained from day one.
            $templateBodies = \App\Models\Prompt::whereIn('key', Blueprint::PROMPT_KEYS)
                ->pluck('body', 'key')->all();
            $promptDefaults = (new \App\Services\PromptRepository())->defaults();
            $prompts = [];
            foreach (Blueprint::PROMPT_KEYS as $key) {
                $body = $templateBodies[$key] ?? ($promptDefaults[$key]['body'] ?? '');
                if (trim($body) !== '') $prompts[$key] = $body;
            }
            $name = $population->name;
            $desc = null;
        }

        return Blueprint::create([
            'population_id'    => $population->id,
            'name'             => $name,
            'description'      => $desc,
            'parameters'       => $parameters,
            'prompt_overrides' => $prompts ?: null,
            'created_by'       => Auth::id(),
        ]);
    }

    /** Deep-copy parameters, regenerating dimension + facet UUIDs so the clone is independent. */
    private function cloneParameters(array $parameters): array
    {
        return array_values(array_map(function ($p) {
            $facets = array_map(function ($f) {
                return [
                    'id'     => (string) Str::uuid(),
                    'name'   => $f['name']   ?? '',
                    'text'   => $f['text']   ?? '',
                    'weight' => (int) ($f['weight'] ?? 0),
                    'value'  => $f['value']  ?? null,
                ];
            }, $p['facets'] ?? []);
            return [
                'id'                   => (string) Str::uuid(),
                'name'                 => $p['name'] ?? '',
                'description'          => $p['description'] ?? null,
                'type'                 => $p['type'] ?? 'personality',
                'tier'                 => $p['tier'] ?? 'primary',
                'library_parameter_id' => $p['library_parameter_id'] ?? null,
                'show_on_profile'      => (bool) ($p['show_on_profile'] ?? false),
                'facets'               => array_values($facets),
            ];
        }, $parameters));
    }

    private function personlighedBase(Population $population): string
    {
        return "/simulation/admin/populations/{$population->id}/personlighed";
    }

    private function availableTesterModelIds(): array
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
