@extends('cms.layout')

@section('title', $page->exists ? 'Rediger '.$page->title : 'Ny side')

@section('content')
    <div class="card">
        <h2 style="font-size:18px;margin-bottom:16px;">{{ $page->exists ? 'Rediger side' : 'Ny side' }}</h2>

        <form method="POST" action="{{ $page->exists ? '/cms/'.$page->id : '/cms' }}" enctype="multipart/form-data" id="page-form">
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
                <button class="btn btn--secondary" type="submit" name="save_and_preview" value="1" data-skip-spellcheck="1">Gem</button>
                <button class="btn" type="submit" name="save_and_preview" value="1">Gem og tjek stavning</button>
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

    {{-- Spellcheck modal --}}
    <div id="sc-overlay" style="display:none;">
        <div id="sc-modal" role="dialog" aria-labelledby="sc-title">
            <header>
                <h3 id="sc-title">Stave- og grammatiktjek</h3>
                <button type="button" class="iconbtn" onclick="scClose()" aria-label="Luk">×</button>
            </header>
            <div id="sc-body">
                <div id="sc-loading" style="padding:24px;text-align:center;color:var(--ink-mute);">Tjekker teksten…</div>
                <div id="sc-empty" style="display:none;padding:24px;text-align:center;color:var(--success);">Ingen stavefejl fundet ✓</div>
                <div id="sc-error" style="display:none;padding:24px;text-align:center;color:var(--danger);"></div>
                <div id="sc-list" style="display:none;"></div>
            </div>
            <footer>
                <label class="checkbox" style="margin-right:auto;font-size:13px;">
                    <input type="checkbox" id="sc-toggle-all" checked>
                    <span>Vælg alle</span>
                </label>
                <button type="button" class="btn btn--ghost" onclick="scSkip()">Gem uden rettelser</button>
                <button type="button" class="btn" id="sc-apply-btn" onclick="scApplyAndSave()">Ret valgte og gem</button>
            </footer>
        </div>
    </div>
    <style>
        #sc-overlay {
            position: fixed; inset: 0;
            background: rgba(15,23,42,0.45);
            display: flex; align-items: center; justify-content: center;
            z-index: 1000; padding: 20px;
        }
        #sc-modal {
            background: var(--surface); border-radius: 14px;
            box-shadow: 0 20px 60px -20px rgba(0,0,0,0.4);
            width: 100%; max-width: 720px; max-height: 85vh;
            display: flex; flex-direction: column;
        }
        #sc-modal header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 22px; border-bottom: 1px solid var(--line);
        }
        #sc-modal header h3 { font-size: 16px; font-weight: 700; margin: 0; }
        #sc-body { flex: 1; overflow-y: auto; }
        #sc-modal footer {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 22px; border-top: 1px solid var(--line);
            background: var(--line-soft); border-radius: 0 0 14px 14px;
        }
        .sc-item {
            display: flex; gap: 12px; align-items: flex-start;
            padding: 14px 22px; border-bottom: 1px solid var(--line-soft);
        }
        .sc-item:last-child { border-bottom: 0; }
        .sc-item input[type=checkbox] { margin-top: 4px; flex-shrink: 0; }
        .sc-item .sc-text { flex: 1; min-width: 0; }
        .sc-diff { font-size: 14px; line-height: 1.5; word-break: break-word; }
        .sc-diff del {
            background: var(--danger-soft); color: var(--danger);
            text-decoration: line-through; padding: 1px 4px; border-radius: 3px;
        }
        .sc-diff ins {
            background: var(--success-soft); color: #166534;
            text-decoration: none; padding: 1px 4px; border-radius: 3px;
            margin-left: 4px;
        }
        .sc-suggestion {
            display: inline-block;
            width: auto; min-width: 120px;
            background: var(--success-soft); color: #166534;
            border: 1px solid #bbf7d0; border-radius: 3px;
            padding: 2px 6px; margin-left: 6px;
            font-size: 14px; font-family: inherit;
        }
        .sc-suggestion:focus {
            outline: none; border-color: var(--success);
            box-shadow: 0 0 0 2px rgba(22,163,74,0.15);
        }
        .sc-explain { font-size: 12px; color: var(--ink-mute); margin-top: 4px; text-transform: lowercase; }
    </style>

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

        // Spellcheck-aware submit. Intercepts the page form, runs the LLM check,
        // shows the modal, then submits (optionally after applying selected fixes).
        const pageForm = document.getElementById('page-form');
        let scSkipCheck = false;
        let scSubmitter = null; // remember which button was clicked, so the redirect target is preserved

        pageForm.addEventListener('submit', (ev) => {
            hidden.value = quill.root.innerHTML;
            if (scSkipCheck) return; // already approved via the modal
            // The plain "Gem" button bypasses the spellcheck entirely.
            if (ev.submitter && ev.submitter.dataset.skipSpellcheck === '1') return;
            ev.preventDefault();
            scSubmitter = ev.submitter || pageForm.querySelector('button[type=submit]');
            scOpen();
            scRun();
        });

        // ---- Modal state + helpers ----
        let scErrors = [];
        const scOverlay = document.getElementById('sc-overlay');
        const scLoading = document.getElementById('sc-loading');
        const scEmpty   = document.getElementById('sc-empty');
        const scErrBox  = document.getElementById('sc-error');
        const scList    = document.getElementById('sc-list');
        const scApplyBtn= document.getElementById('sc-apply-btn');
        const scToggleAll = document.getElementById('sc-toggle-all');

        scOverlay.addEventListener('click', (e) => { if (e.target === scOverlay) scClose(); });
        scToggleAll.addEventListener('change', (e) => {
            document.querySelectorAll('#sc-list input[type=checkbox]').forEach(cb => cb.checked = e.target.checked);
        });

        function scOpen() {
            scOverlay.style.display = 'flex';
            scLoading.style.display = 'block';
            scEmpty.style.display = 'none';
            scErrBox.style.display = 'none';
            scList.style.display = 'none';
            scList.innerHTML = '';
            scApplyBtn.disabled = true;
        }
        function scClose() { scOverlay.style.display = 'none'; }
        function scSkip() {
            scSkipCheck = true;
            scClose();
            // Replay the original submitter so save_and_preview etc. carry through.
            if (scSubmitter && pageForm.contains(scSubmitter)) {
                pageForm.requestSubmit(scSubmitter);
            } else {
                pageForm.requestSubmit();
            }
        }

        function scEscape(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        async function scRun() {
            const csrf = pageForm.querySelector('input[name=_token]').value;
            try {
                const resp = await fetch('/cms/spellcheck', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ content: hidden.value }),
                });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                const json = await resp.json();
                scLoading.style.display = 'none';
                scErrors = json.errors || [];
                if (scErrors.length === 0) {
                    scEmpty.style.display = 'block';
                    if (json.note) {
                        scErrBox.textContent = json.note;
                        scErrBox.style.display = 'block';
                    }
                    setTimeout(() => { scSkip(); }, 700);
                    return;
                }
                scList.innerHTML = scErrors.map(e => `
                    <div class="sc-item">
                        <input type="checkbox" data-err-id="${scEscape(e.id)}" checked>
                        <div class="sc-text">
                            <div class="sc-diff">
                                <del>${scEscape(e.original)}</del>
                                <input type="text" class="sc-suggestion" data-err-id="${scEscape(e.id)}" value="${scEscape(e.suggestion)}">
                            </div>
                            ${e.explanation ? `<div class="sc-explain">${scEscape(e.explanation)}</div>` : ''}
                        </div>
                    </div>
                `).join('');
                scList.style.display = 'block';
                scApplyBtn.disabled = false;
            } catch (err) {
                scLoading.style.display = 'none';
                scErrBox.textContent = 'Tjek kunne ikke gennemføres. Prøver at gemme alligevel…';
                scErrBox.style.display = 'block';
                setTimeout(() => { scSkip(); }, 1200);
            }
        }

        function scApplyAndSave() {
            const selected = new Set(
                Array.from(document.querySelectorAll('#sc-list input[type=checkbox]:checked'))
                    .map(cb => cb.dataset.errId)
            );
            // Read possibly-edited suggestions back from the inputs.
            const overrides = {};
            document.querySelectorAll('#sc-list .sc-suggestion').forEach(inp => {
                overrides[inp.dataset.errId] = inp.value;
            });

            let html = quill.root.innerHTML;
            for (const e of scErrors) {
                if (!selected.has(e.id)) continue;
                const replacement = overrides[e.id] ?? e.suggestion;
                if (replacement === e.original) continue;
                // Try the original substring; if not present (HTML entities, whitespace),
                // try an entity-encoded variant before giving up.
                const variants = [e.original, scHtmlEntities(e.original)];
                for (const needle of variants) {
                    const idx = html.indexOf(needle);
                    if (idx !== -1) {
                        html = html.slice(0, idx) + replacement + html.slice(idx + needle.length);
                        break;
                    }
                }
            }
            quill.clipboard.dangerouslyPasteHTML(html);
            hidden.value = quill.root.innerHTML;
            source.value = hidden.value;
            scSkip();
        }

        function scHtmlEntities(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }
    </script>
@endsection
