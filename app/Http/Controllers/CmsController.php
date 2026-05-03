<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsController extends Controller
{
    public function index()
    {
        $pages = CmsPage::orderBy('sort_order')->get();
        return view('cms.index', compact('pages'));
    }

    public function create()
    {
        return view('cms.edit', ['page' => new CmsPage(['is_visible' => true, 'sort_order' => (CmsPage::max('sort_order') ?? 0) + 1])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        CmsPage::create($data);
        return redirect('/cms')->with('status', 'Side oprettet.');
    }

    public function edit(CmsPage $page)
    {
        return view('cms.edit', compact('page'));
    }

    public function update(Request $request, CmsPage $page)
    {
        $page->update($this->validated($request, $page->id));
        return redirect('/cms')->with('status', 'Side opdateret.');
    }

    public function destroy(CmsPage $page)
    {
        $page->delete();
        return redirect('/cms')->with('status', 'Side slettet.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255',
            'content'    => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_visible' => 'nullable|boolean',
        ]);

        $data['slug']       = isset($data['slug']) ? trim($data['slug'], '/') : Str::slug($data['title']);
        $data['is_visible'] = (bool) ($data['is_visible'] ?? false);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $exists = CmsPage::where('slug', $data['slug'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            abort(422, 'Slug findes allerede.');
        }

        return $data;
    }
}
