@extends('layouts.app')
@section('content')

<div class="bp-header">
  <a href="{{ url('/simulation/admin/blueprints') }}" class="bp-back" title="Tilbage til personligheder"><i class="fa-solid fa-arrow-left"></i></a>
  <div class="bp-header-meta">
    <input type="text" id="bp-name" class="bp-name-inline" value="{{ $blueprint->name }}" placeholder="Personlighedens navn">
    <input type="text" id="bp-description" class="bp-desc-inline" value="{{ $blueprint->description }}" placeholder="Kort beskrivelse (valgfri)">
  </div>
  <div class="bp-header-actions">
    <button type="button" class="btn btn-secondary" style="font-size:12px; color:#b91c1c;"
      onclick="if(confirm('Slet denne personlighed?')) document.getElementById('delete-form').submit()">
      <i class="fa-solid fa-trash"></i> Slet
    </button>
    <button type="button" class="btn btn-primary" id="save-btn" onclick="saveBlueprint()">
      <i class="fa-solid fa-floppy-disk"></i> Gem
    </button>
  </div>
</div>

<div class="bp-tabs">
  <a href="{{ url('/simulation/admin/blueprints/'.$blueprint->id) }}" class="active">Dimensioner</a>
  <a href="{{ url('/simulation/admin/blueprints/'.$blueprint->id.'/prompts') }}">Prompts</a>
</div>

