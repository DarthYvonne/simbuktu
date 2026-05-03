@extends('layouts.app')
@section('content')

<form id="bp-form" method="POST" action="{{ url('/simulation/admin/blueprints/'.$blueprint->id) }}">
  @csrf @method('PATCH')

  <div class="view-header">
    <div style="flex:1; min-width:0;">
      <a href="{{ url('/simulation/admin/blueprints') }}" style="font-size:13px; color:#65676b; text-decoration:none;">
        <i class="fa-solid fa-arrow-left"></i> Personlighedsstrukturer
      </a>
      <input type="text" name="name" required value="{{ old('name', $blueprint->name) }}"
        style="display:block; margin-top:6px; font-size:22px; font-weight:700; padding:4px 6px; border:1px solid transparent; border-radius:6px; font-family:inherit; width:100%; max-width:520px;"
        onfocus="this.style.borderColor='#dadde1'" onblur="this.style.borderColor='transparent'">
      <input type="text" name="description" value="{{ old('description', $blueprint->description) }}" placeholder="Beskrivelse"
        style="display:block; margin-top:2px; font-size:13px; color:#65676b; padding:4px 6px; border:1px solid transparent; border-radius:6px; font-family:inherit; width:100%; max-width:520px;"
        onfocus="this.style.borderColor='#dadde1'" onblur="this.style.borderColor='transparent'">
    </div>
    <div style="display:flex; gap:8px;">
      <button type="button" class="btn btn-secondary" style="color:#b91c1c;"
        onclick="if(confirm('Slet denne personlighedsstruktur?')) { document.getElementById('delete-form').submit(); }">
        <i class="fa-solid fa-trash"></i>
      </button>
      <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-floppy-disk"></i> Gem personlighedsstruktur
      </button>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-error">
      @foreach ($errors->all() as $err)<div>{{ $err }}</div>@endforeach
    </div>
  @endif

  <div style="display:grid; grid-template-columns: 280px 1fr; gap:14px; align-items:start;">

    {{-- LEFT: dimension list --}}
    <div style="background:#fff; border:1px solid #dadde1; border-radius:8px; padding:10px;">
      <div style="font-size:12px; font-weight:700; color:#65676b; text-transform:uppercase; letter-spacing:.4px; padding:4px 6px 8px;">Dimensioner</div>
      <div id="dim-list"></div>
      <div style="display:flex; flex-direction:column; gap:4px; margin-top:8px; padding-top:8px; border-top:1px solid #f0f2f5;">
        <button type="button" class="btn btn-secondary" style="font-size:13px; justify-content:flex-start;" onclick="addNewDimension()">
          <i class="fa-solid fa-plus"></i> Ny dimension
        </button>
        <button type="button" class="btn btn-secondary" style="font-size:13px; justify-content:flex-start;" onclick="openPicker()">
          <i class="fa-solid fa-layer-group"></i> Fra biblioteket
        </button>
      </div>
    </div>

    {{-- RIGHT: dimension editor --}}
    <div id="dim-editor" style="background:#fff; border:1px solid #dadde1; border-radius:8px; padding:18px; min-height:400px;"></div>

  </div>

  <div id="bp-hidden"></div>
</form>

<form id="delete-form" method="POST" action="{{ url('/simulation/admin/blueprints/'.$blueprint->id) }}" style="display:none;">
  @csrf @method('DELETE')
</form>

{{-- Library picker modal --}}
<div id="picker" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:12px; padding:22px; width:100%; max-width:560px; max-height:80vh; display:flex; flex-direction:column; box-shadow:0 8px 40px rgba(0,0,0,.2);">
    <h2 style="margin:0 0 14px; font-size:17px;">Vælg fra biblioteket</h2>
    <input type="text" id="picker-search" placeholder="Søg dimensioner..." autofocus
      style="padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit; margin-bottom:10px;">
    <div id="picker-list" style="overflow-y:auto; flex:1; min-height:0; max-height:50vh;"></div>
    <div style="display:flex; justify-content:flex-end; margin-top:14px;">
      <button type="button" class="btn btn-secondary" onclick="closePicker()">Luk</button>
    </div>
  </div>
</div>

