@extends('layouts.app')
@section('content')

@include('admin.blueprints._header')

@component('admin.blueprints._subtabs', ['blueprint' => $blueprint])
  <button type="button" class="btn btn-primary" id="save-btn" onclick="saveBlueprint()">
    <i class="fa-solid fa-floppy-disk"></i> Gem dimensioner
  </button>
@endcomponent

<input type="hidden" id="bp-name" value="{{ $blueprint->name }}">
<input type="hidden" id="bp-description" value="{{ $blueprint->description }}">

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
.bp-dim { display: flex; align-items: center; gap: 6px; padding: 9px 10px; font-size: 13px; color: #1c1e21; cursor: pointer; border-top: 1px solid #f0f2f5; }
.bp-dim:first-child { border-top: none; }
.bp-dim:hover { background: #f7f8fa; }
.bp-dim.active { background: #e7f3ff; color: #1877f2; font-weight: 600; border-radius: 6px; }
.bp-dim.active + .bp-dim { border-top-color: transparent; }
.bp-dim .label { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.bp-dim .meta { font-size: 11px; color: #65676b; }
.bp-dim.active .meta { color: #1877f2; }
.bp-dim .lib-icon { font-size: 10px; color: #65676b; }
.bp-side-actions { display: flex; flex-direction: column; gap: 4px; padding: 4px 4px 8px; margin-bottom: 4px; border-bottom: 1px solid #f0f2f5; }
.bp-side-actions button { background: none; border: none; text-align: left; padding: 7px 10px; font-size: 13px; color: #1c1e21; cursor: pointer; border-radius: 6px; font-family: inherit; display: flex; align-items: center; gap: 8px; }
.bp-side-actions button:hover { background: #f0f2f5; }
.bp-side-actions button .ico { width: 14px; color: #65676b; }
.bp-add-facet { background: none; border: none; color: #1877f2; font-size: 13px; font-weight: 600; cursor: pointer; padding: 4px 10px; border-radius: 6px; font-family: inherit; }
.bp-add-facet:hover { background: #e7f3ff; }

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
.bp-card-head .actions { display: flex; gap: 6px; flex-shrink: 0; align-items: center; }
.bp-card-head .actions button { background: none; border: 1px solid #dadde1; border-radius: 6px; padding: 5px 10px; font-size: 12px; cursor: pointer; color: #65676b; white-space: nowrap; }
.bp-card-head .actions button:hover { background: #fff; color: #1c1e21; }
.bp-card-head .actions .profile-toggle { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #65676b; cursor: pointer; padding: 5px 8px; border: 1px solid #dadde1; border-radius: 6px; user-select: none; }
.bp-card-head .actions .profile-toggle:hover { background: #fff; color: #1c1e21; }

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

/* --- Lille Sokrates --- */
.sok-toggle {
  position: fixed; bottom: 22px; right: 22px; z-index: 900;
  width: 56px; height: 56px; border-radius: 50%; border: none;
  background: #1877f2; color: #fff; font-size: 22px; cursor: pointer;
  box-shadow: 0 6px 20px rgba(24,119,242,0.35);
  display: flex; align-items: center; justify-content: center;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.sok-toggle:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(24,119,242,0.45); }
.sok-toggle.open { background: #fff; color: #1877f2; }
.sok-panel {
  position: fixed; top: 0; right: 0; bottom: 0; width: 400px; max-width: 100vw;
  background: #fff; box-shadow: -6px 0 24px rgba(0,0,0,0.12); z-index: 800;
  transform: translateX(100%); transition: transform 0.22s ease;
  display: flex; flex-direction: column;
}
.sok-panel.open { transform: translateX(0); }
.sok-head {
  padding: 14px 16px; border-bottom: 1px solid #e4e6eb;
  display: flex; align-items: center; gap: 10px;
}
.sok-head h2 { margin: 0; font-size: 15px; font-weight: 700; flex: 1; }
.sok-head .sub { font-size: 11px; color: #65676b; margin-top: 2px; }
.sok-head .close { background: none; border: none; cursor: pointer; color: #65676b; font-size: 18px; padding: 4px 8px; border-radius: 6px; }
.sok-head .close:hover { background: #f0f2f5; color: #1c1e21; }
.sok-messages { flex: 1; overflow-y: auto; padding: 12px 16px; background: #f7f8fa; }
.sok-msg { margin-bottom: 10px; max-width: 92%; }
.sok-msg.user { margin-left: auto; }
.sok-msg.assistant { margin-right: auto; }
.sok-msg-bubble {
  padding: 9px 12px; border-radius: 14px; font-size: 13px; line-height: 1.45;
  white-space: pre-wrap; word-wrap: break-word;
}
.sok-msg.user .sok-msg-bubble { background: #1877f2; color: #fff; border-bottom-right-radius: 4px; }
.sok-msg.assistant .sok-msg-bubble { background: #fff; color: #1c1e21; border: 1px solid #e4e6eb; border-bottom-left-radius: 4px; }
.sok-proposal {
  margin-top: 8px; padding: 10px 12px; background: #fffbeb; border: 1px solid #fde68a;
  border-radius: 8px; font-size: 12px; color: #78350f;
}
.sok-proposal h4 { margin: 0 0 6px; font-size: 12px; font-weight: 700; color: #92400e; }
.sok-proposal ul { margin: 0 0 8px; padding-left: 16px; line-height: 1.5; }
.sok-proposal li { margin-bottom: 2px; }
.sok-proposal .row { display: flex; gap: 6px; margin-top: 8px; }
.sok-proposal .row button {
  padding: 6px 12px; border-radius: 6px; border: 1px solid; cursor: pointer;
  font-size: 12px; font-weight: 600; font-family: inherit;
}
.sok-proposal .apply { background: #1877f2; color: #fff; border-color: #1877f2; }
.sok-proposal .apply:hover { background: #1465d3; }
.sok-proposal .cancel { background: #fff; color: #65676b; border-color: #dadde1; }
.sok-proposal .cancel:hover { background: #f0f2f5; }
.sok-proposal.applied { background: #dcfce7; border-color: #bbf7d0; color: #166534; }
.sok-proposal.applied h4 { color: #166534; }
.sok-proposal.cancelled { opacity: 0.55; }
.sok-empty { text-align: center; color: #65676b; font-size: 13px; padding: 30px 14px; }
.sok-empty i { font-size: 28px; opacity: 0.4; display: block; margin-bottom: 10px; }
.sok-input {
  border-top: 1px solid #e4e6eb; padding: 10px 12px; display: flex; gap: 8px; align-items: flex-end;
  background: #fff;
}
.sok-input textarea {
  flex: 1; resize: none; padding: 8px 10px; border: 1px solid #dadde1; border-radius: 8px;
  font-family: inherit; font-size: 13px; line-height: 1.4; min-height: 36px; max-height: 120px;
}
.sok-input textarea:focus { outline: none; border-color: #1877f2; box-shadow: 0 0 0 2px #e7f3ff; }
.sok-input button {
  background: #1877f2; color: #fff; border: none; padding: 8px 14px; border-radius: 8px;
  font-weight: 600; font-size: 13px; cursor: pointer; font-family: inherit; flex-shrink: 0;
}
.sok-input button:disabled { opacity: 0.5; cursor: not-allowed; }
.sok-thinking { padding: 8px 12px; font-size: 12px; color: #65676b; font-style: italic; }
</style>

<div id="save-toast" style="display:none; position:fixed; top:14px; left:50%; transform:translateX(-50%); background:#dcfce7; color:#166534; border:1px solid #bbf7d0; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; z-index:2000; box-shadow:0 4px 16px rgba(0,0,0,0.1);"></div>

<div class="bp-layout">
  <aside class="bp-side">
    <div class="bp-side-actions">
      <button type="button" onclick="openPicker()"><span class="ico"><i class="fa-solid fa-layer-group"></i></span> Fra biblioteket</button>
      <button type="button" onclick="addCustomDimension()"><span class="ico"><i class="fa-solid fa-plus"></i></span> Ny dimension</button>
    </div>
    <h4>Dimensioner</h4>
    <div id="dim-list"></div>
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

function uuid() {
  return (crypto.randomUUID ? crypto.randomUUID()
    : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
        const r = Math.random() * 16 | 0;
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
      }));
}

state.parameters = state.parameters.map(p => ({
  id: p.id || uuid(),
  name: p.name ?? '',
  description: p.description ?? '',
  library_parameter_id: p.library_parameter_id ?? null,
  show_on_profile: !!p.show_on_profile,
  type: p.type ?? 'personality',
  tier: p.tier ?? 'primary',
  facets: Array.isArray(p.facets) && p.facets.length
    ? p.facets.map(f => ({
        id: f.id || uuid(),
        name: f.name ?? '',
        text: f.text ?? '',
        weight: Number.isFinite(f.weight) ? f.weight : 0,
        value: f.value ?? '',
      }))
    : [{ id: uuid(), name: '', text: '', weight: 0, value: '' }],
}));

// Single-step undo snapshot per dimension. Captured BEFORE any mutation.
function snapshotDim(i) {
  if (i == null || !state.parameters[i]) return;
  const p = state.parameters[i];
  // Don't overwrite an existing snapshot in the same edit "burst" — that would
  // let the user undo past their own pre-AI state. Keep the first snapshot taken
  // since the last successful undo or save.
  if (p._previous) return;
  p._previous = JSON.parse(JSON.stringify({
    name: p.name, description: p.description, type: p.type, tier: p.tier,
    show_on_profile: p.show_on_profile, library_parameter_id: p.library_parameter_id,
    facets: p.facets,
  }));
}

function undoDim(i) {
  const p = state.parameters[i];
  if (!p || !p._previous) return;
  Object.assign(p, p._previous);
  p._previous = null;
  render();
}

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
  const isDemographic = (p.type || 'personality') === 'demographic';
  const facetRows = p.facets.map((f, fi) => `
    <div class="bp-facet" data-fi="${fi}">
      <div class="bp-facet-name">
        <input type="text" value="${escapeHtml(f.name)}" placeholder="Facet-navn (fx lav, anekdotisk)" oninput="updateFacet(${fi}, 'name', this.value)">
        ${isDemographic ? `<input type="text" value="${escapeHtml(f.value ?? '')}" placeholder="Værdi (fx 16-24, 14, eller tom)" oninput="updateFacet(${fi}, 'value', this.value)" title="16-24 = uniform random int. 42 = literal. Tom = brug facet-navnet."
          style="margin-top:4px; padding:5px 7px; border:1px solid #dadde1; border-radius:4px; font-size:11px; font-family:inherit; color:#1c1e21; width:100%;">` : ''}
      </div>
      <div class="bp-facet-weight">
        <input type="number" min="0" max="100" step="1" value="${f.weight ?? 0}" oninput="updateFacet(${fi}, 'weight', this.value)">
        <span class="u">%</span>
      </div>
      <div class="bp-facet-text">
        <textarea rows="3" placeholder="Håndskreven psykologi/kommunikations-konsekvens-tekst..." oninput="updateFacet(${fi}, 'text', this.value)">${escapeHtml(f.text)}</textarea>
      </div>
      <div class="bp-facet-actions">
        <button type="button" onclick="removeFacet(${fi})" ${facetCount <= 1 ? 'disabled title="Mindst én facet"' : 'title="Slet facet"'}>
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

  wrap.innerHTML = `
    <div class="bp-card">
      <div class="bp-card-head">
        <div class="meta">
          <input type="text" class="dim-name" value="${escapeHtml(p.name)}" placeholder="Dimensionens navn (fx empati)" oninput="updateDim('name', this.value)">
          <input type="text" class="dim-desc" value="${escapeHtml(p.description ?? '')}" placeholder="Kort beskrivelse" oninput="updateDim('description', this.value)">
        </div>
        <div class="actions">
          <label class="profile-toggle" title="Personlighed: facet-tekst farver LLM'ens stemme. Demografi: facet-navnet (eller værdi) er en konkret egenskab — alder, region, etc.">
            <span style="color:#65676b;">Type:</span>
            <select onchange="updateDim('type', this.value)" style="border:none; background:transparent; font-size:12px; font-weight:600; cursor:pointer; font-family:inherit; padding:0;">
              <option value="personality" ${(p.type || 'personality') === 'personality' ? 'selected' : ''}>Personlighed</option>
              <option value="demographic" ${(p.type || 'personality') === 'demographic' ? 'selected' : ''}>Demografi</option>
            </select>
          </label>
          <label class="profile-toggle" title="Primær = grundlæggende træk (uafhængig sampling). Sekundær = livsstil/præferencer (LLM tjekker plausibilitet mod primære).">
            <span style="color:#65676b;">Tier:</span>
            <select onchange="updateDim('tier', this.value)" style="border:none; background:transparent; font-size:12px; font-weight:600; cursor:pointer; font-family:inherit; padding:0;">
              <option value="primary" ${(p.tier || 'primary') === 'primary' ? 'selected' : ''}>Primær</option>
              <option value="secondary" ${(p.tier || 'primary') === 'secondary' ? 'selected' : ''}>Sekundær</option>
            </select>
          </label>
          <label class="profile-toggle" title="Vis sampled facet som badge på persona-profilen">
            <input type="checkbox" ${p.show_on_profile ? 'checked' : ''} onchange="updateDim('show_on_profile', this.checked)" style="margin:0;">
            Vis på profil
          </label>
          <button type="button" onclick="distributeEqually()" title="Fordel vægte jævnt">Fordel jævnt</button>
          <button type="button" onclick="distributeNormal()" title="Normalfordel vægte">Normalfordel</button>
          ${p._previous ? `<button type="button" onclick="undoDim(${i})" title="Fortryd seneste ændring af denne dimension" style="color:#92400e;"><i class="fa-solid fa-rotate-left"></i> Fortryd</button>` : ''}
          <button type="button" onclick="removeDim()" style="color:#b91c1c;" title="Fjern dimension"><i class="fa-solid fa-trash"></i></button>
        </div>
      </div>
      <div>${facetRows}</div>
      <div class="bp-card-foot">
        <span class="bp-sum ${sumClass}">${sumLabel}</span>
        <button type="button" class="bp-add-facet" onclick="addFacet()"><i class="fa-solid fa-plus"></i> Tilføj facet</button>
      </div>
    </div>
  `;
}

function selectDim(i) { state.selectedDim = i; render(); }

function updateDim(field, value) {
  snapshotDim(state.selectedDim);
  state.parameters[state.selectedDim][field] = value;
  if (field === 'name') renderList();
  if (field === 'type') renderEditor(); // toggle Værdi inputs on facets
}

function updateFacet(fi, field, value) {
  if (field === 'weight') {
    const n = parseInt(value); value = (Number.isFinite(n) && n >= 0) ? Math.min(100, n) : 0;
  }
  snapshotDim(state.selectedDim);
  state.parameters[state.selectedDim].facets[fi][field] = value;
  if (field === 'weight') {
    renderEditor();
    const el = document.querySelector(`.bp-facet[data-fi="${fi}"] .bp-facet-weight input`);
    if (el) { el.focus(); el.setSelectionRange(el.value.length, el.value.length); }
  }
  if (field === 'name') renderList();
}

function distributeEqually() {
  const p = state.parameters[state.selectedDim];
  if (!p || !p.facets.length) return;
  snapshotDim(state.selectedDim);
  const base = Math.floor(100 / p.facets.length);
  const remainder = 100 - base * p.facets.length;
  p.facets.forEach((f, i) => { f.weight = base + (i < remainder ? 1 : 0); });
  renderEditor();
}

function distributeNormal() {
  const p = state.parameters[state.selectedDim];
  if (!p || !p.facets.length) return;
  snapshotDim(state.selectedDim);
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
    id: uuid(),
    name: '', description: '', library_parameter_id: null, show_on_profile: false,
    type: 'personality', tier: 'primary',
    facets: [
      { id: uuid(), name: '', text: '', weight: 0, value: '' },
      { id: uuid(), name: '', text: '', weight: 0, value: '' },
    ],
  });
  state.selectedDim = state.parameters.length - 1;
  render();
}

function removeDim() {
  if (!confirm('Fjern denne dimension fra strukturen?')) return;
  state.parameters.splice(state.selectedDim, 1);
  if (state.selectedDim >= state.parameters.length) state.selectedDim = Math.max(0, state.parameters.length - 1);
  render();
}

function addFacet() {
  snapshotDim(state.selectedDim);
  state.parameters[state.selectedDim].facets.push({ id: uuid(), name: '', text: '', weight: 0, value: '' });
  render();
}

function removeFacet(fi) {
  const p = state.parameters[state.selectedDim];
  if (p.facets.length <= 1) return;
  snapshotDim(state.selectedDim);
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
    // _previous is a UI-only snapshot for the Fortryd button; never sent to server
    if (p._previous) delete p._previous;
    if (p.id) fd.append(`parameters[${i}][id]`, p.id);
    fd.append(`parameters[${i}][name]`, p.name);
    fd.append(`parameters[${i}][description]`, p.description ?? '');
    fd.append(`parameters[${i}][type]`, p.type ?? 'personality');
    fd.append(`parameters[${i}][tier]`, p.tier ?? 'primary');
    if (p.library_parameter_id) fd.append(`parameters[${i}][library_parameter_id]`, p.library_parameter_id);
    fd.append(`parameters[${i}][show_on_profile]`, p.show_on_profile ? 1 : 0);
    p.facets.forEach((f, j) => {
      if (f.id) fd.append(`parameters[${i}][facets][${j}][id]`, f.id);
      fd.append(`parameters[${i}][facets][${j}][name]`, f.name ?? '');
      fd.append(`parameters[${i}][facets][${j}][text]`, f.text ?? '');
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
    id: uuid(),
    name: lib.name,
    description: lib.description ?? '',
    library_parameter_id: lib.id,
    show_on_profile: false,
    type: lib.type ?? 'personality',
    tier: 'primary',
    facets: (lib.facets || []).map(f => ({
      id: uuid(),
      name: f.name ?? '',
      text: f.text ?? '',
      weight: Number.isFinite(f.weight) ? f.weight : 0,
      value: f.value ?? '',
    })),
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

// ============================================================================
// Lille Sokrates — AI sparring partner for blueprint authoring
// ============================================================================
const sok = { messages: [], open: false, pending: false };
const SOK_URL = BP_URL + '/chat';

function sokToggle() {
  sok.open = !sok.open;
  document.getElementById('sok-panel').classList.toggle('open', sok.open);
  document.getElementById('sok-toggle').classList.toggle('open', sok.open);
  if (sok.open) setTimeout(() => document.getElementById('sok-input').focus(), 200);
}

function sokRender() {
  const wrap = document.getElementById('sok-messages');
  if (!sok.messages.length && !sok.pending) {
    wrap.innerHTML = `
      <div class="sok-empty">
        <i class="fa-regular fa-comments"></i>
        <div><strong>Lille Sokrates</strong></div>
        <div style="margin-top:8px; line-height:1.5;">
          Spar med en samtalepartner om denne personlighed. Beskriv hvad du ser hos folk i shitstorms,
          så hjælper jeg dig med at omsætte intuitionen til præcis prompt-tekst.
        </div>
      </div>`;
    return;
  }
  wrap.innerHTML = sok.messages.map((m, idx) => {
    if (m.role === 'user') {
      return `<div class="sok-msg user"><div class="sok-msg-bubble">${escapeHtml(m.content)}</div></div>`;
    }
    let html = `<div class="sok-msg assistant"><div class="sok-msg-bubble">${escapeHtml(m.content || '(intet svar)')}</div>`;
    if (m.operations && m.operations.length) {
      const stateClass = m.applied === true ? 'applied' : (m.applied === false ? 'cancelled' : '');
      const stateLabel = m.applied === true ? '✓ Anvendt' : (m.applied === false ? 'Annulleret' : 'Forslag');
      html += `<div class="sok-proposal ${stateClass}">
        <h4>${stateLabel}: ${m.operations.length} ændring${m.operations.length === 1 ? '' : 'er'}</h4>
        <ul>${m.operations.map(o => `<li>${escapeHtml(describeOp(o))}</li>`).join('')}</ul>`;
      if (m.applied === undefined) {
        html += `<div class="row">
          <button class="apply" onclick="sokApplyMessage(${idx})">Anvend</button>
          <button class="cancel" onclick="sokCancelMessage(${idx})">Annullér</button>
        </div>`;
      }
      html += `</div>`;
    }
    html += `</div>`;
    return html;
  }).join('') + (sok.pending ? '<div class="sok-thinking"><span class="spinner" style="border-color:#1877f2; border-top-color:transparent;"></span> Lille Sokrates tænker…</div>' : '');
  wrap.scrollTop = wrap.scrollHeight;
}

function describeOp(op) {
  const d = op.data || {};
  switch (op.op) {
    case 'add_facet':       return `Tilføj facet "${d.name || '?'}" (${d.weight ?? 0}%) til dimension`;
    case 'update_facet':    return `Opdatér facet ${d.facet_id ? d.facet_id.substring(0,8) : '?'}`;
    case 'delete_facet':    return `Slet facet ${d.facet_id ? d.facet_id.substring(0,8) : '?'}`;
    case 'add_dimension':   return `Tilføj ny dimension "${d.name || '?'}" med ${(d.facets || []).length} facetter`;
    case 'update_dimension':return `Opdatér dimension "${d.name || d.dimension_id?.substring(0,8) || '?'}"`;
    case 'delete_dimension':return `Slet dimension`;
    default: return op.op;
  }
}

async function sokSend(e) {
  if (e) e.preventDefault();
  const ta = document.getElementById('sok-input');
  const text = ta.value.trim();
  if (!text || sok.pending) return;
  sok.messages.push({ role: 'user', content: text });
  ta.value = '';
  ta.style.height = 'auto';
  sok.pending = true;
  sokRender();
  try {
    const r = await fetch(SOK_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify({
        messages: sok.messages.filter(m => m.role === 'user' || m.role === 'assistant').map(m => ({ role: m.role, content: m.content })),
        state: state.parameters.map(p => ({
          id: p.id, name: p.name, description: p.description, type: p.type, tier: p.tier,
          show_on_profile: p.show_on_profile,
          facets: (p.facets || []).map(f => ({
            id: f.id, name: f.name, text: f.text, weight: f.weight, value: f.value || '',
          })),
        })),
        selected_index: state.selectedDim,
      }),
    });
    const data = await r.json();
    sok.messages.push({
      role: 'assistant',
      content: data.message || '(intet svar)',
      operations: Array.isArray(data.operations) ? data.operations : [],
    });
  } catch (err) {
    sok.messages.push({ role: 'assistant', content: 'Forbindelsesfejl — prøv igen. (' + err.message + ')', operations: [] });
  } finally {
    sok.pending = false;
    sokRender();
  }
}

function sokApplyMessage(idx) {
  const m = sok.messages[idx];
  if (!m || !m.operations) return;
  let applied = 0;
  for (const op of m.operations) {
    if (sokApplyOp(op)) applied++;
  }
  m.applied = true;
  sokRender();
  render();
  showToast(applied > 0 ? `${applied} ændring${applied === 1 ? '' : 'er'} anvendt` : 'Ingen ændringer kunne anvendes', applied > 0);
}

function sokCancelMessage(idx) {
  const m = sok.messages[idx];
  if (!m) return;
  m.applied = false;
  sokRender();
}

function sokApplyOp(op) {
  try {
    switch (op.op) {
      case 'add_facet':         return opAddFacet(op.data || {});
      case 'update_facet':      return opUpdateFacet(op.data || {});
      case 'delete_facet':      return opDeleteFacet(op.data || {});
      case 'add_dimension':     return opAddDimension(op.data || {});
      case 'update_dimension':  return opUpdateDimension(op.data || {});
      case 'delete_dimension':  return opDeleteDimension(op.data || {});
      default: return false;
    }
  } catch (e) { return false; }
}

function opAddFacet(d) {
  const idx = state.parameters.findIndex(p => p.id === d.dimension_id);
  if (idx < 0) return false;
  snapshotDim(idx);
  state.parameters[idx].facets.push({
    id: uuid(),
    name: d.name ?? '',
    text: d.text ?? '',
    weight: Number(d.weight) || 0,
    value: d.value ?? '',
  });
  return true;
}
function opUpdateFacet(d) {
  const idx = state.parameters.findIndex(p => p.id === d.dimension_id);
  if (idx < 0) return false;
  const facet = state.parameters[idx].facets.find(f => f.id === d.facet_id);
  if (!facet) return false;
  snapshotDim(idx);
  if (d.name !== undefined)   facet.name = d.name;
  if (d.text !== undefined)   facet.text = d.text;
  if (d.weight !== undefined) facet.weight = Number(d.weight) || 0;
  if (d.value !== undefined)  facet.value = d.value;
  return true;
}
function opDeleteFacet(d) {
  const idx = state.parameters.findIndex(p => p.id === d.dimension_id);
  if (idx < 0) return false;
  const before = state.parameters[idx].facets.length;
  snapshotDim(idx);
  state.parameters[idx].facets = state.parameters[idx].facets.filter(f => f.id !== d.facet_id);
  return state.parameters[idx].facets.length < before;
}
function opAddDimension(d) {
  state.parameters.push({
    id: uuid(),
    name: d.name ?? '',
    description: d.description ?? '',
    library_parameter_id: null,
    show_on_profile: !!d.show_on_profile,
    type: d.type ?? 'personality',
    tier: d.tier ?? 'primary',
    facets: (d.facets || []).map(f => ({
      id: uuid(),
      name: f.name ?? '',
      text: f.text ?? '',
      weight: Number(f.weight) || 0,
      value: f.value ?? '',
    })),
  });
  state.selectedDim = state.parameters.length - 1;
  return true;
}
function opUpdateDimension(d) {
  const idx = state.parameters.findIndex(p => p.id === d.dimension_id);
  if (idx < 0) return false;
  snapshotDim(idx);
  ['name','description','type','tier','show_on_profile'].forEach(field => {
    if (d[field] !== undefined) state.parameters[idx][field] = d[field];
  });
  return true;
}
function opDeleteDimension(d) {
  const idx = state.parameters.findIndex(p => p.id === d.dimension_id);
  if (idx < 0) return false;
  state.parameters.splice(idx, 1);
  if (state.selectedDim >= state.parameters.length) state.selectedDim = Math.max(0, state.parameters.length - 1);
  return true;
}

// Bind events
document.getElementById('sok-toggle').addEventListener('click', sokToggle);
document.getElementById('sok-close').addEventListener('click', sokToggle);
document.getElementById('sok-form').addEventListener('submit', sokSend);
document.getElementById('sok-input').addEventListener('keydown', (e) => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sokSend(); }
});
document.getElementById('sok-input').addEventListener('input', (e) => {
  e.target.style.height = 'auto';
  e.target.style.height = Math.min(120, e.target.scrollHeight) + 'px';
});
sokRender();
</script>

<button id="sok-toggle" class="sok-toggle" title="Spar med Lille Sokrates"><i class="fa-regular fa-comments"></i></button>

<aside id="sok-panel" class="sok-panel">
  <div class="sok-head">
    <div style="flex:1;">
      <h2><i class="fa-solid fa-brain" style="color:#1877f2; margin-right:6px;"></i> Lille Sokrates</h2>
      <div class="sub">Samtalepartner — hjælper med at omsætte intuition til prompt-tekst</div>
    </div>
    <button type="button" class="close" id="sok-close" title="Luk (Esc)">×</button>
  </div>
  <div class="sok-messages" id="sok-messages"></div>
  <form class="sok-input" id="sok-form">
    <textarea id="sok-input" placeholder="Skriv til Sokrates… (Enter for at sende, Shift+Enter for ny linje)" rows="1"></textarea>
    <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
  </form>
</aside>

@endsection
