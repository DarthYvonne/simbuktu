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
        $parameters = LibraryParameter::orderBy('sort_order')->orderBy('name')->get()
            ->groupBy(fn ($p) => $p->category ?: 'egne');
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
            'name'            => ['required', 'string', 'max:64'],
            'category'        => 'nullable|string|max:64',
            'description'     => 'nullable|string|max:500',
            'facets'          => 'required|array|min:2',
            'facets.*.name'   => 'required|string|max:64',
            'facets.*.text'   => 'required|string|max:5000',
            'facets.*.weight' => 'nullable|integer|min:0|max:100',
        ]);
        $exists = LibraryParameter::where('name', $data['name'])
            ->where('category', $data['category'] ?? null)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => 'En dimension med dette navn findes allerede i denne kategori.',
            ]);
        }
        $data['facets'] = array_values(array_map(
            fn ($f) => [
                'name'   => trim($f['name']),
                'text'   => trim($f['text']),
                'weight' => (int) ($f['weight'] ?? 0),
            ],
            $data['facets']
        ));
        if (array_sum(array_column($data['facets'], 'weight')) > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'facets' => 'Summen af vægte må højst være 100 %.',
            ]);
        }
        return $data;
    }
}