{{-- Promote modal --}}
<div id="promote-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:12px; padding:24px; width:100%; max-width:480px; box-shadow:0 8px 40px rgba(0,0,0,.2);">
    <h2 style="margin:0 0 14px; font-size:17px;">Gem til biblioteket</h2>
    <div style="font-size:13px; color:#65676b; margin-bottom:14px;">
      Snapshot af denne dimension lægges i biblioteket. Eksisterende strukturer påvirkes ikke.
    </div>
    <div style="display:flex; flex-direction:column; gap:10px;">
      <button type="button" class="btn btn-secondary" style="justify-content:flex-start; padding:14px;" onclick="doPromote('new')">
        <div>
          <div style="font-weight:700;">Gem som ny</div>
          <div style="font-size:12px; color:#65676b; font-weight:400;">Opret en ny biblioteks-dimension med dette navn.</div>
        </div>
      </button>
      <div id="update-existing-row" style="display:none;">
        <button type="button" class="btn btn-secondary" style="justify-content:flex-start; padding:14px; width:100%;" onclick="doPromote('update')">
          <div>
            <div style="font-weight:700;">Opdater eksisterende</div>
            <div style="font-size:12px; color:#65676b; font-weight:400;" id="update-existing-target">—</div>
          </div>
        </button>
      </div>
    </div>
    <div style="display:flex; justify-content:flex-end; margin-top:16px;">
      <button type="button" class="btn btn-secondary" onclick="closePromote()">Annuller</button>
    </div>
  </div>
</div>

<script>
const BP_URL = @json(url('/simulation/admin/blueprints/'.$blueprint->id));
const CSRF   = @json(csrf_token());
const LIBRARY = @json($library);

const state = {
  parameters: @json($blueprint->parameters ?? []),
  selectedDim: 0,
  selectedFacet: 0,
};

// Ensure each parameter has the expected shape
state.parameters = state.parameters.map(p => ({
  name: p.name ?? '',
  description: p.description ?? '',
  library_parameter_id: p.library_parameter_id ?? null,
  facets: Array.isArray(p.facets) && p.facets.length ? p.facets.map(f => ({ name: f.name ?? '', text: f.text ?? '' })) : [{ name: '', text: '' }, { name: '', text: '' }],
}));

function escapeHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function render() {
  renderList();
  renderEditor();
  renderHidden();
}

function renderList() {
  const wrap = document.getElementById('dim-list');
  if (!state.parameters.length) {
    wrap.innerHTML = '<div style="font-size:13px; color:#65676b; padding:8px; text-align:center;">Ingen dimensioner endnu.</div>';
    return;
  }
  wrap.innerHTML = state.parameters.map((p, i) => {
    const active = i === state.selectedDim;
    const fromLib = p.library_parameter_id ? '<i class="fa-solid fa-layer-group" style="font-size:10px; color:#65676b;" title="Fra biblioteket"></i>' : '';
    return `
      <div onclick="selectDim(${i})"
        style="display:flex; align-items:center; gap:6px; padding:9px 10px; border-radius:6px; cursor:pointer; ${active ? 'background:#e7f0ff; color:#1877f2;' : 'color:#1c1e21;'}; font-size:14px; ${active ? 'font-weight:600;' : ''}">
        <span style="flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(p.name) || '<em style="color:#9ca3af;">unavngivet</em>'}</span>
        ${fromLib}
        <span style="font-size:11px; color:#65676b;">${(p.facets || []).length}</span>
      </div>
    `;
  }).join('');
}