<style>
.bp-header { display:flex; align-items:flex-start; gap:14px; margin-bottom:10px; }
.bp-back { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:8px; color:#1877f2; text-decoration:none; flex-shrink:0; margin-top:2px; }
.bp-back:hover { background:#e7f3ff; }
.bp-header-meta { flex:1; min-width:0; }
.bp-name-inline { display:block; width:100%; font-size:24px; font-weight:800; letter-spacing:-0.3px; color:#1c1e21; padding:4px 8px; border:1px solid transparent; border-radius:6px; background:transparent; font-family:inherit; }
.bp-name-inline:hover { border-color:#dadde1; background:#fff; }
.bp-name-inline:focus { outline:none; border-color:#1877f2; background:#fff; box-shadow:0 0 0 2px #e7f3ff; }
.bp-desc-inline { display:block; width:100%; margin-top:2px; font-size:13px; color:#65676b; padding:4px 8px; border:1px solid transparent; border-radius:6px; background:transparent; font-family:inherit; }
.bp-desc-inline:hover { border-color:#dadde1; background:#fff; }
.bp-desc-inline:focus { outline:none; border-color:#1877f2; background:#fff; box-shadow:0 0 0 2px #e7f3ff; }
.bp-header-actions { display:flex; gap:8px; flex-shrink:0; }
.bp-tabs { display:inline-flex; gap:2px; background:#f0f2f5; border-radius:8px; padding:3px; margin-bottom:14px; }
.bp-tabs a { padding:6px 14px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none; color:#65676b; }
.bp-tabs a.active { color:#1c1e21; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,0.06); }

.bp-layout { display: grid; grid-template-columns: 240px 1fr; gap: 16px; align-items: start; }
.bp-side { position: sticky; top: 14px; background: #fff; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); padding: 8px; max-height: calc(100vh - 40px); overflow-y: auto; }
.bp-side h4 { font-size: 11px; font-weight: 700; color: #65676b; text-transform: uppercase; letter-spacing: 0.4px; padding: 10px 10px 4px; }
.bp-dim { display: flex; align-items: center; gap: 6px; padding: 7px 10px; border-radius: 6px; font-size: 13px; color: #1c1e21; cursor: pointer; }
.bp-dim:hover { background: #f0f2f5; }
.bp-dim.active { background: #e7f3ff; color: #1877f2; font-weight: 600; }
.bp-dim .label { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.bp-dim .meta { font-size: 11px; color: #65676b; }
.bp-dim.active .meta { color: #1877f2; }
.bp-dim .lib-icon { font-size: 10px; color: #65676b; }
.bp-side-actions { display: flex; flex-direction: column; gap: 4px; padding: 8px 4px 4px; margin-top: 6px; border-top: 1px solid #f0f2f5; }
.bp-side-actions button { background: none; border: none; text-align: left; padding: 7px 10px; font-size: 13px; color: #1c1e21; cursor: pointer; border-radius: 6px; font-family: inherit; display: flex; align-items: center; gap: 8px; }
.bp-side-actions button:hover { background: #f0f2f5; }
.bp-side-actions button .ico { width: 14px; color: #65676b; }

/* Picker modal: category-grouped */
.bp-picker-cat { margin-bottom: 14px; }
.bp-picker-cat .h { font-size: 11px; font-weight: 700; color: #65676b; text-transform: uppercase; letter-spacing: 0.4px; padding: 0 4px 6px; }
.bp-picker-cat .item { padding: 9px 12px; border: 1px solid #dadde1; border-radius: 6px; margin-bottom: 4px; cursor: pointer; background: #fff; }
.bp-picker-cat .item:hover { background: #f0f7ff; border-color: #1877f2; }
.bp-picker-cat .item.in-bp { background: #f0fdf4; border-color: #bbf7d0; }
.bp-picker-cat .item.in-bp::after { content: '✓ allerede tilføjet'; float: right; font-size: 11px; color: #166534; font-weight: 600; }
.bp-picker-cat .n { font-weight: 600; font-size: 13px; color: #1c1e21; }
.bp-picker-cat .d { font-size: 12px; color: #65676b; margin-top: 2px; }
.bp-picker-cat .c { font-size: 11px; color: #65676b; margin-top: 3px; }

.bp-content { min-width: 0; }
.bp-card { background: #fff; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); margin-bottom: 12px; overflow: hidden; }
.bp-card-head { display: flex; gap: 12px; align-items: flex-start; padding: 14px 16px; background: #f7f8fa; border-bottom: 1px solid #e4e6eb; }
.bp-card-head .meta { flex: 1; min-width: 0; }
.bp-card-head input.dim-name { display: block; width: 100%; max-width: 480px; font-size: 16px; font-weight: 700; color: #1c1e21; padding: 6px 8px; border: 1px solid transparent; border-radius: 6px; background: transparent; font-family: inherit; }
.bp-card-head input.dim-name:hover { border-color: #dadde1; background: #fff; }
.bp-card-head input.dim-name:focus { outline: none; border-color: #1877f2; background: #fff; box-shadow: 0 0 0 2px #e7f3ff; }
.bp-card-head input.dim-desc { display: block; width: 100%; max-width: 480px; margin-top: 4px; font-size: 13px; color: #65676b; padding: 6px 8px; border: 1px solid transparent; border-radius: 6px; background: transparent; font-family: inherit; }
.bp-card-head input.dim-desc:hover { border-color: #dadde1; background: #fff; }
.bp-card-head input.dim-desc:focus { outline: none; border-color: #1877f2; background: #fff; box-shadow: 0 0 0 2px #e7f3ff; }
.bp-card-head .actions { display: flex; gap: 6px; flex-shrink: 0; }
.bp-card-head .actions button { background: none; border: 1px solid #dadde1; border-radius: 6px; padding: 5px 10px; font-size: 12px; cursor: pointer; color: #65676b; white-space: nowrap; }
.bp-card-head .actions button:hover { background: #fff; color: #1c1e21; }
.bp-card-head .actions .lib-badge { font-size: 11px; color: #65676b; background: #fff; border: 1px solid #dadde1; border-radius: 10px; padding: 3px 9px; align-self: center; }

.bp-facet { display: grid; grid-template-columns: 180px 90px 1fr auto; gap: 12px; padding: 12px 16px; border-top: 1px solid #f0f2f5; align-items: start; }
.bp-facet:first-child { border-top: none; }
.bp-facet-name { padding-top: 4px; }
.bp-facet-name input { width: 100%; font-weight: 600; font-size: 13px; color: #1c1e21; padding: 6px 8px; border: 1px solid transparent; border-radius: 6px; background: transparent; font-family: inherit; }
.bp-facet-name input:hover { border-color: #dadde1; background: #fafbfc; }
.bp-facet-name input:focus { outline: none; border-color: #1877f2; background: #fff; box-shadow: 0 0 0 2px #e7f3ff; }
.bp-facet-weight { display: flex; align-items: center; gap: 4px; padding-top: 4px; }
.bp-facet-weight input { width: 56px; padding: 6px 8px; border: 1px solid transparent; border-radius: 6px; font-size: 13px; font-family: inherit; background: #fafbfc; text-align: right; color: #1c1e21; }
.bp-facet-weight input:hover { border-color: #dadde1; background: #fff; }
.bp-facet-weight input:focus { outline: none; border-color: #1877f2; background: #fff; box-shadow: 0 0 0 2px #e7f3ff; }
.bp-facet-weight .u { font-size: 12px; color: #65676b; }
.bp-facet-text textarea { width: 100%; min-height: 80px; border: 1px solid transparent; border-radius: 6px; padding: 8px 10px; font-size: 13px; font-family: inherit; line-height: 1.5; color: #1c1e21; background: #fafbfc; resize: vertical; }
.bp-facet-text textarea:hover { border-color: #dadde1; background: #fff; }
.bp-facet-text textarea:focus { outline: none; border-color: #1877f2; background: #fff; box-shadow: 0 0 0 2px #e7f3ff; }
.bp-facet-actions { padding-top: 4px; }
.bp-facet-actions button { background: none; border: 1px solid #dadde1; border-radius: 6px; padding: 4px 8px; font-size: 11px; cursor: pointer; color: #65676b; }
.bp-facet-actions button:hover:not(:disabled) { background: #f0f2f5; color: #b91c1c; border-color: #fecaca; }
.bp-facet-actions button:disabled { opacity: 0.4; cursor: not-allowed; }

.bp-card-foot { display: flex; justify-content: space-between; align-items: center; padding: 8px 16px; background: #f7f8fa; border-top: 1px solid #e4e6eb; font-size: 12px; color: #65676b; }
.bp-card-foot button { background: none; border: none; color: #1877f2; font-size: 13px; font-weight: 600; cursor: pointer; padding: 4px 8px; border-radius: 6px; font-family: inherit; }
.bp-card-foot button:hover { background: #e7f3ff; }

.bp-empty { text-align: center; padding: 80px 20px; color: #65676b; background: #fff; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); }
.bp-empty i { font-size: 36px; opacity: 0.3; display: block; margin-bottom: 14px; }

.bp-meta-card { background: #fff; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); padding: 14px 16px; margin-bottom: 12px; }
.bp-meta-card label { display: block; font-weight: 600; font-size: 12px; color: #65676b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px; }
.bp-meta-card input { width: 100%; padding: 8px 10px; border: 1px solid transparent; border-radius: 6px; font-size: 14px; font-family: inherit; background: #fafbfc; }
.bp-meta-card input:hover { border-color: #dadde1; background: #fff; }
.bp-meta-card input:focus { outline: none; border-color: #1877f2; background: #fff; box-shadow: 0 0 0 2px #e7f3ff; }
.bp-meta-card .row { display: grid; grid-template-columns: 1fr 2fr; gap: 12px; align-items: center; }
.bp-meta-card .row + .row { margin-top: 8px; }

.bp-sum { font-size: 12px; }
.bp-sum.ok  { color: #166534; font-weight: 600; }
.bp-sum.gap { color: #65676b; }
.bp-sum.over { color: #b91c1c; font-weight: 600; }

@media (max-width: 900px) {
  .bp-layout { grid-template-columns: 1fr; }
  .bp-side { position: static; max-height: none; }
  .bp-facet { grid-template-columns: 1fr; gap: 6px; }
}

/* Modals — match the rest of the site */
.bp-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
.bp-modal.open { display: flex; }
.bp-modal .panel { background: #fff; border-radius: 12px; padding: 22px; width: 100%; max-width: 520px; box-shadow: 0 8px 40px rgba(0,0,0,0.2); max-height: 80vh; display: flex; flex-direction: column; }
.bp-modal h2 { font-size: 17px; margin: 0 0 14px; }
.bp-modal .search { padding: 9px 12px; border: 1px solid #dadde1; border-radius: 6px; font-size: 14px; font-family: inherit; margin-bottom: 10px; }
.bp-modal .list { overflow-y: auto; flex: 1; min-height: 0; max-height: 50vh; }
.bp-modal .list-item { padding: 10px 12px; border: 1px solid #dadde1; border-radius: 6px; margin-bottom: 6px; cursor: pointer; background: #fff; }
.bp-modal .list-item:hover { background: #f0f7ff; border-color: #1877f2; }
.bp-modal .list-item .n { font-weight: 700; font-size: 14px; color: #1c1e21; }
.bp-modal .list-item .d { font-size: 12px; color: #65676b; margin-top: 2px; }
.bp-modal .list-item .c { font-size: 11px; color: #65676b; margin-top: 4px; }
.bp-modal .promote-option { display: block; width: 100%; padding: 14px; border: 1px solid #dadde1; border-radius: 8px; background: #fff; cursor: pointer; text-align: left; font-family: inherit; margin-bottom: 8px; }
.bp-modal .promote-option:hover { background: #f0f7ff; border-color: #1877f2; }
.bp-modal .promote-option .t { font-weight: 700; font-size: 14px; color: #1c1e21; }
.bp-modal .promote-option .s { font-size: 12px; color: #65676b; margin-top: 3px; }
.bp-modal .actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px; }
</style>

<div id="save-toast" style="display:none; position:fixed; top:14px; left:50%; transform:translateX(-50%); background:#dcfce7; color:#166534; border:1px solid #bbf7d0; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; z-index:2000; box-shadow:0 4px 16px rgba(0,0,0,0.1);"></div>

<div class="bp-layout">
  <aside class="bp-side">
    <h4>Dimensioner</h4>
    <div id="dim-list"></div>
    <div class="bp-side-actions">
      <button type="button" onclick="openPicker()"><span class="ico"><i class="fa-solid fa-layer-group"></i></span> Fra biblioteket</button>
      <button type="button" onclick="addCustomDimension()"><span class="ico"><i class="fa-solid fa-plus"></i></span> Ny dimension</button>
    </div>
  </aside>

  <div class="bp-content" id="dim-editor"></div>
</div>

<form id="delete-form" method="POST" action="{{ url('/simulation/admin/blueprints/'.$blueprint->id) }}" style="display:none;">
  @csrf @method('DELETE')
</form>

{{-- Library picker --}}
<div class="bp-modal" id="picker">
  <div class="panel" style="max-width:600px;">
    <h2>Vælg fra biblioteket</h2>
    <input type="text" class="search" id="picker-search" placeholder="Søg dimensioner..." autofocus>
    <div class="list" id="picker-list"></div>
    <div class="actions">
      <button type="button" class="btn btn-secondary" onclick="closePicker()">Luk</button>
    </div>
  </div>
</div>

{{-- Promote --}}
<div class="bp-modal" id="promote-modal">
  <div class="panel" style="max-width:460px;">
    <h2>Gem til biblioteket</h2>
    <div style="font-size:13px; color:#65676b; margin-bottom:14px;">
      Et snapshot af denne dimension lægges i biblioteket. Eksisterende personligheder påvirkes ikke.
    </div>
    <button type="button" class="promote-option" onclick="doPromote('new')">
      <div class="t">Gem som ny</div>
      <div class="s">Opret en ny biblioteks-dimension med dette navn.</div>
    </button>
    <div id="update-existing-row" style="display:none;">
      <button type="button" class="promote-option" onclick="doPromote('update')">
        <div class="t">Opdater eksisterende</div>
        <div class="s" id="update-existing-target">—</div>
      </button>
    </div>
    <div class="actions">
      <button type="button" class="btn btn-secondary" onclick="closePromote()">Annuller</button>
    </div>
  </div>
</div>

<script>
const BP_URL = @json(url('/simulation/admin/blueprints/'.$blueprint->id));
const CSRF   = @json(csrf_token());
const LIBRARY = @json($library);

const CATEGORY_LABELS = {
  demografi: 'Demografi',
  psykometri: 'Psykometri',
  politik: 'Politik',
  sprog_adfaerd: 'Sprog & adfærd',
  subkultur: 'Subkultur',
};
const CUSTOM_CAT = '__custom';

const state = {
  parameters: @json($blueprint->parameters ?? []),
  selectedDim: 0,
};

state.parameters = state.parameters.map(p => ({
  name: p.name ?? '',
  description: p.description ?? '',
  library_parameter_id: p.library_parameter_id ?? null,
  show_on_profile: !!p.show_on_profile,
  facets: Array.isArray(p.facets) && p.facets.length
    ? p.facets.map(f => ({ name: f.name ?? '', text: f.text ?? '', weight: Number.isFinite(f.weight) ? f.weight : 0 }))
    : [{ name: '', text: '', weight: 0 }, { name: '', text: '', weight: 0 }],
}));

function escapeHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function render() {
  renderList();
  renderEditor();
}

function paramIndexForLibrary(libId) {
  return state.parameters.findIndex(p => p.library_parameter_id === libId);
}

function renderList() {
  const wrap = document.getElementById('dim-list');
  if (!state.parameters.length) {
    wrap.innerHTML = '<div style="font-size:12px; color:#65676b; padding:8px; font-style:italic;">Ingen dimensioner endnu.</div>';
    return;
  }
  wrap.innerHTML = state.parameters.map((p, i) => {
    const active = i === state.selectedDim;
    const lib = p.library_parameter_id ? '<i class="fa-solid fa-layer-group lib-icon" title="Fra biblioteket"></i>' : '';
    const name = p.name ? escapeHtml(p.name) : '<em style="color:#9ca3af;">unavngivet</em>';
    return `
      <div class="bp-dim ${active ? 'active' : ''}" onclick="selectDim(${i})">
        <span class="label">${name}</span>
        ${lib}
        <span class="meta">${(p.facets || []).length}</span>
      </div>
    `;
  }).join('');
}

function renderEditor() {
  const wrap = document.getElementById('dim-editor');
  if (!state.parameters.length) {
    wrap.innerHTML = `
      <div class="bp-empty">
        <i class="fa-solid fa-id-card"></i>
        <p>Tilføj en dimension for at komme i gang.</p>
      </div>`;
    return;
  }
  const i = state.selectedDim;
  const p = state.parameters[i];
  if (!p) return;

  const facetCount = (p.facets || []).length;
  const facetRows = p.facets.map((f, fi) => `
    <div class="bp-facet" data-fi="${fi}">
      <div class="bp-facet-name">
        <input type="text" value="${escapeHtml(f.name)}" placeholder="Facet-navn (fx lav, anekdotisk)" oninput="updateFacet(${fi}, 'name', this.value)">
        <input type="text" value="${escapeHtml(f.value ?? '')}" placeholder="Værdi (fx 16-24, 14, eller tom)" oninput="updateFacet(${fi}, 'value', this.value)" title="Demografi: 16-24 = uniform random int. 42 = literal. Tom = brug facet-navnet."
          style="margin-top:4px; padding:5px 7px; border:1px solid #dadde1; border-radius:4px; font-size:11px; font-family:inherit; color:#1c1e21; width:100%;">
      </div>
      <div class="bp-facet-weight">
        <input type="number" min="0" max="100" step="1" value="${f.weight ?? 0}" oninput="updateFacet(${fi}, 'weight', this.value)">
        <span class="u">%</span>
      </div>
      <div class="bp-facet-text">
        <textarea rows="3" placeholder="Håndskreven psykologi/kommunikations-konsekvens-tekst..." oninput="updateFacet(${fi}, 'text', this.value)">${escapeHtml(f.text)}</textarea>
      </div>
      <div class="bp-facet-actions">
        <button type="button" onclick="removeFacet(${fi})" ${facetCount <= 2 ? 'disabled title="Minimum 2 facetter"' : 'title="Slet facet"'}>
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>
    </div>
  `).join('');

  const sum = p.facets.reduce((s, f) => s + (parseInt(f.weight) || 0), 0);
  let sumLabel, sumClass;
  if (sum > 100)       { sumLabel = `Sum: ${sum}% — over grænsen`; sumClass = 'over'; }
  else if (sum === 100) { sumLabel = `Sum: 100%`;                   sumClass = 'ok'; }
  else                  { sumLabel = `Sum: ${sum}% — ${100 - sum}% får ingen tekst`; sumClass = 'gap'; }

  const libBadge = p.library_parameter_id
    ? '<span class="lib-badge"><i class="fa-solid fa-layer-group" style="font-size:9px;"></i> Fra biblioteket</span>'
    : '';

  wrap.innerHTML = `
    <div class="bp-card">
      <div class="bp-card-head">
        <div class="meta">
          <input type="text" class="dim-name" value="${escapeHtml(p.name)}" placeholder="Dimensionens navn (fx empati)" oninput="updateDim('name', this.value)">
          <input type="text" class="dim-desc" value="${escapeHtml(p.description ?? '')}" placeholder="Kort beskrivelse" oninput="updateDim('description', this.value)">
        </div>
        <div class="actions">
          ${libBadge}
          <label style="display:inline-flex; align-items:center; gap:6px; font-size:12px; color:#65676b; cursor:pointer; padding:5px 8px; border:1px solid #dadde1; border-radius:6px;" title="Vis sampled facet som badge på persona-profilen">
            <input type="checkbox" ${p.show_on_profile ? 'checked' : ''} onchange="updateDim('show_on_profile', this.checked)" style="margin:0;">
            Vis på profil
          </label>
          <button type="button" onclick="openPromote()" title="Gem til biblioteket"><i class="fa-solid fa-arrow-up"></i> Til bibliotek</button>
          <button type="button" onclick="removeDim()" style="color:#b91c1c;" title="Fjern dimension"><i class="fa-solid fa-trash"></i></button>
        </div>
      </div>
      <div>${facetRows}</div>
      <div class="bp-card-foot">
        <span class="bp-sum ${sumClass}">${sumLabel}</span>
        <span style="display:flex; gap:8px; align-items:center;">
          <button type="button" onclick="distributeEqually()" style="color:#65676b; font-weight:600;">Fordel jævnt</button>
          <button type="button" onclick="distributeNormal()" style="color:#65676b; font-weight:600;">Normalfordel</button>
          <button type="button" onclick="addFacet()"><i class="fa-solid fa-plus"></i> Tilføj facet</button>
        </span>
      </div>
    </div>
  `;
}

function selectDim(i) { state.selectedDim = i; render(); }

function updateDim(field, value) {
  state.parameters[state.selectedDim][field] = value;
  if (field === 'name') renderTree();
}

function updateFacet(fi, field, value) {
  if (field === 'weight') {
    const n = parseInt(value); value = (Number.isFinite(n) && n >= 0) ? Math.min(100, n) : 0;
  }
  state.parameters[state.selectedDim].facets[fi][field] = value;
  if (field === 'weight') {
    renderEditor();
    const el = document.querySelector(`.bp-facet[data-fi="${fi}"] .bp-facet-weight input`);
    if (el) { el.focus(); el.setSelectionRange(el.value.length, el.value.length); }
  }
  if (field === 'name') renderTree();
}

function distributeEqually() {
  const p = state.parameters[state.selectedDim];
  if (!p || !p.facets.length) return;
  const base = Math.floor(100 / p.facets.length);
  const remainder = 100 - base * p.facets.length;
  p.facets.forEach((f, i) => { f.weight = base + (i < remainder ? 1 : 0); });
  renderEditor();
}

function distributeNormal() {
  const p = state.parameters[state.selectedDim];
  if (!p || !p.facets.length) return;
  const weights = normalWeights(p.facets.length);
  p.facets.forEach((f, i) => { f.weight = weights[i]; });
  renderEditor();
}

// Gaussian-weighted distribution of `n` integer values summing to 100.
// Center at the middle index, standard deviation = n/4 so the bell fits the range nicely.
// Falls back to equal split for n <= 1.
function normalWeights(n) {
  if (n <= 0) return [];
  if (n === 1) return [100];
  const center = (n - 1) / 2;
  const std = Math.max(0.5, n / 4);
  const raw = [];
  for (let i = 0; i < n; i++) {
    raw.push(Math.exp(-((i - center) ** 2) / (2 * std * std)));
  }
  const sum = raw.reduce((a, b) => a + b, 0);
  // Scale to 100, round, distribute leftover by largest fractional remainders.
  const scaled = raw.map(v => v / sum * 100);
  const floored = scaled.map(v => Math.floor(v));
  let leftover = 100 - floored.reduce((a, b) => a + b, 0);
  const order = scaled
    .map((v, i) => ({ i, frac: v - Math.floor(v) }))
    .sort((a, b) => b.frac - a.frac);
  for (let k = 0; k < leftover; k++) floored[order[k].i] += 1;
  return floored;
}

function addCustomDimension() {
  state.parameters.push({
    name: '', description: '', library_parameter_id: null, show_on_profile: false,
    facets: [{ name: '', text: '', weight: 0 }, { name: '', text: '', weight: 0 }],
  });
  state.selectedDim = state.parameters.length - 1;
  state.collapsed[CUSTOM_CAT] = false;
  render();
}

function removeDim() {
  if (!confirm('Fjern denne dimension fra strukturen?')) return;
  state.parameters.splice(state.selectedDim, 1);
  if (state.selectedDim >= state.parameters.length) state.selectedDim = Math.max(0, state.parameters.length - 1);
  render();
}

function addFacet() {
  state.parameters[state.selectedDim].facets.push({ name: '', text: '' });
  render();
}

function removeFacet(fi) {
  const p = state.parameters[state.selectedDim];
  if (p.facets.length <= 2) return;
  p.facets.splice(fi, 1);
  render();
}

// --- Save ---
function buildFormData() {
  const fd = new FormData();
  fd.append('_token', CSRF);
  fd.append('_method', 'PATCH');
  fd.append('name', document.getElementById('bp-name').value);
  fd.append('description', document.getElementById('bp-description').value);
  state.parameters.forEach((p, i) => {
    if (p.id) fd.append(`parameters[${i}][id]`, p.id);
    fd.append(`parameters[${i}][name]`, p.name);
    fd.append(`parameters[${i}][description]`, p.description ?? '');
    fd.append(`parameters[${i}][type]`, p.type ?? 'personality');
    if (p.library_parameter_id) fd.append(`parameters[${i}][library_parameter_id]`, p.library_parameter_id);
    fd.append(`parameters[${i}][show_on_profile]`, p.show_on_profile ? 1 : 0);
    p.facets.forEach((f, j) => {
      if (f.id) fd.append(`parameters[${i}][facets][${j}][id]`, f.id);
      fd.append(`parameters[${i}][facets][${j}][name]`, f.name);
      fd.append(`parameters[${i}][facets][${j}][text]`, f.text);
      fd.append(`parameters[${i}][facets][${j}][weight]`, f.weight ?? 0);
      fd.append(`parameters[${i}][facets][${j}][value]`, f.value ?? '');
    });
  });
  return fd;
}

function showToast(msg, ok = true) {
  const el = document.getElementById('save-toast');
  el.textContent = msg;
  el.style.background = ok ? '#dcfce7' : '#fee2e2';
  el.style.color = ok ? '#166534' : '#b91c1c';
  el.style.borderColor = ok ? '#bbf7d0' : '#fecaca';
  el.style.display = 'block';
  setTimeout(() => { el.style.display = 'none'; }, 1800);
}

async function saveBlueprint() {
  const btn = document.getElementById('save-btn');
  btn.disabled = true;
  try {
    const r = await fetch(BP_URL, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: buildFormData(),
    });
    if (!r.ok) {
      const err = await r.json().catch(() => ({}));
      const msg = err.errors ? Object.values(err.errors).flat().join(' · ') : 'Kunne ikke gemme.';
      showToast(msg, false);
      return false;
    }
    showToast('Gemt');
    return true;
  } catch (e) {
    showToast('Netværksfejl', false);
    return false;
  } finally {
    btn.disabled = false;
  }
}

// --- Picker ---
function openPicker() {
  document.getElementById('picker').classList.add('open');
  document.getElementById('picker-search').value = '';
  renderPicker('');
  setTimeout(() => document.getElementById('picker-search').focus(), 50);
}
function closePicker() { document.getElementById('picker').classList.remove('open'); }

function renderPicker(query) {
  const list = document.getElementById('picker-list');
  const q = (query || '').toLowerCase();
  const matched = LIBRARY.filter(p =>
    !q || (p.name || '').toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q)
  );
  if (!matched.length) {
    list.innerHTML = '<div style="text-align:center; padding:30px; color:#65676b; font-size:13px;">Ingen dimensioner i biblioteket matcher.</div>';
    return;
  }
  const cats = [...Object.keys(CATEGORY_LABELS), ...new Set(matched.map(p => p.category).filter(c => c && !CATEGORY_LABELS[c]))];
  let html = '';
  for (const cat of cats) {
    const items = matched.filter(p => p.category === cat);
    if (!items.length) continue;
    html += `
      <div class="bp-picker-cat">
        <div class="h">${escapeHtml(CATEGORY_LABELS[cat] ?? cat)}</div>
        ${items.map(p => {
          const inBp = paramIndexForLibrary(p.id) >= 0;
          return `
            <div class="item ${inBp ? 'in-bp' : ''}" onclick="${inBp ? '' : `insertFromLibrary(${p.id})`}">
              <div class="n">${escapeHtml(p.name)}</div>
              ${p.description ? `<div class="d">${escapeHtml(p.description)}</div>` : ''}
              <div class="c">${(p.facets || []).length} facetter</div>
            </div>
          `;
        }).join('')}
      </div>
    `;
  }
  // uncategorised matches
  const others = matched.filter(p => !p.category);
  if (others.length) {
    html += `
      <div class="bp-picker-cat">
        <div class="h">Egne dimensioner</div>
        ${others.map(p => {
          const inBp = paramIndexForLibrary(p.id) >= 0;
          return `
            <div class="item ${inBp ? 'in-bp' : ''}" onclick="${inBp ? '' : `insertFromLibrary(${p.id})`}">
              <div class="n">${escapeHtml(p.name)}</div>
              ${p.description ? `<div class="d">${escapeHtml(p.description)}</div>` : ''}
              <div class="c">${(p.facets || []).length} facetter</div>
            </div>
          `;
        }).join('')}
      </div>
    `;
  }
  list.innerHTML = html;
}
document.getElementById('picker-search').addEventListener('input', e => renderPicker(e.target.value));

function insertFromLibrary(id) {
  const lib = LIBRARY.find(p => p.id === id);
  if (!lib) return;
  state.parameters.push({
    name: lib.name,
    description: lib.description ?? '',
    library_parameter_id: lib.id,
    show_on_profile: false,
    facets: (lib.facets || []).map(f => ({ name: f.name ?? '', text: f.text ?? '', weight: Number.isFinite(f.weight) ? f.weight : 0 })),
  });
  state.selectedDim = state.parameters.length - 1;
  closePicker();
  render();
}

// --- Promote ---
function openPromote() {
  const p = state.parameters[state.selectedDim];
  if (!p || !p.name.trim()) { showToast('Giv dimensionen et navn først.', false); return; }
  const target = LIBRARY.find(l => l.id === p.library_parameter_id) || LIBRARY.find(l => l.name === p.name);
  const row = document.getElementById('update-existing-row');
  if (target) {
    row.style.display = 'block';
    document.getElementById('update-existing-target').textContent = `Overskriv "${target.name}" i biblioteket.`;
    row.dataset.targetId = target.id;
  } else {
    row.style.display = 'none';
  }
  document.getElementById('promote-modal').classList.add('open');
}
function closePromote() { document.getElementById('promote-modal').classList.remove('open'); }

async function doPromote(mode) {
  const ok = await saveBlueprint();
  if (!ok) return;
  const targetId = document.getElementById('update-existing-row').dataset.targetId;
  try {
    const r = await fetch(BP_URL + '/promote', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify({
        parameter_index: state.selectedDim,
        mode,
        target_id: mode === 'update' ? Number(targetId) : null,
      }),
    });
    const data = await r.json();
    if (!r.ok || !data.ok) { showToast(data.error || 'Kunne ikke gemme til biblioteket.', false); return; }
    state.parameters[state.selectedDim].library_parameter_id = data.library_parameter_id;
    closePromote();
    showToast(mode === 'new' ? `"${data.name}" tilføjet til biblioteket` : `"${data.name}" opdateret`);
    render();
  } catch (e) {
    showToast('Netværksfejl', false);
  }
}

render();
</script>

@endsection
