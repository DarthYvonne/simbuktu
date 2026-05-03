<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Population;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PopulationController extends Controller
{
    public function index()
    {
        $course = Auth::user()?->currentCourse();
        if ($course?->population_id) {
            return redirect("/slophub/admin/populations/{$course->population_id}/personas");
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
        ]);
        $data['created_by'] = Auth::id();
        $population = Population::create($data);
        return redirect("/slophub/admin/populations/{$population->id}")->with('success', 'Population oprettet.');
    }

    public function show(Population $population)
    {
        return view('admin.populations.show', compact('population'));
    }

    public function update(Request $request, Population $population)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
        ]);
        $population->update($data);
        return back()->with('success', 'Population opdateret.');
    }

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

    public function subkultur(Population $population)
    {
        $defaults     = config('personas')['subcultures'];
        $override     = $population->config_overrides['subcultures'] ?? null;
        $effective    = $override ?? $defaults;
        $isOverridden = $override !== null;
        return view('admin.populations.subkultur', compact('population', 'defaults', 'effective', 'isOverridden'));
    }

    public function saveSubkultur(Request $request, Population $population)
    {
        $rows = $request->input('subcultures', []);
        $map  = [];
        foreach ($rows as $r) {
            $name = trim((string) ($r['name'] ?? ''));
            if ($name === '') continue;
            $w = is_numeric($r['weight'] ?? null) ? 0 + $r['weight'] : 0;
            $map[$name] = $w;
        }

        $allOverride   = $population->config_overrides ?? [];
        $defaults      = config('personas')['subcultures'];
        $defaultsFloat = array_map(fn ($v) => 0 + $v, $defaults);
        $mapFloat      = array_map(fn ($v) => 0 + $v, $map);

        if ($mapFloat === $defaultsFloat) {
            unset($allOverride['subcultures']);
        } else {
            $allOverride['subcultures'] = $map;
        }

        $population->update(['config_overrides' => empty($allOverride) ? null : $allOverride]);
        return back()->with('success', 'Subkulturer gemt.');
    }

    public function resetSubkultur(Population $population)
    {
        $allOverride = $population->config_overrides ?? [];
        unset($allOverride['subcultures']);
        $population->update(['config_overrides' => empty($allOverride) ? null : $allOverride]);
        return redirect("/slophub/admin/populations/{$population->id}/subkultur")->with('success', 'Nulstillet til global standard.');
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
        return redirect("/slophub/admin/populations/{$population->id}/demografi")->with('success', 'Nulstillet til global standard.');
    }

    public function prompts(Population $population)
    {
        $repo     = new \App\Services\PromptRepository();
        $defaults = $repo->defaults();
        $narrative = $defaults['persona.narrative'];
        $current   = $population->narrative_prompt ?? $narrative['body'];
        $isOverridden = (bool) $population->narrative_prompt;
        return view('admin.populations.prompts', compact('population', 'narrative', 'current', 'isOverridden'));
    }

    public function savePrompt(Request $request, Population $population)
    {
        $body = trim($request->input('body', ''));
        $defaults = (new \App\Services\PromptRepository())->defaults();
        $defaultBody = $defaults['persona.narrative']['body'] ?? '';
        $population->update(['narrative_prompt' => ($body && $body !== $defaultBody) ? $body : null]);
        return back()->with('success', 'Prompt gemt.');
    }

    public function destroy(Population $population)
    {
        $imageDir = $population->imagePath();
        if (is_dir($imageDir)) {
            foreach (glob("{$imageDir}/*.{png,jpg}", GLOB_BRACE) as $f) unlink($f);
            @rmdir("{$imageDir}/thumbs");
            @rmdir($imageDir);
        }
        $population->delete();
        return redirect('/slophub/admin/populations')->with('success', 'Population slettet.');
    }
}
