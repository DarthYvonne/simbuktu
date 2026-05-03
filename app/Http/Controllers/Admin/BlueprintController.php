<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blueprint;
use App\Models\LibraryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $data['parameters'] = [];
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
            'parameters.*.name'                 => 'required|string|max:64',
            'parameters.*.description'          => 'nullable|string|max:500',
            'parameters.*.library_parameter_id' => 'nullable|integer',
            'parameters.*.show_on_profile'      => 'nullable|boolean',
            'parameters.*.facets'               => 'required|array|min:2',
            'parameters.*.facets.*.name'        => 'required|string|max:64',
            'parameters.*.facets.*.text'        => 'required|string|max:5000',
            'parameters.*.facets.*.weight'      => 'nullable|integer|min:0|max:100',
        ]);
        $errors = [];
        $data['parameters'] = array_values(array_map(function ($p, $i) use (&$errors) {
            $facets = array_values(array_map(
                fn ($f) => [
                    'name'   => trim($f['name']),
                    'text'   => trim($f['text']),
                    'weight' => (int) ($f['weight'] ?? 0),
                ],
                $p['facets']
            ));
            $sum = array_sum(array_column($facets, 'weight'));
            if ($sum > 100) {
                $errors["parameters.$i.facets"] = 'Summen af vægte for "'.($p['name'] ?: 'unavngivet').'" overstiger 100 % (er '.$sum.' %).';
            }
            return [
                'name'                 => trim($p['name']),
                'description'          => $p['description'] ?? null,
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
