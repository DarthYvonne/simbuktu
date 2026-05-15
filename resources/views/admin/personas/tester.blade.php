@extends('layouts.app')
@section('content')

@if (($context ?? 'population') === 'system')
  <div class="view-header">
    <h1>System @if ($course)<span style="font-weight: 400; color: #65676b; font-size: 14px;">· {{ $course->name }}</span>@endif</h1>
  </div>
  @include('admin._opsaetning_tabs')
@else
  @include('admin.populations.personlighed._header', ['urlBase' => $urlBase, 'population' => $population])
@endif

@if (!$population || count($personas) === 0)
  <div class="card" style="text-align: center; padding: 30px;">
    Ingen personas at teste mod. Vælg et kursus med population, eller generér personas først.
  </div>
@elseif ($posts->isEmpty())
  <div class="card" style="text-align: center; padding: 30px;">
    Ingen opslag i dette kursus endnu.
  </div>
@else

@php
  $selectedPersona = collect($personas)->firstWhere('id', $selectedPersonaId);
  $selectedPost    = $posts->firstWhere('id', (int) $selectedPostId);
@endphp

<style>
.tester-grid { display: grid; grid-template-columns: 1fr 1fr 1.4fr; gap: 12px; }
@media (max-width: 1100px) { .tester-grid { grid-template-columns: 1fr; } }
.tester-col { background: #fff; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); display: flex; flex-direction: column; min-height: 480px; max-height: 720px; }
.tester-col h3 { margin: 0; padding: 12px 14px; border-bottom: 1px solid #dadde1; font-size: 14px; font-weight: 700; }
.tester-col .col-search { padding: 8px 10px; border-bottom: 1px solid #f0f2f5; }
.tester-col .col-search input { width: 100%; padding: 6px 10px; border: 1px solid #dadde1; border-radius: 6px; font-size: 13px; }
.tester-col .col-body { overflow-y: auto; flex: 1; padding: 4px; }
.tester-item { display: flex; gap: 10px; padding: 8px 10px; padding-right: 30px; border-radius: 8px; cursor: pointer; font-size: 13px; line-height: 1.35; position: relative; }
.tester-item .open-btn { position: absolute; top: 6px; right: 6px; width: 22px; height: 22px; border: none; background: rgba(255,255,255,0.7); border-radius: 4px; cursor: pointer; color: #65676b; font-size: 11px; opacity: 0; transition: opacity 0.12s; padding: 0; display: flex; align-items: center; justify-content: center; }
.tester-item:hover .open-btn { opacity: 1; }
.tester-item .open-btn:hover { background: #fff; color: #1877f2; }
.tester-item:hover { background: #f0f2f5; }
.tester-item.selected { background: #e7f3ff; outline: 2px solid #1877f2; }
.tester-item .avatar { width: 36px; height: 36px; border-radius: 50%; background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; flex-shrink: 0; object-fit: cover; }
.tester-item .meta { color: #65676b; font-size: 11px; }
.tester-empty { padding: 20px; text-align: center; color: #65676b; font-size: 13px; }
.run-bar { display: flex; align-items: center; gap: 10px; justify-content: space-between; background: #fff; border-radius: 12px; padding: 12px 14px; margin: 0 0 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); }
.run-bar .summary { font-size: 13px; color: #65676b; }
.run-bar .summary b { color: #050505; }
.models-pop { position: relative; }
.models-pop summary { list-style: none; cursor: pointer; padding: 8px 14px; border: 1px solid #dadde1; border-radius: 6px; font-size: 13px; font-weight: 600; background: #f0f2f5; }
.models-pop summary::-webkit-details-marker { display: none; }
.models-pop[open] > .pop-content { display: block; }
.pop-content { position: absolute; top: 100%; right: 0; margin-top: 4px; background: #fff; border: 1px solid #dadde1; border-radius: 8px; padding: 10px 12px; box-shadow: 0 6px 16px rgba(0,0,0,0.12); z-index: 50; min-width: 280px; max-height: 340px; overflow-y: auto; }
.pop-content label { display: flex; gap: 8px; align-items: center; padding: 5px 4px; font-size: 13px; }
.pop-content .nokey { color: #b91c1c; font-size: 11px; margin-left: 6px; }
.reply-card { padding: 12px 14px; border-bottom: 1px solid #f0f2f5; }
.reply-card:last-child { border-bottom: none; }
.reply-card .title-row { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; }
.reply-card .model { font-weight: 700; font-size: 13px; color: #050505; }
.reply-card .provider { font-size: 11px; color: #65676b; margin-left: 6px; }
.reply-card .price { font-weight: 400; font-size: 12px; color: #65676b; margin-left: 6px; }
.reply-card .cost { font-weight: 400; font-size: 12px; color: #1877f2; white-space: nowrap; }
.reply-card .text { margin-top: 6px; padding: 10px 14px; background: #f0f2f5; border-radius: 14px; line-height: 1.45; font-size: 14px; }
.reply-card .err { margin-top: 6px; padding: 8px 12px; background: #fee2e2; color: #b91c1c; border-radius: 8px; font-size: 12px; }
.reply-card .spinner-line { color: #65676b; font-size: 12px; margin-top: 6px; }
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: none; align-items: center; justify-content: center; z-index: 1000; padding: 20px; }
.modal-backdrop.open { display: flex; }
.modal-box { background: #fff; border-radius: 12px; max-width: 720px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 22px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.25); }
.modal-close { position: absolute; top: 10px; right: 10px; background: transparent; border: none; font-size: 22px; cursor: pointer; color: #65676b; padding: 2px 8px; border-radius: 4px; }
.modal-close:hover { background: #f0f2f5; color: #050505; }
.modal-box h2 { margin: 0 0 6px; }
.modal-box h3 { margin: 16px 0 6px; font-size: 14px; color: #65676b; text-transform: uppercase; letter-spacing: 0.5px; }
.modal-box .meta { color: #65676b; font-size: 13px; }
.modal-box .chip { display: inline-block; font-size: 12px; padding: 3px 10px; border-radius: 12px; background: #e7f3ff; color: #1877f2; font-weight: 600; margin: 0 4px 4px 0; }
.modal-box .attr-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f0f2f5; font-size: 13px; }
</style>

<form method="POST" action="{{ $runUrl }}" id="testForm">
  @csrf
  <input type="hidden" name="persona_id" id="personaId" value="{{ $selectedPersonaId }}">
  <input type="hidden" name="post_id" id="postId" value="{{ $selectedPostId }}">

  <div class="run-bar">
    <div class="summary">
      <span id="selSummary">Vælg et opslag og en persona...</span>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
      <details class="models-pop">
        <summary><i class="fa-solid fa-cube"></i> Modeller (<span id="modelCount">{{ count($selectedModels) }}</span>)</summary>
        <div class="pop-content">
          @foreach ($allModels as $id => $m)
            @php $hasKey = in_array($id, $availableModels, true); @endphp
            <label>
              <input type="checkbox" name="models[]" value="{{ $id }}"
                {{ in_array($id, $selectedModels, true) && $hasKey ? 'checked' : '' }}
                {{ $hasKey ? '' : 'disabled' }}
                class="model-cb">
              <span>{{ $m['label'] }}</span>
              <span style="font-weight: 400; color: #65676b; font-size: 11px;">${{ number_format($m['price_in'], 2) }} in / ${{ number_format($m['price_out'], 2) }} out per 1M</span>
              @if (!$hasKey) <span class="nokey">(API nøgle mangler)</span> @endif
            </label>
          @endforeach
        </div>
      </details>
      <button type="submit" class="btn btn-primary" id="runBtn" disabled><i class="fa-solid fa-play"></i> Kør</button>
    </div>
  </div>

  <div class="tester-grid">
    {{-- Column 1: Personas --}}
    <div class="tester-col">
      <h3>1. Vælg persona</h3>
      <div class="col-search">
        <input type="search" id="personaSearch" placeholder="Søg i personas...">
      </div>
      <div class="col-body" id="personaList">
        @foreach ($personas as $p)
          @php
            $demo = $p['demographics'] ?? [];
            $dimFacets = collect($p['dimensions'] ?? [])->pluck('facet')->filter()->implode(' ');
            $needle = strtolower(($p['name'] ?? '').' '.($demo['occupation_hint'] ?? '').' '.($demo['region'] ?? '').' '.$dimFacets);
            $initials = strtoupper(mb_substr($p['name'] ?? '?', 0, 2));
            $metaBits = array_filter([
              $demo['age'] ?? null,
              $demo['occupation_hint'] ?? null,
              $demo['region'] ?? null,
            ]);
            $chips = collect($p['dimensions'] ?? [])->filter(fn ($d) => !empty($d['show_on_profile']))->take(3);
          @endphp
          <div class="tester-item persona-item {{ $selectedPersonaId === $p['id'] ? 'selected' : '' }}" data-id="{{ $p['id'] }}" data-text="{{ $needle }}">
            @if (!empty($p['image_file']))
              <img src="{{ url('/simulation/admin/populations/'.$population->id.'/personas/'.$p['id'].'/thumb') }}" class="avatar">
            @else
              <div class="avatar">{{ $initials }}</div>
            @endif
            <div style="flex: 1; min-width: 0;">
              <div><strong>{{ $p['name'] }}</strong></div>
              <div class="meta">{{ implode(' · ', $metaBits) }}</div>
              @if ($chips->isNotEmpty())
                <div class="meta">{{ $chips->pluck('facet')->implode(', ') }}</div>
              @endif
            </div>
            <button type="button" class="open-btn" data-modal-type="persona" data-modal-id="{{ $p['id'] }}" title="Vis detaljer"><i class="fa-solid fa-up-right-from-square"></i></button>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Column 2: Posts --}}
    <div class="tester-col">
      <h3>2. Vælg opslag</h3>
      <div class="col-search">
        <input type="search" id="postSearch" placeholder="Søg i opslag..." value="{{ $q }}">
      </div>
      <div class="col-body" id="postList">
        @foreach ($posts as $post)
          <div class="tester-item post-item {{ (int) $selectedPostId === $post->id ? 'selected' : '' }}" data-id="{{ $post->id }}" data-text="{{ Str::lower($post->body) }}">
            <div style="flex: 1; min-width: 0;">
              <div style="overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ Str::limit($post->body, 160) }}</div>
              <div class="meta">#{{ $post->id }} · {{ $post->created_at->format('Y-m-d H:i') }}</div>
            </div>
            <button type="button" class="open-btn" data-modal-type="post" data-modal-id="{{ $post->id }}" title="Vis detaljer"><i class="fa-solid fa-up-right-from-square"></i></button>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Column 3: Replies --}}
    <div class="tester-col">
      <h3>3. Modelsvar <span id="progLabel" style="font-weight: 400; color: #65676b; font-size: 12px; margin-left: 8px;">{{ $done }} / {{ $queued }}</span></h3>
      <div class="col-body" id="repliesBody">
        @if (!$batchId && empty($results))
          <div class="tester-empty">Vælg opslag, persona og modeller — så tryk <b>Kør</b>.</div>
        @else
          <div id="repliesList">
            @php $resultsByModel = collect($results)->keyBy('model'); @endphp
            @foreach ($selectedModels as $modelId)
              @php $m = $allModels[$modelId] ?? ['label' => $modelId, 'provider' => '?']; $r = $resultsByModel->get($modelId); @endphp
              <div class="reply-card" data-model="{{ $modelId }}">
                <div class="title-row">
                  <span>
                    <span class="model">{{ $m['label'] }}</span>
                    <span class="provider">{{ $m['provider'] }}</span>
                    @if (isset($m['price_in']))
                      <span class="price">${{ number_format($m['price_in'], 2) }} in / ${{ number_format($m['price_out'], 2) }} out per 1M</span>
                    @endif
                  </span>
                  <span class="cost">
                    @if ($r && isset($r['cost_usd']))
                      {{ number_format($r['cost_usd'] * 7.0, 4, ',', '.') }} kr.
                    @endif
                  </span>
                </div>
                @if ($r && !empty($r['error']))
                  <div class="err">Fejl: {{ $r['error'] }}</div>
                @elseif ($r)
                  <div class="text">{{ $r['text'] }}</div>
                @else
                  <div class="spinner-line"><span class="spinner" style="border-color: #1877f2; border-top-color: transparent;"></span> Genererer svar…</div>
                @endif
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
</form>

<div id="detailModal" class="modal-backdrop">
  <div class="modal-box">
    <button type="button" class="modal-close" onclick="closeModal()" title="Luk (Esc)">×</button>
    <div id="modalContent"></div>
  </div>
</div>

@php
  $postsJs = $posts->keyBy('id')->map(fn ($p) => [
      'id'          => $p->id,
      'body'        => $p->body,
      'image_path'  => $p->image_path,
      'author_name' => $p->author_name,
      'created_at'  => $p->created_at?->format('Y-m-d H:i'),
  ]);
@endphp
<script>
const personas = @json(collect($personas)->keyBy('id'));
const posts    = @json($postsJs);
const allModels = @json($allModels);
const PERSONA_THUMB_BASE = '{{ url('/simulation/admin/populations/'.$population->id.'/personas') }}';
const STORAGE_BASE = '{{ url('/storage') }}';

const personaId = document.getElementById('personaId');
const postId    = document.getElementById('postId');
const runBtn    = document.getElementById('runBtn');
const selSum    = document.getElementById('selSummary');
const modelCount = document.getElementById('modelCount');

function updateRunState() {
  const ok = personaId.value && postId.value && document.querySelectorAll('.model-cb:checked').length > 0;
  runBtn.disabled = !ok;
  const personaName = personas[personaId.value]?.name || '—';
  const postPreview = posts[postId.value]?.body?.substring(0, 60) || '—';
  selSum.innerHTML = `<b>${escapeHtml(personaName)}</b> reagerer på <b>"${escapeHtml(postPreview)}${postPreview.length >= 60 ? '...' : ''}"</b>`;
  modelCount.textContent = document.querySelectorAll('.model-cb:checked').length;
}

function bindList(container, hiddenInput, itemClass) {
  container.querySelectorAll(itemClass).forEach(el => {
    el.addEventListener('click', () => {
      container.querySelectorAll(itemClass).forEach(o => o.classList.remove('selected'));
      el.classList.add('selected');
      hiddenInput.value = el.dataset.id;
      updateRunState();
    });
  });
}
bindList(document.getElementById('postList'), postId, '.post-item');
bindList(document.getElementById('personaList'), personaId, '.persona-item');

document.querySelectorAll('.model-cb').forEach(cb => cb.addEventListener('change', updateRunState));

function filterList(searchInput, container, itemClass) {
  searchInput.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase().trim();
    container.querySelectorAll(itemClass).forEach(el => {
      el.style.display = !q || el.dataset.text.includes(q) ? '' : 'none';
    });
  });
}
filterList(document.getElementById('postSearch'), document.getElementById('postList'), '.post-item');
filterList(document.getElementById('personaSearch'), document.getElementById('personaList'), '.persona-item');

function escapeHtml(s) { return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function escapeAttr(s) { return escapeHtml(s); }

// --- Detail modal ---
const detailModal = document.getElementById('detailModal');
const modalContent = document.getElementById('modalContent');
function openInModal(type, id) {
  let html = '';
  if (type === 'post') {
    const p = posts[id]; if (!p) return;
    const img = p.image_path ? `<img src="${STORAGE_BASE}/${escapeAttr(p.image_path)}" style="max-width:100%;border-radius:8px;margin:10px 0;">` : '';
    html = `<h2>Opslag #${p.id}</h2>
      <div class="meta">${escapeHtml(p.author_name || 'Anonym')} · ${escapeHtml(p.created_at || '')}</div>
      ${img}
      <div style="white-space:pre-wrap;line-height:1.5;margin-top:10px;">${escapeHtml(p.body)}</div>`;
  } else if (type === 'persona') {
    const pr = personas[id]; if (!pr) return;
    const img = pr.image_file
      ? `<img src="${PERSONA_THUMB_BASE}/${pr.id}/image" style="width:120px;height:120px;border-radius:50%;object-fit:cover;flex-shrink:0;">`
      : `<div style="width:120px;height:120px;border-radius:50%;background:#e4e6eb;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:32px;flex-shrink:0;">${escapeHtml((pr.name||'?').substring(0,2).toUpperCase())}</div>`;
    const demo = pr.demographics || {};
    const metaBits = [demo.age, demo.occupation_hint, demo.region, demo.family].filter(Boolean).map(escapeHtml).join(' · ');
    const dims = pr.dimensions || [];
    const chips = dims.filter(d => d.show_on_profile).map(d => `<span class="chip" title="${escapeAttr(d.dimension || '')}">${escapeHtml(d.facet || '')}</span>`).join('');
    const demoDims  = dims.filter(d => (d.type || 'personality') === 'demographic');
    const persDims  = dims.filter(d => (d.type || 'personality') === 'personality');
    const demoRows = demoDims.map(d => `<div class="attr-row"><span>${escapeHtml(d.dimension || '')}</span><strong>${escapeHtml(d.value ?? d.facet ?? '')}</strong></div>`).join('');
    const persRows = persDims.map(d => `<div class="attr-row"><span>${escapeHtml(d.dimension || '')}</span><strong>${escapeHtml(d.facet || '')}</strong></div>`).join('');
    html = `<div style="display:flex;gap:14px;align-items:flex-start;">${img}
      <div style="flex:1;min-width:0;">
        <h2>${escapeHtml(pr.name || '')}</h2>
        <div class="meta">${metaBits}</div>
        ${pr.bio ? `<div class="meta" style="margin-top:6px;font-style:italic;">"${escapeHtml(pr.bio)}"</div>` : ''}
        ${chips ? `<div style="margin-top:8px;">${chips}</div>` : ''}
      </div></div>
      ${pr.narrative ? `<h3>Narrativ</h3><p style="white-space:pre-wrap;line-height:1.5;margin:0;">${escapeHtml(pr.narrative)}</p>` : ''}
      ${demoRows ? `<h3>Demografi</h3>${demoRows}` : ''}
      ${persRows ? `<h3>Personlighed</h3>${persRows}` : ''}`;
  }
  modalContent.innerHTML = html;
  detailModal.classList.add('open');
}
function closeModal() { detailModal.classList.remove('open'); }
detailModal.addEventListener('click', e => { if (e.target === detailModal) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
document.body.addEventListener('click', e => {
  const btn = e.target.closest('.open-btn');
  if (!btn) return;
  e.stopPropagation(); e.preventDefault();
  openInModal(btn.dataset.modalType, btn.dataset.modalId);
});

document.getElementById('testForm').addEventListener('submit', () => { runBtn.disabled = true; });

updateRunState();

// --- Results polling ---
@if ($batchId)
const STATUS_URL = '{{ $statusUrl }}';
const renderedModels = new Set();

function fillReplyCard(card, r) {
  const body = r.error
    ? `<div class="err">Fejl: ${escapeHtml(r.error)}</div>`
    : `<div class="text">${escapeHtml(r.text || '')}</div>`;
  const oldFiller = card.querySelector('.spinner-line, .text, .err');
  if (oldFiller) oldFiller.remove();
  card.insertAdjacentHTML('beforeend', body);

  if (typeof r.cost_usd === 'number') {
    const dkk = r.cost_usd * 7.0;
    const costEl = card.querySelector('.cost');
    if (costEl) costEl.textContent = dkk.toFixed(4).replace('.', ',') + ' kr.';
  }
}

async function pollTester() {
  try {
    const res = await fetch(STATUS_URL);
    const data = await res.json();
    document.getElementById('progLabel').textContent = `${data.done} / ${data.queued}`;
    data.results.forEach(r => {
      if (renderedModels.has(r.model)) return;
      const card = document.querySelector(`.reply-card[data-model="${r.model}"]`);
      if (!card) return;
      renderedModels.add(r.model);
      fillReplyCard(card, r);
    });
    if (data.done >= data.queued && data.queued > 0) {
      clearInterval(testerTimer);
    }
  } catch (e) {}
}
const testerTimer = setInterval(pollTester, 2000);
pollTester();
@endif
</script>

@endif
@endsection
