<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryParameter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlueprintLibraryController extends Controller
{
    public function index()
    {
        $parameters = LibraryParameter::orderBy('name')->get();
        return view('admin.blueprint-library.index', compact('parameters'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $parameter = LibraryParameter::create($data);
        return redirect("/simulation/admin/blueprint-library/{$parameter->id}")->with('success', 'Dimension oprettet.');
    }

    public function edit(LibraryParameter $parameter)
    {
        return view('admin.blueprint-library.edit', compact('parameter'));
    }

    public function update(Request $request, LibraryParameter $parameter)
    {
        $data = $this->validatePayload($request, $parameter->id);
        $parameter->update($data);
        return back()->with('success', 'Dimension opdateret.');
    }

    public function destroy(LibraryParameter $parameter)
    {
        $parameter->delete();
        return redirect('/simulation/admin/blueprint-library')->with('success', 'Dimension slettet.');
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:64', Rule::unique('library_parameters', 'name')->ignore($ignoreId)],
            'description'     => 'nullable|string|max:500',
            'levels'          => 'required|array|min:2',
            'levels.*.name'   => 'required|string|max:64',
            'levels.*.text'   => 'required|string|max:5000',
        ]);
        $data['levels'] = array_values(array_map(
            fn ($l) => ['name' => trim($l['name']), 'text' => trim($l['text'])],
            $data['levels']
        ));
        return $data;
    }
}
