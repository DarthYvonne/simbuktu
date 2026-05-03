@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Opsætning</h1>
  <div style="color: #65676b; font-size: 13px;">Skriv et opslag, vælg personas, se hvad de ville svare.</div>
</div>

@php $base = '/slophub/admin/populations/'.$population->id; @endphp
@include('admin._opsaetning_tabs')

@if (count($personas) === 0)
  <div class="card" style="text-align: center; padding: 30px;">
    Ingen personas at teste mod. <a href="{{ url("$base/personas") }}" style="color: #1877f2;">Generér nogle først</a>.
  </div>
@else

<style>
.composer { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 14px; }
.composer-head { display: flex; align-items: center; gap: 10px; padding-bottom: 12px; border-bottom: 1px solid #dadde1; margin-bottom: 12px; }
.composer-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #a1c4fd, #c2e9fb); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
.composer-field { border: 1px solid #dadde1; border-radius: 10px; background: #f8f9fa; padding: 12px 14px; transition: border 0.15s, background 0.15s; }
.composer-field:focus-within { border-color: #1877f2; background: #fff; }
.composer textarea { width: 100%; border: none; resize: vertical; font-size: 16px; line-height: 1.5; min-height: 100px; outline: none; font-family: inherit; background: transparent; }
.composer textarea::placeholder { color: #8a8d91; }
.preview-wrap { margin-top: 12px; position: relative; border-radius: 10px; overflow: hidden; border: 1px solid #dadde1; }
.preview-wrap .close { position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.6); color: #fff; border: none; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; z-index: 5; }
.img-preview { max-height: 400px; width: 100%; object-fit: cover; display: block; }
.link-card { display: flex; background: #f0f2f5; }
.link-card img { width: 160px; height: 100%; min-height: 100px; object-fit: cover; flex-shrink: 0; background: #e4e6eb; }
.link-card .link-meta { padding: 10px 14px; flex: 1; min-width: 0; }
.link-card .site { font-size: 11px; color: #65676b; text-transform: uppercase; letter-spacing: 0.5px; }
.link-card .title { font-weight: 600; font-size: 15px; margin: 2px 0; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.link-card .desc { font-size: 13px; color: #65676b; line-height: 1.35; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.link-loading { padding: 14px; color: #65676b; font-size: 13px; text-align: center; }
.attach-bar { display: flex; align-items: center; justify-content: space-between; border: 1px solid #dadde1; border-radius: 10px; padding: 10px 14px; margin-top: 12px; }
.attach-bar span { font-weight: 600; font-size: 13px; }
.attach-btn { background: transparent; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 20px; color: #65676b; }
.attach-btn:hover { background: #f0f2f5; }
.submit-row { margin-top: 14px; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
.image-desc { background: #fef9e7; border-left: 3px solid #f59e0b; padding: 8px 12px; border-radius: 6px; font-size: 12px; line-height: 1.45; margin-top: 8px; color: #1c1e21; }
.image-desc strong { color: #92400e; }
</style>

<div class="composer">
  <form method="POST" action="{{ url("$base/personas/tester") }}" enctype="multipart/form-data" id="testForm">
    @csrf
    <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;">

    <div class="composer-head">
      <div class="composer-avatar">TS</div>
      <div>
        <strong>Test-opslag</strong>
        <div style="color: #65676b; font-size: 12px;">Sandkasse — gemmes ikke som rigtigt opslag</div>
      </div>
    </div>

    <div class="composer-field">
      <textarea name="post" id="bodyInput" placeholder="Skriv et opslag at teste..." required>{{ $postText }}</textarea>
    </div>

    <div id="imagePreview" class="preview-wrap" style="display: none;">
      <button type="button" class="close" onclick="clearImage()"><i class="fa-solid fa-xmark"></i></button>
      <img class="img-preview" id="imagePreviewImg">
    </div>

    <div id="linkPreview" class="preview-wrap" style="display: none;">
      <button type="button" class="close" onclick="clearLinkPreview(true)"><i class="fa-solid fa-xmark"></i></button>
      <div id="linkCard"></div>
    </div>

    <div class="attach-bar">
      <span>Tilføj til opslaget</span>
      <button type="button" class="attach-btn" onclick="document.getElementById('imageInput').click()" title="Billede"><i class="fa-regular fa-image"></i></button>
    </div>

    <div class="submit-row">
      <label style="font-size: 13px; color: #65676b;">Antal tilfældige personas (hvis ingen valgt): <input type="number" name="count" value="5" min="1" max="20" style="width: 70px; padding: 4px 8px; border: 1px solid #dadde1; border-radius: 4px;"></label>
      <button class="btn btn-primary" id="reactBtn"><i class="fa-solid fa-play"></i> Lad personas reagere</button>
    </div>

    <details style="margin-top: 14px;">
      <summary style="cursor: pointer; color: #1877f2; font-weight: 600; font-size: 13px;">Vælg specifikke personas (valgfrit)</summary>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 8px; margin-top: 10px; max-height: 400px; overflow-y: auto; padding: 10px; background: #f8f9fa; border-radius: 8px;">
        @foreach ($personas as $p)
          <label style="display: flex; gap: 8px; align-items: center; padding: 6px 8px; background: #fff; border-radius: 6px; cursor: pointer; font-size: 13px;">
            <input type="checkbox" name="persona_ids[]" value="{{ $p['id'] }}" {{ ($selectedId ?? null) === $p['id'] ? 'checked' : '' }}>
            <span><strong>{{ $p['name'] }}</strong>, {{ $p['demographics']['age'] }}<br><span style="color: #65676b; font-size: 11px;">{{ implode(', ', $p['subcultures']) }}</span></span>
          </label>
        @endforeach
      </div>
    </details>
  </form>
</div>

@if ($batchId || !empty($reactions))
<div class="card" id="resultsCard">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 14px;">
    <div style="flex: 1;">
      <div style="background: #f0f2f5; padding: 12px 16px; border-radius: 8px; font-size: 14px;">
        <strong>Opslag:</strong> {{ $postText }}
      </div>
      @if ($imagePath)
        <img src="{{ Storage::url($imagePath) }}" style="max-height: 200px; border-radius: 8px; margin-top: 8px;">
      @endif
      @if ($imageDescription)
        <div class="image-desc">
          <strong>Personas ser dette på billedet:</strong> {{ $imageDescription }}
        </div>
      @endif
      @if ($linkPreview && !empty($linkPreview['title']))
        <div style="margin-top: 8px; padding: 10px 12px; border: 1px solid #dadde1; border-radius: 8px; font-size: 13px;">
          <div style="font-weight: 600;">{{ $linkPreview['title'] }}</div>
          <div style="color: #65676b;">{{ $linkPreview['description'] }}</div>
        </div>
      @endif
    </div>
    <span id="progStatus" style="color: #65676b; font-size: 13px; min-width: 100px; text-align: right;">
      <span id="progLabel">{{ $done }} / {{ $queued }}</span>
      @if ($done < $queued)<span class="spinner" style="border-color: #1877f2; border-top-color: transparent; margin-left: 6px;"></span>@endif
    </span>
  </div>

  <div id="reactionsList"></div>
</div>
@endif
@endif

<script>
const bodyEl = document.getElementById('bodyInput');
const imgInput = document.getElementById('imageInput');
const imgPreview = document.getElementById('imagePreview');
const imgEl = document.getElementById('imagePreviewImg');
const linkPreviewEl = document.getElementById('linkPreview');
const linkCard = document.getElementById('linkCard');
let lastUrl = null;
let userClearedLink = false;
let debounceTimer = null;

imgInput?.addEventListener('change', (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    imgEl.src = ev.target.result;
    imgPreview.style.display = 'block';
    linkPreviewEl.style.display = 'none';
  };
  reader.readAsDataURL(file);
});

function clearImage() {
  imgInput.value = '';
  imgPreview.style.display = 'none';
  checkForLink();
}

function extractUrl(text) {
  const m = text.match(/https?:\/\/[^\s<>"']+/i);
  return m ? m[0].replace(/[.,;:!?)]+$/, '') : null;
}

async function checkForLink() {
  if (imgInput.files.length > 0) { linkPreviewEl.style.display = 'none'; return; }
  const url = extractUrl(bodyEl.value);
  if (!url) { linkPreviewEl.style.display = 'none'; lastUrl = null; return; }
  if (url === lastUrl || userClearedLink) return;
  lastUrl = url;

  linkPreviewEl.style.display = 'block';
  linkCard.innerHTML = '<div class="link-loading">Henter preview...</div>';
  try {
    const res = await fetch('{{ url("/slophub/posts/link-preview") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify({ url })
    });
    const data = await res.json();
    if (!data) { linkCard.innerHTML = '<div class="link-loading">Kunne ikke hente preview.</div>'; return; }
    linkCard.innerHTML = `
      <div class="link-card">
        ${data.image ? `<img src="${escapeAttr(data.image)}" onerror="this.style.display='none'">` : ''}
        <div class="link-meta">
          <div class="site">${escapeHtml(data.site_name || '')}</div>
          <div class="title">${escapeHtml(data.title || url)}</div>
          <div class="desc">${escapeHtml(data.description || '')}</div>
        </div>
      </div>`;
  } catch { linkCard.innerHTML = '<div class="link-loading">Kunne ikke hente preview.</div>'; }
}

function clearLinkPreview(byUser) {
  linkPreviewEl.style.display = 'none';
  if (byUser) userClearedLink = true;
}

bodyEl?.addEventListener('input', () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(checkForLink, 500);
});

function escapeHtml(s) { return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function escapeAttr(s) { return escapeHtml(s); }

document.getElementById('testForm')?.addEventListener('submit', function() {
  document.getElementById('reactBtn').disabled = true;
});

@if ($batchId)
const STATUS_URL = '{{ url("$base/personas/tester/status") }}';
const PERSONA_URL = '{{ url("$base/personas") }}';
const renderedIds = new Set();

function renderReaction(r) {
  const p = r.persona;
  const subs = (p.subcultures || []).map(s => `<span class="tag sub" style="font-size: 10px; padding: 1px 6px;">${escapeHtml(s)}</span>`).join('');
  const initials = (p.name || '?').substring(0, 2).toUpperCase();
  const img = p.image_file
    ? `<img src="${PERSONA_URL}/${p.id}/image" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;">`
    : `<div style="width:40px;height:40px;border-radius:50%;background:#e4e6eb;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;">${initials}</div>`;
  const body = r.error
    ? `<div style="background:#fee2e2;color:#b91c1c;padding:8px 12px;border-radius:8px;margin-top:6px;font-size:12px;">Fejl: ${escapeHtml(r.error)}</div>`
    : `<div style="background:#f0f2f5;padding:10px 14px;border-radius:14px;margin-top:6px;line-height:1.45;">${escapeHtml(r.text || '')}</div>`;
  return `<div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #f0f2f5;">${img}<div style="flex:1;">
    <div style="display:flex;gap:8px;align-items:baseline;flex-wrap:wrap;">
      <a href="${PERSONA_URL}/${p.id}" style="font-weight:600;color:#050505;">${escapeHtml(p.name)}</a>
      <span style="color:#65676b;font-size:12px;">${p.demographics.age} · ${escapeHtml(p.demographics.occupation_hint)} · ${escapeHtml(p.politics.party)}</span>
    </div>
    <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:4px;">${subs}</div>
    ${body}
  </div></div>`;
}

async function pollTester() {
  try {
    const res = await fetch(STATUS_URL);
    const data = await res.json();
    document.getElementById('progLabel').textContent = `${data.done} / ${data.queued}`;
    const list = document.getElementById('reactionsList');
    data.reactions.forEach(r => {
      const key = r.persona.id + (r.error ? ':err' : '');
      if (renderedIds.has(key)) return;
      renderedIds.add(key);
      list.insertAdjacentHTML('beforeend', renderReaction(r));
    });
    if (data.done >= data.queued && data.queued > 0) {
      document.getElementById('progStatus').innerHTML = '<span style="color:#22c55e;">✓ Færdig</span>';
      clearInterval(testerTimer);
    }
  } catch (e) {}
}
const testerTimer = setInterval(pollTester, 2000);
pollTester();
@endif
</script>
@endsection
