@extends('layouts.app')
@section('content')

<div class="view-header">
  <div>
    <a href="{{ url('/simulation/admin/personlighedskomponenter') }}" style="font-size:13px; color:#65676b; text-decoration:none;">
      <i class="fa-solid fa-arrow-left"></i> Personlighedskomponenter
    </a>
    <h1 style="margin:3px 0 0;">{{ $parameter->name }}</h1>
  </div>
  <form method="POST" action="{{ url('/simulation/admin/personlighedskomponenter/'.$parameter->id) }}"
        onsubmit="return confirm('Slet dimension {{ addslashes($parameter->name) }}? Eksisterende strukturer påvirkes ikke.')">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-secondary" style="color:#b91c1c;">
      <i class="fa-solid fa-trash"></i> Slet
    </button>
  </form>
</div>

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-error">
    @foreach ($errors->all() as $err)
      <div>{{ $err }}</div>
    @endforeach
  </div>
@endif

<form method="POST" action="{{ url('/simulation/admin/personlighedskomponenter/'.$parameter->id) }}" style="max-width:860px;">
  @csrf @method('PATCH')

  <div style="background:#fff; border:1px solid #dadde1; border-radius:8px; padding:18px; margin-bottom:14px;">
    <div style="margin-bottom:14px;">
      <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Navn *</label>
      <input type="text" name="name" required value="{{ old('name', $parameter->name) }}"
        style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
    </div>
    <div style="margin-bottom:14px;">
      <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Kategori</label>
      @php $cat = old('category', $parameter->category); @endphp
      <select name="category" style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
        <option value="" {{ $cat ? '' : 'selected' }}>Egne dimensioner</option>
        <option value="demografi" {{ $cat === 'demografi' ? 'selected' : '' }}>Demografi</option>
        <option value="psykometri" {{ $cat === 'psykometri' ? 'selected' : '' }}>Psykometri</option>
        <option value="politik" {{ $cat === 'politik' ? 'selected' : '' }}>Politik</option>
        <option value="sprog_adfaerd" {{ $cat === 'sprog_adfaerd' ? 'selected' : '' }}>Sprog & adfærd</option>
        <option value="subkultur" {{ $cat === 'subkultur' ? 'selected' : '' }}>Subkultur</option>
      </select>
    </div>
    <div style="margin-bottom:14px;">
      <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Beskrivelse</label>
      <input type="text" name="description" value="{{ old('description', $parameter->description) }}"
        placeholder="Kort forklaring til biblioteks-pickeren"
        style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
    </div>
    <div style="display:flex; gap:18px; align-items:flex-end;">
      <div style="flex:1;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Type</label>
        @php $type = old('type', $parameter->type ?? 'personality'); @endphp
        <select name="type" style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
          <option value="personality" {{ $type === 'personality' ? 'selected' : '' }}>Personlighed (facet-tekst farver LLM'ens stemme)</option>
          <option value="demographic" {{ $type === 'demographic' ? 'selected' : '' }}>Demografi (facet-navn er værdien — fx alder, region)</option>
        </select>
      </div>
      <label style="display:flex; align-items:center; gap:8px; padding:10px 12px; background:#f0f2f5; border-radius:6px; cursor:pointer; font-size:13px; user-select:none;">
        <input type="hidden" name="default_in_new_blueprints" value="0">
        <input type="checkbox" name="default_in_new_blueprints" value="1" {{ old('default_in_new_blueprints', $parameter->default_in_new_blueprints) ? 'checked' : '' }}>
        <span><strong>Medtag som standard</strong> i nye personligheder</span>
      </label>
    </div>
  </div>

  <div style="background:#fff; border:1px solid #dadde1; border-radius:8px; padding:18px; margin-bottom:14px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
      <div style="font-weight:600; font-size:14px;">Facetter</div>
      <div style="display:flex; gap:8px; align-items:center;">
        <span id="weight-sum" style="font-size:12px; color:#65676b;"></span>
        <button type="button" class="btn btn-secondary" style="font-size:12px;" onclick="distributeEqually()">Fordel jævnt</button>
        <button type="button" class="btn btn-secondary" style="font-size:12px;" onclick="distributeNormal()">Normalfordel</button>
        <button type="button" class="btn btn-secondary" style="font-size:12px;" onclick="addFacet()">
          <i class="fa-solid fa-plus"></i> Tilføj facet
        </button>
      </div>
    </div>
    <div id="facets"></div>
  </div>

  <div style="display:flex; justify-content:flex-end;">
    <button type="submit" class="btn btn-primary">Gem ændringer</button>
  </div>
</form>

<script>
const existing = @json($parameter->facets ?? []);
let facetIdx = 0;

function addFacet(name = '', text = '', weight = 0, id = '', value = '') {
  const wrap = document.getElementById('facets');
  const i = facetIdx++;
  const div = document.createElement('div');
  div.style.cssText = 'border:1px solid #dadde1; border-radius:6px; padding:10px; margin-bottom:8px; background:#f8f9fa;';
  div.innerHTML = `
    <input type="hidden" name="facets[${i}][id]" value="${id}">
    <div style="display:flex; gap:8px; align-items:center; margin-bottom:6px;">
      <input type="text" name="facets[${i}][name]" required placeholder="Facet-navn (fx lav, anekdotisk)"
        style="flex:1; padding:6px 10px; border:1px solid #dadde1; border-radius:4px; font-size:13px; font-family:inherit;">
      <input type="text" name="facets[${i}][value]" placeholder="Værdi (fx 16-24)" title="Demografi: 16-24 = uniform random int. 42 = literal. Tom = facet-navnet." class="facet-value-input"
        style="width:140px; padding:6px 8px; border:1px solid #dadde1; border-radius:4px; font-size:12px; font-family:inherit;">
      <div style="display:flex; align-items:center; gap:4px;">
        <input type="number" name="facets[${i}][weight]" min="0" max="100" step="1" oninput="updateSum()"
          style="width:64px; padding:6px 8px; border:1px solid #dadde1; border-radius:4px; font-size:13px; font-family:inherit; text-align:right;">
        <span style="font-size:13px; color:#65676b;">%</span>
      </div>
      <button type="button" onclick="this.closest('div').parentElement.remove(); updateSum();" style="background:none; border:none; color:#b91c1c; cursor:pointer; font-size:14px;">
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>
    <textarea name="facets[${i}][text]" rows="4" placeholder="Håndskreven tekst (valgfri på demografi-dimensioner)"
      style="width:100%; padding:8px 10px; border:1px solid #dadde1; border-radius:4px; font-size:13px; font-family:inherit; resize:vertical;"></textarea>
  `;
  wrap.appendChild(div);
  div.querySelector('input[name$="[name]"]').value = name;
  div.querySelector('input[name$="[value]"]').value = value || '';
  div.querySelector('input[type=number]').value = weight;
  div.querySelector('textarea').value = text;
  updateSum();
  updateValueVisibility();
}

function getWeights() {
  return Array.from(document.querySelectorAll('#facets input[type=number]'));
}

function updateSum() {
  const inputs = getWeights();
  const sum = inputs.reduce((s, el) => s + (parseInt(el.value) || 0), 0);
  const el = document.getElementById('weight-sum');
  if (sum > 100) {
    el.textContent = `Sum: ${sum}% — over grænsen`;
    el.style.color = '#b91c1c';
  } else if (sum === 100) {
    el.textContent = `Sum: 100%`;
    el.style.color = '#166534';
  } else {
    el.textContent = `Sum: ${sum}% — ${100 - sum}% får ingen tekst`;
    el.style.color = '#65676b';
  }
}

function distributeEqually() {
  const inputs = getWeights();
  if (!inputs.length) return;
  const base = Math.floor(100 / inputs.length);
  const remainder = 100 - base * inputs.length;
  inputs.forEach((el, i) => { el.value = base + (i < remainder ? 1 : 0); });
  updateSum();
}

function distributeNormal() {
  const inputs = getWeights();
  if (!inputs.length) return;
  const w = normalWeights(inputs.length);
  inputs.forEach((el, i) => { el.value = w[i]; });
  updateSum();
}

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
  const scaled = raw.map(v => v / sum * 100);
  const floored = scaled.map(v => Math.floor(v));
  let leftover = 100 - floored.reduce((a, b) => a + b, 0);
  const order = scaled
    .map((v, i) => ({ i, frac: v - Math.floor(v) }))
    .sort((a, b) => b.frac - a.frac);
  for (let k = 0; k < leftover; k++) floored[order[k].i] += 1;
  return floored;
}

function updateValueVisibility() {
  const isDemo = document.querySelector('select[name="type"]')?.value === 'demographic';
  document.querySelectorAll('.facet-value-input').forEach(el => {
    el.style.display = isDemo ? '' : 'none';
  });
}
document.querySelector('select[name="type"]')?.addEventListener('change', updateValueVisibility);

if (existing.length) {
  existing.forEach(f => addFacet(f.name ?? '', f.text ?? '', f.weight ?? 0, f.id ?? '', f.value ?? ''));
} else {
  addFacet(); addFacet();
}
updateValueVisibility();
</script>

@endsection
