@extends('cms.layout')

@section('title', 'Forside')

@section('content')
    <div class="card">
        <h2 style="font-size:18px;margin-bottom:16px;">Forside · hero-billede</h2>

        <p style="color:#666;font-size:14px;margin-bottom:20px;">
            Vises i højre side af forsidens hero-sektion. Skaleres så det dækker hele området.
        </p>

        @if($heroImage)
            <div style="margin-bottom:20px;">
                <label>Nuværende billede</label>
                <img src="{{ asset($heroImage) }}?v={{ time() }}" alt="Hero" style="max-width:100%;max-height:320px;border:1px solid #e0e0e0;border-radius:4px;">
                <div style="margin-top:8px;font-size:13px;color:#888;"><code>{{ $heroImage }}</code></div>
            </div>
        @else
            <div style="margin-bottom:20px;color:#888;font-size:14px;">
                Intet brugerdefineret billede sat — forsiden bruger standardbilledet
                <code>img/hero-feed.png</code>.
            </div>
        @endif

        <form method="POST" action="/cms/settings" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <label>Upload nyt billede</label>
                <input type="file" name="hero_image" accept="image/png,image/jpeg,image/webp,image/gif">
                <div style="font-size:12px;color:#888;margin-top:4px;">JPG, PNG, WebP eller GIF · maks 5 MB</div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <button class="btn" type="submit">Gem billede</button>
                @if($heroImage)
                    <button class="btn btn--danger" type="submit" name="remove_hero" value="1"
                        onclick="return confirm('Fjern hero-billedet og gå tilbage til standard?');">
                        Fjern billede
                    </button>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <h2 style="font-size:18px;margin-bottom:8px;">Forside · indhold under hero</h2>
        <p style="color:#666;font-size:14px;margin-bottom:16px;">
            Det HTML-indhold der vises under hero-sektionen på forsiden. Lad være tom for at skjule blokken.
        </p>

        <form method="POST" action="/cms/settings">
            @csrf

            <div class="form-row">
                <label>Indhold</label>
                <div id="editor"></div>
                <textarea name="home_content" id="content-input" style="display:none;">{{ old('home_content', $homeContent) }}</textarea>
                <details style="margin-top:8px;">
                    <summary style="cursor:pointer;font-size:13px;color:#888;">Vis HTML-kilde</summary>
                    <textarea id="html-source" style="margin-top:8px;min-height:160px;" oninput="syncFromSource(this.value)">{{ old('home_content', $homeContent) }}</textarea>
                </details>
                <div id="preview" style="display:none;margin-top:12px;border:1px solid #e0e0e0;border-radius:4px;padding:20px;background:#fff;"></div>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <button class="btn" type="submit">Gem indhold</button>
                <button type="button" class="btn btn--secondary" onclick="togglePreview(this)">Preview</button>
                <a href="/" class="btn btn--secondary" target="_blank">Vis forside ↗</a>
                <button class="btn btn--danger" type="submit" name="reset_home_content" value="1"
                    onclick="return confirm('Nulstil til standardskabelonen?');">
                    Nulstil til skabelon
                </button>
            </div>
        </form>
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
        const initial = hidden.value;
        if (initial) quill.clipboard.dangerouslyPasteHTML(initial);

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

        function togglePreview(btn) {
            const pane = document.getElementById('preview');
            if (pane.style.display === 'none') {
                pane.innerHTML = quill.root.innerHTML;
                pane.style.display = 'block';
                btn.textContent = 'Skjul preview';
            } else {
                pane.style.display = 'none';
                btn.textContent = 'Preview';
            }
        }
    </script>
@endsection
