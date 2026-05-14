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
        $heroImage      = CmsSetting::get('home_hero_image');
        $heroHeadline   = CmsSetting::get('home_hero_headline', 'Hvad hvis du kunne spørge dem?');
        $heroSubhead    = CmsSetting::get('home_hero_subhead', 'VI BYGGER SIMULEREDE POPULATIONER SOM KAN GIVE DIG INSPIRATION TIL AT FORSTÅ DIN MÅLGRUPPE BEDRE');
        $heroButtonText = CmsSetting::get('home_hero_button_text', 'Udforsk nu');
        $heroButtonUrl  = CmsSetting::get('home_hero_button_url', '#');
        $homeContent    = CmsSetting::get('home_content');
        return view('cms.settings', compact('heroImage', 'heroHeadline', 'heroSubhead', 'heroButtonText', 'heroButtonUrl', 'homeContent'));
    }

    public function saveSettings(Request $request)
    {
        // If the user picked a file but PHP rejected it (post_max_size /
        // upload_max_filesize / disk error), $request->hasFile() is false even
        // though the user clearly intended to upload. Surface a real error
        // instead of silently saving everything else and leaving the image
        // unchanged.
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] !== UPLOAD_ERR_OK && $_FILES['hero_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $msg = match ($_FILES['hero_image']['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Billedet er for stort. Brug et mindre billede eller bed admin om at hæve PHP\'s upload-grænse.',
                UPLOAD_ERR_PARTIAL    => 'Upload blev afbrudt midt i. Prøv igen.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server-fejl: ingen midlertidig mappe.',
                UPLOAD_ERR_CANT_WRITE => 'Server-fejl: kan ikke skrive billedet.',
                default               => 'Upload-fejl (kode '.$_FILES['hero_image']['error'].').',
            };
            return back()->withErrors(['hero_image' => $msg])->withInput();
        }

        $request->validate([
            'hero_image'           => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'hero_headline'        => 'nullable|string|max:500',
            'hero_subhead'         => 'nullable|string|max:1000',
            'hero_button_text'     => 'nullable|string|max:100',
            'hero_button_url'      => 'nullable|string|max:500',
            'home_content'         => 'nullable|string',
            'reset_all'            => 'nullable|boolean',
        ]);

        if ($request->boolean('reset_all')) {
            $this->deleteCurrentHero();
            foreach ([
                'home_hero_image', 'home_hero_headline', 'home_hero_subhead',
                'home_hero_button_text', 'home_hero_button_url', 'home_content',
            ] as $key) {
                CmsSetting::where('key', $key)->delete();
            }
            return redirect('/cms/settings')->with('status', 'Forside nulstillet til standard.');
        }

        foreach ([
            'home_hero_headline'    => 'hero_headline',
            'home_hero_subhead'     => 'hero_subhead',
            'home_hero_button_text' => 'hero_button_text',
            'home_hero_button_url'  => 'hero_button_url',
            'home_content'          => 'home_content',
        ] as $key => $field) {
            if ($request->has($field)) {
                CmsSetting::set($key, $request->input($field));
            }
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

        if ($request->boolean('save_and_preview')) {
            return redirect('/')->with('status', 'Forside gemt.');
        }

        return redirect('/cms/settings')->with('status', 'Forside gemt.');
    }

    private function deleteCurrentHero(): void
    {
        $current = CmsSetting::get('home_hero_image');
        if ($current && str_starts_with($current, 'img/uploads/')) {
            $path = public_path($current);
            if (is_file($path)) @unlink($path);
        }
    }

    public function index(Request $request)
    {
        $topLevel = CmsPage::whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('title')])
            ->orderBy('sort_order')->orderBy('title')
            ->get();

        // Selected top-level page (drives the inverted-box highlight and the
        // visible "Ny underside" affordance). Default = first top-level page.
        $selectedId = (int) $request->query('selected', 0);
        if (!$topLevel->firstWhere('id', $selectedId)) {
            $selectedId = (int) ($topLevel->first()?->id ?? 0);
        }

        return view('cms.index', compact('topLevel', 'selectedId'));
    }

    public function create(Request $request)
    {
        $parentId = $request->query('parent') ? (int) $request->query('parent') : null;
        $parents  = CmsPage::whereNull('parent_id')->orderBy('sort_order')->orderBy('title')->get();
        return view('cms.edit', [
            'page'    => new CmsPage([
                'is_visible' => true,
                'sort_order' => (CmsPage::max('sort_order') ?? 0) + 1,
                'parent_id'  => $parentId,
            ]),
            'parents' => $parents,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $page = CmsPage::create($data);
        $this->handleHeroImage($request, $page);
        return $this->afterSave($request, $page, 'Side oprettet.');
    }

    public function edit(CmsPage $page)
    {
        // A page cannot be its own ancestor — exclude it (and its descendants) from parent choices.
        $descendantIds = $this->descendantIds($page);
        $parents = CmsPage::whereNull('parent_id')
            ->whereNotIn('id', $descendantIds)
            ->orderBy('sort_order')->orderBy('title')
            ->get();
        return view('cms.edit', compact('page', 'parents'));
    }

    public function update(Request $request, CmsPage $page)
    {
        $page->update($this->validated($request, $page->id));
        $this->handleHeroImage($request, $page);
        return $this->afterSave($request, $page, 'Side opdateret.');
    }

    /**
     * Upload (or remove) the page's hero image. Stored under public/img/uploads/cms-pages.
     * The remove_hero flag, when set, deletes the current file without expecting an upload.
     */
    private function handleHeroImage(Request $request, CmsPage $page): void
    {
        // Catch PHP-level upload rejections (post_max_size etc.) before they
        // get swallowed silently by Laravel's nullable image validation.
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] !== UPLOAD_ERR_OK && $_FILES['hero_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $msg = match ($_FILES['hero_image']['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Billedet er for stort til at blive uploadet. Brug et mindre billede.',
                UPLOAD_ERR_PARTIAL    => 'Upload blev afbrudt midt i. Prøv igen.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server-fejl: ingen midlertidig mappe.',
                UPLOAD_ERR_CANT_WRITE => 'Server-fejl: kan ikke skrive billedet.',
                default               => 'Upload-fejl (kode '.$_FILES['hero_image']['error'].').',
            };
            throw \Illuminate\Validation\ValidationException::withMessages(['hero_image' => $msg]);
        }

        $request->validate([
            'hero_image'  => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:8192',
            'remove_hero' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_hero')) {
            $this->deleteHeroFile($page->hero_image);
            $page->update(['hero_image' => null]);
            return;
        }

        if ($request->hasFile('hero_image')) {
            $this->deleteHeroFile($page->hero_image);
            $file = $request->file('hero_image');
            $name = 'page-'.$page->id.'-'.now()->format('YmdHis').'.'.$file->getClientOriginalExtension();
            $dest = public_path('img/uploads/cms-pages');
            if (!is_dir($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $name);
            $page->update(['hero_image' => 'img/uploads/cms-pages/'.$name]);
        }
    }

    private function deleteHeroFile(?string $path): void
    {
        if (!$path || !str_starts_with($path, 'img/uploads/cms-pages/')) return;
        $abs = public_path($path);
        if (is_file($abs)) @unlink($abs);
    }

    private function afterSave(Request $request, CmsPage $page, string $msg)
    {
        if ($request->boolean('save_and_preview')) {
            return redirect($page->url())->with('status', $msg);
        }
        return redirect('/cms/'.$page->id.'/edit')->with('status', $msg);
    }

    public function destroy(CmsPage $page)
    {
        // Clean up any uploaded hero images (own + cascaded children's).
        foreach ($page->children as $child) $this->deleteHeroFile($child->hero_image);
        $this->deleteHeroFile($page->hero_image);
        $page->delete();
        return redirect('/cms')->with('status', 'Side slettet.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'parent_id'  => 'nullable|integer|exists:cms_pages,id',
            'title'      => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255',
            'content'    => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_visible' => 'nullable|boolean',
        ]);

        $data['slug']       = isset($data['slug']) && $data['slug'] !== ''
            ? trim($data['slug'], '/')
            : Str::slug($data['title']);
        $data['parent_id']  = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;
        $data['is_visible'] = (bool) ($data['is_visible'] ?? false);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Prevent picking yourself or a descendant as parent.
        if ($ignoreId && $data['parent_id']) {
            if ($data['parent_id'] === $ignoreId) abort(422, 'En side kan ikke være sin egen overside.');
            $page = CmsPage::find($ignoreId);
            if ($page && in_array($data['parent_id'], $this->descendantIds($page), true)) {
                abort(422, 'Oversiden kan ikke være en underside af sig selv.');
            }
            // Only one level deep — a subpage cannot itself have a parent that has a parent.
            $proposedParent = CmsPage::find($data['parent_id']);
            if ($proposedParent && $proposedParent->parent_id) {
                abort(422, 'Undersider kan kun ligge ét niveau dybt.');
            }
        }
        if (!$ignoreId && $data['parent_id']) {
            // Same one-level rule applies when creating.
            $proposedParent = CmsPage::find($data['parent_id']);
            if ($proposedParent && $proposedParent->parent_id) {
                abort(422, 'Undersider kan kun ligge ét niveau dybt.');
            }
        }

        // Slug uniqueness is scoped to the same parent — siblings share a namespace.
        $exists = CmsPage::where('slug', $data['slug'])
            ->where('parent_id', $data['parent_id'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
        if ($exists) {
            abort(422, 'En side med samme URL findes allerede under den valgte overside.');
        }

        return $data;
    }

    /** Return [$page->id, ...children ids...] so we can exclude them from parent options. */
    private function descendantIds(CmsPage $page): array
    {
        $ids = [$page->id];
        foreach ($page->children as $c) $ids[] = $c->id;
        return $ids;
    }
}
