@extends('cms.layout')

@section('title', 'Forside')

@section('content')
    <div class="card">
        <h2 style="font-size:18px;margin-bottom:16px;">Forside</h2>

        <form method="POST" action="/cms/settings" enctype="multipart/form-data">
            @csrf

            <h3 style="font-size:15px;font-weight:600;margin:8px 0 12px;color:#555;">Hero-tekst</h3>

            <div class="form-row">
                <label>Overskrift</label>
                <textarea name="hero_headline" rows="2" style="min-height:60px;font-family:inherit;">{{ old('hero_headline', $heroHeadline) }}</textarea>
            </div>

            <div class="form-row">
                <label>Underoverskrift</label>
                <textarea name="hero_subhead" rows="3" style="min-height:80px;font-family:inherit;">{{ old('hero_subhead', $heroSubhead) }}</textarea>
            </div>

            <div class="form-row form-row--inline">
                <div>
                    <label>Knap-tekst</label>
                    <input type="text" name="hero_button_text" value="{{ old('hero_button_text', $heroButtonText) }}" placeholder="f.eks. Udforsk nu">
                    <div style="font-size:12px;color:#888;margin-top:4px;">Lad være tom for at skjule knappen.</div>
                </div>
                <div>
                    <label>Knap-link</label>
                    <input type="text" name="hero_button_url" value="{{ old('hero_button_url', $heroButtonUrl) }}" placeholder="/kontakt eller https://...">
                </div>
            </div>

            <h3 style="font-size:15px;font-weight:600;margin:24px 0 12px;color:#555;">Hero-billede</h3>

            @if($heroImage)
                <div style="margin-bottom:12px;">
                    <img src="{{ asset($heroImage) }}?v={{ time() }}" alt="Hero" style="max-width:100%;max-height:240px;border:1px solid #e0e0e0;border-radius:4px;">
                    <div style="margin-top:6px;font-size:13px;color:#888;"><code>{{ $heroImage }}</code></div>
                </div>
            @else
                <div style="margin-bottom:12px;color:#888;font-size:14px;">
                    Bruger standardbilledet <code>img/hero-feed.png</code>.
                </div>
            @endif

            <div class="form-row">
                <label>Erstat billede</label>
                <input type="file" name="hero_image" accept="image/png,image/jpeg,image/webp,image/gif">
                <div style="font-size:12px;color:#888;margin-top:4px;">JPG, PNG, WebP eller GIF · maks 5 MB</div>
            </div>

            <h3 style="font-size:15px;font-weight:600;margin:24px 0 12px;color:#555;">Indhold under hero</h3>

            <div class="form-row">
                <div id="editor"></div>
                <textarea name="home_content" id="content-input" style="display:none;">{{ old('home_content', $homeContent) }}</textarea>
                <details style="margin-top:8px;">
                    <summary style="cursor:pointer;font-size:13px;color:#888;">Vis HTML-kilde</summary>
                    <textarea id="html-source" style="margin-top:8px;min-height:160px;" oninput="syncFromSource(this.value)">{{ old('home_content', $homeContent) }}</textarea>
                </details>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;border-top:1px solid #eee;padding-top:16px;">
                <button class="btn" type="submit">Gem</button>
                <button class="btn btn--secondary" type="submit" name="save_and_preview" value="1">Gem og preview</button>
            </div>
        </form>

        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:16px;">
            @if($heroImage)
                <form method="POST" action="/cms/settings" style="display:inline;">
                    @csrf
                    <button class="btn btn--danger" type="submit" name="remove_hero" value="1"
                        onclick="return confirm('Fjern hero-billedet og gå tilbage til standard?');">
                        Fjern hero-billede
                    </button>
                </form>
            @endif
            <form method="POST" action="/cms/settings" style="display:inline;">
                @csrf
                <button class="btn btn--danger" type="submit" name="reset_home_content" value="1"
                    onclick="return confirm('Nulstil indhold til standardskabelonen?');">
                    Nulstil indhold til skabelon
                </button>
            </form>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <style>
        #editor { background: #fff; border-radius: 0 0 4px 4px; min-height: 280px; }
        .ql-toolbar.ql-snow, .ql-container.ql-snow { border-color: #ccc; }
        .ql-toolbar.ql-snow { border-radius: 4px 4px 0 0; background: #fafafa; }
        .ql-editor { min-height: 280px; font-size: 15px; line-height: 1.6; }
    </style>
    <script>
        const quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Skriv indhold til forsiden...',
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

        const hidden = document.getElementById('content-input');
        const source = document.getElementById('html-source');
        if (hidden.value) quill.clipboard.dangerouslyPasteHTML(hidden.value);

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
                if (hidden) hidden.value = quill.root.innerHTML;
            });
        });
    </script>
@endsection
