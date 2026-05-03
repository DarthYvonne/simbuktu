@extends('layouts.app')
@section('content')

<div class="view-header">
  <div>
    <a href="{{ url('/simulation/admin/blueprint-library') }}" style="font-size:13px; color:#65676b; text-decoration:none;">
      <i class="fa-solid fa-arrow-left"></i> Bibliotek
    </a>
    <h1 style="margin:3px 0 0;">{{ $parameter->name }}</h1>
  </div>
  <form method="POST" action="{{ url('/simulation/admin/blueprint-library/'.$parameter->id) }}"
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

<form method="POST" action="{{ url('/simulation/admin/blueprint-library/'.$parameter->id) }}" style="max-width:860px;">
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
    <div>
      <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Beskrivelse</label>
      <input type="text" name="description" value="{{ old('description', $parameter->description) }}"
        placeholder="Kort forklaring til biblioteks-pickeren"
        style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
    </div>
  </div>

  <div style="background:#fff; border:1px solid #dadde1; border-radius:8px; padding:18px; margin-bottom:14px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
      <div style="font-weight:600; font-size:14px;">Facetter</div>
      <div style="display:flex; gap:8px; align-items:center;">
        <span id="weight-sum" style="font-size:12px; color:#65676b;"></span>
        <button type="button" class="btn btn-secondary" style="font-size:12px;" onclick="distributeEqually()">Fordel jævnt</button>
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

function addFacet(name = '', text = '', weight = 0) {
  const wrap = document.getElementById('facets');
  const i = facetIdx++;
  const div = document.createElement('div');
  div.style.cssText = 'border:1px solid #dadde1; border-radius:6px; padding:10px; margin-bottom:8px; background:#f8f9fa;';
  div.innerHTML = `
    <div style="display:flex; gap:8px; align-items:center; margin-bottom:6px;">
      <input type="text" name="facets[${i}][name]" required placeholder="Facet-navn (fx lav, anekdotisk)"
        style="flex:1; padding:6px 10px; border:1px solid #dadde1; border-radius:4px; font-size:13px; font-family:inherit;">
      <div style="display:flex; align-items:center; gap:4px;">
        <input type="number" name="facets[${i}][weight]" min="0" max="100" step="1" oninput="updateSum()"
          style="width:64px; padding:6px 8px; border:1px solid #dadde1; border-radius:4px; font-size:13px; font-family:inherit; text-align:right;">
        <span style="font-size:13px; color:#65676b;">%</span>
      </div>
      <button type="button" onclick="this.closest('div').parentElement.remove(); updateSum();" style="background:none; border:none; color:#b91c1c; cursor:pointer; font-size:14px;">
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>
    <textarea name="facets[${i}][text]" required rows="4" placeholder="Håndskreven tekst"
      style="width:100%; padding:8px 10px; border:1px solid #dadde1; border-radius:4px; font-size:13px; font-family:inherit; resize:vertical;"></textarea>
  `;
  wrap.appendChild(div);
  div.querySelector('input[name$="[name]"]').value = name;
  div.querySelector('input[type=number]').value = weight;
  div.querySelector('textarea').value = text;
  updateSum();
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

if (existing.length) {
  existing.forEach(f => addFacet(f.name ?? '', f.text ?? '', f.weight ?? 0));
} else {
  addFacet(); addFacet();
}
</script>

@endsection
