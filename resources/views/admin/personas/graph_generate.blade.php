@extends('layouts.app')
@section('content')

@php
  $base = '/simulation/admin/populations/'.$population->id;
  $gs = $graphSettings ?? [];
@endphp
<div class="view-header">
  <h1>
    <a href="{{ url('/simulation/admin/populations') }}" style="color:#1877f2;"><i class="fa-solid fa-arrow-left"></i></a>
    <span style="font-weight:400;color:#65676b;">Population:</span> {{ $population->name }}
  </h1>
</div>

@include('admin.populations._tabs', ['population' => $population])

<div class="card">
  <div style="display: flex; gap: 14px; margin-bottom: 14px;">
    <div style="flex: 1; background: #f8f9fa; padding: 14px; border-radius: 8px;">
      <div style="font-size: 26px; font-weight: 700; color: #1877f2;">{{ $personaCount }}</div>
      <div style="font-size: 12px; color: #65676b; text-transform: uppercase;">Personas</div>
    </div>
    <div style="flex: 1; background: #f8f9fa; padding: 14px; border-radius: 8px;">
      <div style="font-size: 26px; font-weight: 700; color: #22c55e;">{{ $edgeCount }}</div>
      <div style="font-size: 12px; color: #65676b; text-transform: uppercase;">Venskaber nu</div>
    </div>
    <div style="flex: 1; background: #f8f9fa; padding: 14px; border-radius: 8px;">
      <div style="font-size: 26px; font-weight: 700; color: #7c3aed;">{{ $personaCount > 0 ? round($edgeCount * 2 / $personaCount, 1) : 0 }}</div>
      <div style="font-size: 12px; color: #65676b; text-transform: uppercase;">Venner per persona (snit)</div>
    </div>
  </div>
</div>

