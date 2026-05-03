@extends('cms.layout')

@section('title', $page->exists ? 'Rediger '.$page->title : 'Ny side')

@section('content')
    <div class="card">
        <h2 style="font-size:18px;margin-bottom:16px;">{{ $page->exists ? 'Rediger side' : 'Ny side' }}</h2>

        <form method="POST" action="{{ $page->exists ? '/cms/'.$page->id : '/cms' }}">
            @csrf
            @if($page->exists) @method('PATCH') @endif

            <div class="form-row">
                <label>Titel</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required>
            </div>

            <div class="form-row form-row--inline">
                <div>
                    <label>Slug (URL)</label>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="f.eks. simulationer (tom = forside)">
                </div>
                <div>
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
                <label>Indhold</label>
                <div id="editor"></div>
                <textarea name="content" id="content-input" style="display:none;">{{ old('content', $page->content) }}</textarea>
                <details style="margin-top:8px;">
                    <summary style="cursor:pointer;font-size:13px;color:#888;">Vis HTML-kilde</summary>
                    <textarea id="html-source" style="margin-top:8px;min-height:160px;" oninput="syncFromSource(this.value)">{{ old('content', $page->content) }}</textarea>
                </details>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button class="btn" type="submit">Gem</button>
                <a href="/cms" class="btn btn--secondary">Annuller</a>
            </div>
        </form>
    </div>

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

        document.querySelector('form').addEventListener('submit', () => {
            hidden.value = quill.root.innerHTML;
        });
    </script>
@endsection
