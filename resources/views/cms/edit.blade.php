@extends('cms.layout')

@section('title', $page->exists ? 'Rediger '.$page->title : 'Ny side')

@section('content')
    <div class="card">
        <h2 style="font-size:18px;margin-bottom:16px;">{{ $page->exists ? 'Rediger side' : 'Ny side' }}</h2>

        <form method="POST" action="{{ $page->exists ? '/cms/'.$page->id : '/cms' }}" enctype="multipart/form-data">
            @csrf
            @if($page->exists) @method('PATCH') @endif

            <div class="form-row">
                <label>Titel</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required>
            </div>

            <div class="form-row form-row--inline">
                <div>
                    <label>Overside</label>
                    <select name="parent_id" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;font-size:14px;font-family:inherit;background:#fff;">
                        <option value="">— Ingen (topside)</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}"
                                {{ (int) old('parent_id', $page->parent_id) === $parent->id ? 'selected' : '' }}>
                                {{ $parent->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Slug (URL-segment)</label>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="f.eks. team (tom = forside)">
                </div>
                <div style="max-width:120px;">
                    <label>Sortering</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}">
                </div>
            </div>

            <div class="form-row">
                <label class="checkbox">
                    <input type="hidden" name="is_visible" value="0">
                    <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', $page->is_visible) ? 'checked' : '' }}>
                    Synlig i menu
                </label>
            </div>

            <div class="form-row">
                <label>Hero-billede</label>
                @if($page->hero_image)
                    <div style="margin-bottom:10px;display:flex;gap:14px;align-items:center;background:var(--line-soft);padding:10px;border-radius:8px;">
                        <img src="{{ asset($page->hero_image) }}" alt="Hero" style="width:120px;height:72px;object-fit:cover;border-radius:6px;display:block;">
                        <div style="flex:1;font-size:13px;color:var(--ink-soft);">
                            Nuværende billede. Upload nyt for at erstatte, eller fjern det:
                            <label class="checkbox" style="margin-top:8px;">
                                <input type="checkbox" name="remove_hero" value="1">
                                <span>Fjern hero-billedet</span>
                            </label>
                        </div>
                    </div>
                @endif
                <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp,image/gif">
                <div style="font-size:12px;color:var(--ink-mute);margin-top:6px;">Vises øverst på siden. JPG, PNG, WebP eller GIF. Maks 8 MB.</div>
            </div>

            <div class="form-row">
                <label>Indhold</label>
                <div id="editor"></div>
                <textarea name="content" id="content-input" style="display:none;">{{ old('content', $page->content) }}</textarea>
                <details style="margin-top:8px;">
                    <summary style="cursor:pointer;font-size:13px;color:#888;">Vis HTML-kilde</summary>
                    <textarea id="html-source" style="margin-top:8px;min-height:160px;" oninput="syncFromSource(this.value)">{{ old('content', $page->content) }}</textarea>
                </details>

                <details style="margin-top:10px;">
                    <summary style="cursor:pointer;font-size:13px;color:#888;">Genveje — indsæt i indholdet</summary>
                    <div style="margin-top:10px;display:grid;gap:10px;">
                        <div class="snippet">
                            <div class="snippet__head">
                                <div>
                                    <strong>Kontaktformular</strong>
                                    <span class="snippet__hint">Indsæt koden — bliver til en spamfiltret formular på siden.</span>
                                </div>
                                <button type="button" class="btn btn--secondary btn--sm" onclick="copySnippet(this)" data-snippet="[kontaktform]">Kopiér</button>
                            </div>
                            <code class="snippet__code">[kontaktform]</code>
                        </div>
                    </div>
                </details>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <button class="btn" type="submit" name="save_and_preview" value="1">Gem</button>
                @if($page->exists)
                    <span style="flex:1;"></span>
                    <button type="button" class="btn btn--danger" onclick="document.getElementById('deletePageForm').requestSubmit()">Slet siden</button>
                @endif
            </div>
        </form>

        @if($page->exists)
            <form id="deletePageForm" method="POST" action="/cms/{{ $page->id }}" onsubmit="return confirm('Slet siden?');" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>

    <style>
        .snippet {
            background: var(--line-soft);
            border-radius: 8px;
            padding: 12px 14px;
        }
        .snippet__head {
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; margin-bottom: 8px;
        }
        .snippet__hint { display: block; font-size: 12px; color: var(--ink-mute); font-weight: 400; margin-top: 2px; }
        .snippet__code {
            display: block;
            background: var(--surface);
            padding: 8px 10px;
            border-radius: 6px;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 13px;
            color: var(--ink);
        }
    </style>
    <script>
        function copySnippet(btn) {
            const text = btn.getAttribute('data-snippet');
            navigator.clipboard.writeText(text).then(() => {
                const original = btn.textContent;
                btn.textContent = 'Kopieret!';
                setTimeout(() => { btn.textContent = original; }, 1400);
            });
        }
    </script>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <style>
        #editor { background: #fff; border-radius: 0 0 4px 4px; min-height: 320px; }
        .ql-toolbar.ql-snow, .ql-container.ql-snow { border-color: #ccc; }
        .ql-toolbar.ql-snow { border-radius: 4px 4px 0 0; background: #fafafa; }
        .ql-editor { min-height: 320px; font-size: 15px; line-height: 1.6; }
    </style>
    <script>
        const quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Skriv indhold her...',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    ['blockquote', 'code-block'],
                    ['link', 'image', 'video'],
                    ['clean'],
                ],
            },
        });

        const initial = document.getElementById('content-input').value;
        if (initial) quill.clipboard.dangerouslyPasteHTML(initial);

        const hidden = document.getElementById('content-input');
        const source = document.getElementById('html-source');

        quill.on('text-change', () => {
            const html = quill.root.innerHTML;
            hidden.value = html;
            source.value = html;
        });

        function syncFromSource(html) {
            hidden.value = html;
            quill.clipboard.dangerouslyPasteHTML(html);
        }

        document.querySelectorAll('form').forEach(f => {
            f.addEventListener('submit', () => {
                hidden.value = quill.root.innerHTML;
            });
        });
    </script>
@endsection
