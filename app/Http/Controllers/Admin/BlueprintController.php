<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blueprint;
use App\Models\LibraryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlueprintController extends Controller
{
    public function index()
    {
        $blueprints = Blueprint::orderBy('name')->get();
        $course = \Illuminate\Support\Facades\Auth::user()?->currentCourse();
        return view('admin.blueprints.index', compact('blueprints', 'course'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $defaults = LibraryParameter::where('default_in_new_blueprints', true)
            ->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'name', 'description', 'type', 'facets']);
        $data['parameters'] = $defaults->map(fn ($lib) => [
            'id'                   => (string) Str::uuid(),
            'name'                 => $lib->name,
            'description'          => $lib->description,
            'type'                 => $lib->type ?? 'personality',
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
        $data['created_by'] = Auth::id();
        $blueprint = Blueprint::create($data);
        return redirect("/simulation/admin/blueprints/{$blueprint->id}")->with('success', 'Personlighed oprettet.');
    }

    public function edit(Blueprint $blueprint)
    {
        $library = LibraryParameter::orderBy('sort_order')->orderBy('name')
            ->get(['id', 'category', 'name', 'description', 'facets']);
        return view('admin.blueprints.edit', compact('blueprint', 'library'));
    }

    public function update(Request $request, Blueprint $blueprint)
    {
        $data = $request->validate([
            'name'                              => 'required|string|max:255',
            'description'                       => 'nullable|string',
            'parameters'                        => 'array',
            'parameters.*.id'                   => 'nullable|string|max:64',
            'parameters.*.name'                 => 'required|string|max:64',
            'parameters.*.description'          => 'nullable|string|max:500',
            'parameters.*.type'                 => 'nullable|in:personality,demographic',
            'parameters.*.library_parameter_id' => 'nullable|integer',
            'parameters.*.show_on_profile'      => 'nullable|boolean',
            'parameters.*.facets'               => 'required|array|min:2',
            'parameters.*.facets.*.id'          => 'nullable|string|max:64',
            'parameters.*.facets.*.name'        => 'required|string|max:64',
            'parameters.*.facets.*.text'        => 'required|string|max:5000',
            'parameters.*.facets.*.weight'      => 'nullable|integer|min:0|max:100',
            'parameters.*.facets.*.value'       => 'nullable|string|max:64',
        ]);
        $errors = [];
        $data['parameters'] = array_values(array_map(function ($p, $i) use (&$errors) {
            $facets = array_values(array_map(
                fn ($f) => [
                    'id'     => $f['id'] ?? (string) Str::uuid(),
                    'name'   => trim($f['name']),
                    'text'   => trim($f['text']),
                    'weight' => (int) ($f['weight'] ?? 0),
                    'value'  => trim($f['value'] ?? '') ?: null,
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
                'library_parameter_id' => $p['library_parameter_id'] ?? null,
                'show_on_profile'      => (bool) ($p['show_on_profile'] ?? false),
                'facets'               => $facets,
            ];
        }, $data['parameters'] ?? [], array_keys($data['parameters'] ?? [])));
        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
        $blueprint->update($data);
        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Personlighed gemt.');
    }

    public function destroy(Blueprint $blueprint)
    {
        $blueprint->delete();
        return redirect('/simulation/admin/blueprints')->with('success', 'Personlighed slettet.');
    }

    public function editOm(Blueprint $blueprint)
    {
        return view('admin.blueprints.om', compact('blueprint'));
    }

    public function updateMeta(Request $request, Blueprint $blueprint)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $blueprint->update($data);
        return redirect("/simulation/admin/blueprints/{$blueprint->id}/om")->with('success', 'Gemt.');
    }

    public function editPrompts(Blueprint $blueprint)
    {
        $defaults = (new \App\Services\PromptRepository())->defaults();
        $rows = [];
        foreach (Blueprint::PROMPT_KEYS as $key) {
            $def = $defaults[$key] ?? null;
            if (!$def) continue;
            $override = $blueprint->prompt_overrides[$key] ?? null;
            $rows[] = [
                'key'         => $key,
                'name'        => $def['name'] ?? $key,
                'description' => $def['description'] ?? '',
                'default'     => $def['body'] ?? '',
                'current'     => $override ?: ($def['body'] ?? ''),
                'overridden'  => is_string($override) && trim($override) !== '',
                'placeholders'=> $def['placeholders'] ?? [],
            ];
        }
        return view('admin.blueprints.prompts', compact('blueprint', 'rows'));
    }

    public function updatePrompts(Request $request, Blueprint $blueprint)
    {
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

    public function promote(Request $request, Blueprint $blueprint)
    {
        $data = $request->validate([
            'parameter_index' => 'required|integer|min:0',
            'mode'            => 'required|in:new,update',
            'target_id'       => 'nullable|integer|exists:library_parameters,id',
        ]);
        $params = $blueprint->parameters ?? [];
        $p = $params[$data['parameter_index']] ?? null;
        if (!$p) return response()->json(['ok' => false, 'error' => 'parameter not found'], 404);

        $payload = [
            'name'        => $p['name'],
            'description' => $p['description'] ?? null,
            'facets'      => $p['facets'],
        ];

        if ($data['mode'] === 'update' && !empty($data['target_id'])) {
            $lib = LibraryParameter::findOrFail($data['target_id']);
            $lib->update($payload);
        } else {
            if (LibraryParameter::where('name', $payload['name'])->exists()) {
                return response()->json(['ok' => false, 'error' => 'En dimension med dette navn findes allerede i biblioteket.'], 422);
            }
            $lib = LibraryParameter::create($payload);
        }

        $params[$data['parameter_index']]['library_parameter_id'] = $lib->id;
        $blueprint->update(['parameters' => $params]);

        return response()->json(['ok' => true, 'library_parameter_id' => $lib->id, 'name' => $lib->name]);
    }
}