function renderEditor() {
  const wrap = document.getElementById('dim-editor');
  if (!state.parameters.length) {
    wrap.innerHTML = `
      <div style="text-align:center; padding:60px 20px; color:#65676b;">
        <i class="fa-solid fa-arrow-left" style="font-size:36px; opacity:.3; display:block; margin-bottom:14px;"></i>
        <p>Tilføj en dimension for at komme i gang.</p>
      </div>`;
    return;
  }
  const i = state.selectedDim;
  const p = state.parameters[i];
  if (!p) return;
  if (state.selectedFacet >= (p.facets || []).length) state.selectedFacet = 0;
  const f = p.facets[state.selectedFacet];

  const facetTabs = (p.facets || []).map((fc, fi) => {
    const active = fi === state.selectedFacet;
    return `
      <div onclick="selectFacet(${fi})"
        style="padding:7px 14px; border-radius:6px 6px 0 0; cursor:pointer; font-size:13px; ${active ? 'background:#fff; border:1px solid #dadde1; border-bottom:1px solid #fff; font-weight:600; color:#1c1e21; position:relative; top:1px;' : 'background:#f0f2f5; color:#65676b; border:1px solid transparent;'}">
        ${escapeHtml(fc.name) || '<em style="color:#9ca3af;">facet</em>'}
      </div>`;
  }).join('');

  wrap.innerHTML = `
    <div style="display:flex; gap:10px; align-items:flex-start; margin-bottom:14px;">
      <div style="flex:1; min-width:0;">
        <input type="text" value="${escapeHtml(p.name)}" placeholder="Dimensionens navn (fx empati)"
          oninput="updateDim('name', this.value)"
          style="display:block; width:100%; font-size:18px; font-weight:700; padding:6px 8px; border:1px solid #dadde1; border-radius:6px; font-family:inherit; margin-bottom:6px;">
        <input type="text" value="${escapeHtml(p.description ?? '')}" placeholder="Kort beskrivelse"
          oninput="updateDim('description', this.value)"
          style="display:block; width:100%; font-size:13px; color:#65676b; padding:6px 8px; border:1px solid #dadde1; border-radius:6px; font-family:inherit;">
      </div>
      <div style="display:flex; gap:6px; flex-shrink:0;">
        <button type="button" class="btn btn-secondary" style="font-size:12px;" onclick="openPromote()">
          <i class="fa-solid fa-arrow-up"></i> Til bibliotek
        </button>
        <button type="button" class="btn btn-secondary" style="font-size:12px; color:#b91c1c;" onclick="removeDim()">
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>
    </div>

    <div style="display:flex; gap:2px; align-items:flex-end; border-bottom:1px solid #dadde1; margin-bottom:0; flex-wrap:wrap;">
      ${facetTabs}
      <div onclick="addFacet()" style="padding:7px 12px; cursor:pointer; font-size:13px; color:#1877f2; font-weight:600;">
        <i class="fa-solid fa-plus"></i> Facet
      </div>
    </div>

    <div style="background:#fff; padding:14px 0 0;">
      <div style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
        <input type="text" value="${escapeHtml(f.name)}" placeholder="Facet-navn (fx lav, anekdotisk)"
          oninput="updateFacet('name', this.value)"
          style="flex:1; font-size:14px; font-weight:600; padding:8px 10px; border:1px solid #dadde1; border-radius:6px; font-family:inherit;">
        <button type="button" class="btn btn-secondary" style="font-size:12px; color:#b91c1c;" onclick="removeFacet()" ${(p.facets || []).length <= 2 ? 'disabled title="Minimum 2 facetter"' : ''}>
          <i class="fa-solid fa-trash"></i> Slet facet
        </button>
      </div>
      <textarea oninput="updateFacet('text', this.value)" rows="14" placeholder="Håndskreven psykologi/kommunikations-konsekvens-tekst for denne facet..."
        style="width:100%; padding:12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit; resize:vertical; line-height:1.5;">${escapeHtml(f.text)}</textarea>
    </div>
  `;
}

function renderHidden() {
  const h = document.getElementById('bp-hidden');
  let html = '';
  state.parameters.forEach((p, i) => {
    html += `<input type="hidden" name="parameters[${i}][name]" value="${escapeHtml(p.name)}">`;
    html += `<input type="hidden" name="parameters[${i}][description]" value="${escapeHtml(p.description ?? '')}">`;
    if (p.library_parameter_id) {
      html += `<input type="hidden" name="parameters[${i}][library_parameter_id]" value="${p.library_parameter_id}">`;
    }
    (p.facets || []).forEach((f, j) => {
      html += `<input type="hidden" name="parameters[${i}][facets][${j}][name]" value="${escapeHtml(f.name)}">`;
      html += `<input type="hidden" name="parameters[${i}][facets][${j}][text]" value="${escapeHtml(f.text)}">`;
    });
  });
  h.innerHTML = html;
}

function selectDim(i) { state.selectedDim = i; state.selectedFacet = 0; render(); }
function selectFacet(i) { state.selectedFacet = i; render(); }

function updateDim(field, value) {
  state.parameters[state.selectedDim][field] = value;
  state.parameters[state.selectedDim].library_parameter_id = state.parameters[state.selectedDim].library_parameter_id; // keep
  // Update list label live without rerendering editor (preserves cursor)
  if (field === 'name') renderList();
  renderHidden();
}
function updateFacet(field, value) {
  const p = state.parameters[state.selectedDim];
  p.facets[state.selectedFacet][field] = value;
  if (field === 'name') {
    // Update tab label live
    const tabs = document.querySelectorAll('#dim-editor > div:nth-child(2) > div');
    if (tabs[state.selectedFacet]) {
      tabs[state.selectedFacet].innerHTML = value || '<em style="color:#9ca3af;">facet</em>';
    }
    renderList();
  }
  renderHidden();
}

function addNewDimension() {
  state.parameters.push({
    name: '', description: '', library_parameter_id: null,
    facets: [{ name: '', text: '' }, { name: '', text: '' }],
  });
  state.selectedDim = state.parameters.length - 1;
  state.selectedFacet = 0;
  render();
}