<style>
.dim-row { display: grid; grid-template-columns: 280px 1fr 60px 32px; gap: 14px; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f2f5; }
.dim-row:last-child { border-bottom: none; }
.dim-row .dim-meta { min-width: 0; }
.dim-row .dim-name { font-size: 13px; font-weight: 600; color: #1c1e21; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dim-badge { display: inline-block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; padding: 1px 6px; border-radius: 4px; margin-right: 4px; }
.dim-badge.personality { background: #e7f3ff; color: #1877f2; }
.dim-badge.demographic { background: #fef3c7; color: #92400e; }
.dim-wval { font-family: monospace; color: #1877f2; font-weight: 600; text-align: right; }
.dim-remove { background: none; border: 1px solid #dadde1; border-radius: 6px; color: #65676b; cursor: pointer; padding: 4px 7px; }
.dim-remove:hover { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.dim-empty { font-size: 13px; color: #65676b; font-style: italic; padding: 14px 0; }
.dim-sum { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding: 10px 12px; background: #f7f8fa; border-radius: 8px; font-size: 13px; color: #65676b; }
.dim-sum strong { color: #1c1e21; font-family: monospace; font-size: 14px; }
.dim-sum .note { font-weight: 400; color: #65676b; }
.dim-sum.ok { background: #dcfce7; }
.dim-sum.ok strong, .dim-sum.ok .note { color: #166534; }
.dim-sum.over { background: #fef2f2; }
.dim-sum.over strong, .dim-sum.over .note { color: #b91c1c; }
.dp-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
.dp-modal.open { display: flex; }
.dp-modal .panel { background: #fff; border-radius: 12px; padding: 22px; width: 100%; max-width: 520px; box-shadow: 0 8px 40px rgba(0,0,0,0.2); max-height: 80vh; display: flex; flex-direction: column; }
.dp-modal h2 { font-size: 17px; margin: 0 0 14px; }
.dp-modal .search { padding: 9px 12px; border: 1px solid #dadde1; border-radius: 6px; font-size: 14px; font-family: inherit; margin-bottom: 10px; }
.dp-modal .search:focus { outline: none; border-color: #1877f2; box-shadow: 0 0 0 2px #e7f3ff; }
.dp-modal .list { overflow-y: auto; flex: 1; min-height: 0; max-height: 55vh; }
.dp-modal .cat-h { font-size: 11px; font-weight: 700; color: #65676b; text-transform: uppercase; letter-spacing: 0.4px; padding: 8px 4px 6px; }
.dp-modal .item { padding: 10px 12px; border: 1px solid #dadde1; border-radius: 6px; margin-bottom: 6px; cursor: pointer; background: #fff; }
.dp-modal .item:hover { background: #f0f7ff; border-color: #1877f2; }
.dp-modal .item.added, .dp-modal .item.added:hover { background: #f0fdf4; border-color: #bbf7d0; cursor: default; }
.dp-modal .item .n { font-weight: 600; font-size: 13px; color: #1c1e21; }
.dp-modal .item .added-tag { font-size: 11px; color: #166534; font-weight: 600; margin-left: 4px; }
.dp-modal .item .c { font-size: 11px; color: #65676b; margin-top: 2px; }
.dp-modal .empty { text-align: center; padding: 30px; color: #65676b; font-size: 13px; }
.dp-modal .actions { display: flex; justify-content: flex-end; margin-top: 14px; }
</style>

<form method="POST" action="{{ url("$base/personas/graph") }}">
  @csrf

  <div class="card">
    <h3 style="margin-bottom: 8px;">Dimensioner i netværks-matchningen</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 12px;">
      Vælg hvilke dimensioner fra personligheden der afgør hvem der bliver venner. Sliderne er
      <strong>relative vægte</strong> — kun forholdet betyder noget, men tælleren nedenfor hjælper dig
      med at holde summen på 1.0. Demografi-dimensioner med tal-værdier (fx alder) matches på afstand;
      resten matches på om samme facet er valgt.
    </p>
    <div id="dim-rows"></div>
    <div id="dim-sum" class="dim-sum"></div>
    <button type="button" class="btn btn-secondary" style="margin-top: 12px;" onclick="openDimPicker()">
      <i class="fa-solid fa-plus"></i> Tilføj dimension
    </button>
  </div>

  <div class="card">
    <h3 style="margin-bottom: 8px;">Broer og støj</h3>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Bro-personas (%)</strong><br><small style="color:#65676b;">% af personerne der er "broer" mellem klynger</small></label>
      <input type="range" name="bridge_percentage" min="0" max="10" step="0.5" value="{{ $gs['bridge_percentage'] ?? 3 }}" oninput="this.nextElementSibling.textContent = this.value + '%'">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">{{ ($gs['bridge_percentage'] ?? 3) }}%</span>
    </div>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Bro-bonus</strong><br><small style="color:#65676b;">ekstra similaritet hvis en af parterne er bro</small></label>
      <input type="range" name="bridge_bonus" min="0" max="0.3" step="0.01" value="{{ $gs['bridge_bonus'] ?? 0.10 }}" oninput="this.nextElementSibling.textContent = (+this.value).toFixed(2)">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">{{ number_format($gs['bridge_bonus'] ?? 0.10, 2) }}</span>
    </div>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Støj (%)</strong><br><small style="color:#65676b;">andel tilfældige venskaber (fjerne bekendtskaber)</small></label>
      <input type="range" name="noise_percentage" min="0" max="20" step="1" value="{{ $gs['noise_percentage'] ?? 7 }}" oninput="this.nextElementSibling.textContent = this.value + '%'">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">{{ ($gs['noise_percentage'] ?? 7) }}%</span>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-bottom: 8px;">Antal venner per persona</h3>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Basis-antal</strong><br><small style="color:#65676b;">gennemsnittet (±15 tilfældig variation per persona)</small></label>
      <input type="range" name="base_friend_count" min="30" max="200" step="5" value="{{ $gs['base_friend_count'] ?? 80 }}" oninput="this.nextElementSibling.textContent = this.value">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">{{ $gs['base_friend_count'] ?? 80 }}</span>
    </div>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Min</strong></label>
      <input type="range" name="min_friends" min="5" max="50" step="1" value="{{ $gs['min_friends'] ?? 15 }}" oninput="this.nextElementSibling.textContent = this.value">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">{{ $gs['min_friends'] ?? 15 }}</span>
    </div>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Max</strong></label>
      <input type="range" name="max_friends" min="50" max="500" step="10" value="{{ $gs['max_friends'] ?? 200 }}" oninput="this.nextElementSibling.textContent = this.value">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">{{ $gs['max_friends'] ?? 200 }}</span>
    </div>
  </div>

  <div style="text-align: right; margin: 20px 0;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-play"></i> Byg graf i baggrunden</button>
  </div>
</form>

{{-- Dimension picker modal --}}
<div class="dp-modal" id="dim-picker">
  <div class="panel">
    <h2>Tilføj dimension</h2>
    <input type="text" class="search" id="dim-picker-search" placeholder="Søg dimensioner..."
           oninput="renderDimPickerList()" autocomplete="off">
    <div class="list" id="dim-picker-list"></div>
    <div class="actions">
      <button type="button" class="btn btn-secondary" onclick="closeDimPicker()">Luk</button>
    </div>
  </div>
</div>

@if ($progress)
<div class="card" id="progressCard" style="background: #e7f3ff; border: 1px solid #b6d8fb;">
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <strong id="progPhase">{{ $progress['phase'] ?? '—' }}</strong>
      <span id="progLabel" style="color: #65676b; margin-left: 8px;">{{ $progress['done'] ?? 0 }} / {{ $progress['total'] ?? 0 }}</span>
    </div>
    <span class="spinner" id="progSpinner" style="border-color: #1877f2; border-top-color: transparent;"></span>
  </div>
  <div style="height: 6px; background: #fff; border-radius: 3px; overflow: hidden; margin-top: 10px;">
    <div id="progBar" style="height: 100%; background: #1877f2; width: 0%; transition: width 0.3s;"></div>
  </div>
  @if ($stats)
    <div style="margin-top: 14px; font-size: 13px; color: #1c1e21;">
      <strong>Resultat:</strong> {{ $stats['edges'] }} venskaber · gennemsnit {{ $stats['avg_friends'] }} venner per persona · {{ $stats['bridges'] }} bro-personas · {{ $stats['noise_edges'] }} støj-venskaber
    </div>
  @endif
</div>
@endif

<script>
// ---- Dimension picker -------------------------------------------------------
const ALL_DIMS = @json($dimensions);
const SAVED_WEIGHTS = @json((object) ($gs['dimension_weights'] ?? []));
const DIM_TYPE_LABEL = { personality: 'Personlighed', demographic: 'Demografi' };

// picked: id -> weight (0..1). Restore the saved selection; otherwise start empty —
// no dimensions are added by default, you pick them via "Tilføj dimension".
const DEFAULT_WEIGHT = 0.10;
let picked = {};
for (const id of Object.keys(SAVED_WEIGHTS)) {
  if (ALL_DIMS.some(d => d.id === id)) picked[id] = Number(SAVED_WEIGHTS[id]) || 0;
}

function escapeHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function updateSum() {
  const el = document.getElementById('dim-sum');
  if (!el) return;
  const sum = Object.values(picked).reduce((a, b) => a + (Number(b) || 0), 0);
  const atOne = Math.abs(sum - 1) < 0.001;
  el.className = 'dim-sum' + (atOne ? ' ok' : (sum > 1 ? ' over' : ''));
  let note;
  if (atOne)        note = 'præcis 1.0';
  else if (sum < 1) note = `mangler ${(1 - sum).toFixed(2)}`;
  else              note = `${(sum - 1).toFixed(2)} over 1.0`;
  el.innerHTML = `<span>Samlet vægt</span><strong>${sum.toFixed(2)} <span class="note">· ${note}</span></strong>`;
}

function renderDimRows() {
  const wrap = document.getElementById('dim-rows');
  const ids = Object.keys(picked);
  if (!ids.length) {
    wrap.innerHTML = '<div class="dim-empty">Ingen dimensioner valgt endnu. Klik "Tilføj dimension".</div>';
    updateSum();
    return;
  }
  wrap.innerHTML = ids.map(id => {
    const dim = ALL_DIMS.find(d => d.id === id);
    if (!dim) return '';
    const w = Number(picked[id]) || 0;
    const type = dim.type || 'personality';
    return `
      <div class="dim-row" data-id="${escapeHtml(id)}">
        <div class="dim-meta">
          <div class="dim-name" title="${escapeHtml(dim.name)}">${escapeHtml(dim.name)}</div>
          <span class="dim-badge ${type}">${DIM_TYPE_LABEL[type] || type}</span>
          <span style="font-size:11px;color:#65676b;">${dim.facets} facetter</span>
        </div>
        <input type="range" name="dimension_weights[${escapeHtml(id)}]" min="0" max="1" step="0.05" value="${w}"
               oninput="onWeightInput('${escapeHtml(id)}', this.value)">
        <span class="dim-wval">${w.toFixed(2)}</span>
        <button type="button" class="dim-remove" title="Fjern" onclick="removeDim('${escapeHtml(id)}')">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>`;
  }).join('');
  updateSum();
}

function onWeightInput(id, value) {
  picked[id] = Number(value) || 0;
  const row = document.querySelector(`.dim-row[data-id="${CSS.escape(id)}"] .dim-wval`);
  if (row) row.textContent = picked[id].toFixed(2);
  updateSum();
}

function removeDim(id) {
  delete picked[id];
  renderDimRows();
}

function renderDimPickerList() {
  const list = document.getElementById('dim-picker-list');
  if (!ALL_DIMS.length) {
    list.innerHTML = '<div class="empty">Personligheden har ingen dimensioner endnu.</div>';
    return;
  }
  const q = (document.getElementById('dim-picker-search').value || '').toLowerCase().trim();
  const matched = ALL_DIMS.filter(d => !q || (d.name || '').toLowerCase().includes(q));
  if (!matched.length) {
    list.innerHTML = '<div class="empty">Ingen dimensioner matcher søgningen.</div>';
    return;
  }
  let html = '';
  // Show every matching dimension; already-added ones are marked, not hidden.
  for (const type of ['personality', 'demographic']) {
    const items = matched.filter(d => (d.type || 'personality') === type);
    if (!items.length) continue;
    html += `<div class="cat-h">${DIM_TYPE_LABEL[type] || type}</div>`;
    html += items.map(d => {
      const added = d.id in picked;
      return `
        <div class="item ${added ? 'added' : ''}" ${added ? '' : `onclick="addDim('${escapeHtml(d.id)}')"`}>
          <div class="n">${escapeHtml(d.name)}${added ? '<span class="added-tag">✓ tilføjet</span>' : ''}</div>
          <div class="c">${d.facets} facetter</div>
        </div>`;
    }).join('');
  }
  list.innerHTML = html;
}

function openDimPicker() {
  document.getElementById('dim-picker-search').value = '';
  renderDimPickerList();
  document.getElementById('dim-picker').classList.add('open');
  setTimeout(() => document.getElementById('dim-picker-search').focus(), 50);
}

function closeDimPicker() {
  document.getElementById('dim-picker').classList.remove('open');
}

function addDim(id) {
  if (!(id in picked)) picked[id] = DEFAULT_WEIGHT;
  renderDimRows();
  renderDimPickerList(); // refresh "✓ tilføjet" marks, keep modal + search query
}

document.getElementById('dim-picker').addEventListener('click', e => {
  if (e.target.id === 'dim-picker') closeDimPicker();
});

renderDimRows();

// ---- Build progress polling -------------------------------------------------
let sawActiveBuild = false; // only reload if we actually watched a build progress
async function pollGraph() {
  try {
    const res = await fetch('{{ url("$base/personas/graph/status") }}');
    const data = await res.json();
    const p = data.progress;
    if (!p || p.phase === 'idle') return;
    const card = document.getElementById('progressCard');
    if (!card) return;
    if (p.phase !== 'done') {
      sawActiveBuild = true;
      document.getElementById('progPhase').textContent = p.phase;
      document.getElementById('progLabel').textContent = `${p.done || 0} / ${p.total || 0}`;
      const pct = p.total > 0 ? (p.done / p.total) * 100 : 0;
      document.getElementById('progBar').style.width = pct + '%';
    } else if (sawActiveBuild) {
      document.getElementById('progSpinner').style.display = 'none';
      clearInterval(graphTimer);
      setTimeout(() => location.reload(), 800);
    } else {
      clearInterval(graphTimer);
    }
  } catch {}
}
const graphTimer = setInterval(pollGraph, 1500);
pollGraph();
</script>
@endsection
