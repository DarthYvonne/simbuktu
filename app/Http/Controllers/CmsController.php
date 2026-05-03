<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Models\CmsSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsController extends Controller
{
    public function settings()
    {
        $heroImage   = CmsSetting::get('home_hero_image');
        $homeContent = CmsSetting::get('home_content');
        return view('cms.settings', compact('heroImage', 'homeContent'));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'hero_image'   => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'remove_hero'  => 'nullable|boolean',
            'home_content' => 'nullable|string',
            'reset_home_content' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_hero')) {
            $this->deleteCurrentHero();
            CmsSetting::set('home_hero_image', null);
            return redirect('/cms/settings')->with('status', 'Hero-billede fjernet.');
        }

        if ($request->hasFile('hero_image')) {
            $this->deleteCurrentHero();
            $file = $request->file('hero_image');
            $name = 'hero-'.now()->format('YmdHis').'.'.$file->getClientOriginalExtension();
            $dest = public_path('img/uploads');
            if (!is_dir($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $name);
            CmsSetting::set('home_hero_image', 'img/uploads/'.$name);
        }

        if ($request->boolean('reset_home_content')) {
            CmsSetting::set('home_content', null);
        } elseif ($request->has('home_content')) {
            CmsSetting::set('home_content', $request->input('home_content'));
        }

        return redirect('/cms/settings')->with('status', 'Indstillinger gemt.');
    }

    private function deleteCurrentHero(): void
    {
        $current = CmsSetting::get('home_hero_image');
        if ($current && str_starts_with($current, 'img/uploads/')) {
            $path = public_path($current);
            if (is_file($path)) @unlink($path);
        }
    }

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