function removeDim() {
  if (!confirm('Fjern denne dimension fra strukturen?')) return;
  state.parameters.splice(state.selectedDim, 1);
  if (state.selectedDim >= state.parameters.length) state.selectedDim = Math.max(0, state.parameters.length - 1);
  state.selectedFacet = 0;
  render();
}

function addFacet() {
  state.parameters[state.selectedDim].facets.push({ name: '', text: '' });
  state.selectedFacet = state.parameters[state.selectedDim].facets.length - 1;
  render();
}

function removeFacet() {
  const p = state.parameters[state.selectedDim];
  if (p.facets.length <= 2) return;
  p.facets.splice(state.selectedFacet, 1);
  if (state.selectedFacet >= p.facets.length) state.selectedFacet = p.facets.length - 1;
  render();
}

// --- Picker ---
function openPicker() {
  document.getElementById('picker').style.display = 'flex';
  document.getElementById('picker-search').value = '';
  renderPicker('');
  setTimeout(() => document.getElementById('picker-search').focus(), 50);
}
function closePicker() { document.getElementById('picker').style.display = 'none'; }
function renderPicker(query) {
  const list = document.getElementById('picker-list');
  const q = (query || '').toLowerCase();
  const items = LIBRARY.filter(p =>
    !q || (p.name || '').toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q)
  );
  if (!items.length) {
    list.innerHTML = '<div style="text-align:center; padding:30px; color:#65676b; font-size:13px;">Ingen dimensioner i biblioteket matcher.</div>';
    return;
  }
  list.innerHTML = items.map(p => `
    <div onclick="insertFromLibrary(${p.id})" style="padding:10px 12px; border:1px solid #dadde1; border-radius:6px; margin-bottom:6px; cursor:pointer; background:#fff;"
         onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'">
      <div style="font-weight:700; font-size:14px;">${escapeHtml(p.name)}</div>
      ${p.description ? `<div style="font-size:12px; color:#65676b; margin-top:2px;">${escapeHtml(p.description)}</div>` : ''}
      <div style="font-size:11px; color:#65676b; margin-top:4px;">${(p.facets || []).length} facetter</div>
    </div>
  `).join('');
}
document.getElementById('picker-search').addEventListener('input', e => renderPicker(e.target.value));

function insertFromLibrary(id) {
  const lib = LIBRARY.find(p => p.id === id);
  if (!lib) return;
  state.parameters.push({
    name: lib.name,
    description: lib.description ?? '',
    library_parameter_id: lib.id,
    facets: (lib.facets || []).map(f => ({ name: f.name ?? '', text: f.text ?? '' })),
  });
  state.selectedDim = state.parameters.length - 1;
  state.selectedFacet = 0;
  closePicker();
  render();
}

// --- Promote ---
function openPromote() {
  const p = state.parameters[state.selectedDim];
  if (!p || !p.name.trim()) { alert('Giv dimensionen et navn først.'); return; }
  document.getElementById('promote-modal').style.display = 'flex';
  const target = LIBRARY.find(l => l.id === p.library_parameter_id) || LIBRARY.find(l => l.name === p.name);
  const row = document.getElementById('update-existing-row');
  if (target) {
    row.style.display = 'block';
    document.getElementById('update-existing-target').textContent = `Overskriv "${target.name}" i biblioteket.`;
    row.dataset.targetId = target.id;
  } else {
    row.style.display = 'none';
  }
}
function closePromote() { document.getElementById('promote-modal').style.display = 'none'; }

async function doPromote(mode) {
  const targetId = document.getElementById('update-existing-row').dataset.targetId;
  const body = {
    parameter_index: state.selectedDim,
    mode,
    target_id: mode === 'update' ? Number(targetId) : null,
  };
  try {
    // Save the current blueprint first so the server has the latest dimension content.
    await fetch(BP_URL, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: buildFormData('PATCH'),
    });
    const r = await fetch(BP_URL + '/promote', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await r.json();
    if (!r.ok || !data.ok) {
      alert(data.error || 'Kunne ikke gemme til biblioteket.');
      return;
    }
    state.parameters[state.selectedDim].library_parameter_id = data.library_parameter_id;
    closePromote();
    alert(mode === 'new' ? `"${data.name}" tilføjet til biblioteket.` : `"${data.name}" opdateret i biblioteket.`);
    render();
  } catch (e) {
    alert('Netværksfejl.');
  }
}

function buildFormData(method) {
  const fd = new FormData(document.getElementById('bp-form'));
  fd.append('_method', method);
  return fd;
}

render();
</script>

@endsection
