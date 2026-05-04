@extends('layouts.app')
@section('content')

<div class="view-header">
  <div>
    <h1 style="margin-bottom: 3px;">Dimensions-bibliotek</h1>
    <div style="font-size: 13px; color: #65676b;">Genbrugelige dimensioner der kan indsættes i blueprints som snapshots.</div>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('createModal').style.display='flex'">
    <i class="fa-solid fa-plus"></i> Ny dimension
  </button>
</div>

@include('admin._personligheder_tabs')

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@php
$catLabels = [
  'demografi'    => 'Demografi',
  'psykometri'   => 'Psykometri',
  'politik'      => 'Politik',
  'sprog_adfaerd'=> 'Sprog & adfærd',
  'subkultur'    => 'Subkultur',
  'egne'         => 'Egne dimensioner',
];
@endphp

@if ($parameters->isEmpty())
  <div style="text-align: center; padding: 60px 20px; color: #65676b;">
    <i class="fa-solid fa-sliders" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: .3;"></i>
    <p>Ingen dimensioner i biblioteket endnu.</p>
  </div>
@else
  <div style="max-width: 860px;">
    @foreach ($parameters as $catKey => $group)
      <div style="margin-bottom:18px;">
        <div style="font-size:11px; font-weight:700; color:#65676b; text-transform:uppercase; letter-spacing:.4px; padding:0 4px 6px;">
          {{ $catLabels[$catKey] ?? ucfirst($catKey) }}
          <span style="color:#9ca3af; font-weight:500;">· {{ $group->count() }}</span>
        </div>
        <div style="display:grid; gap:6px;">
          @foreach ($group as $p)
            <a href="{{ url('/simulation/admin/blueprint-library/'.$p->id) }}"
               style="display:flex; align-items:center; gap:12px; background:#fff; border:1px solid #dadde1; border-radius:8px; padding:10px 14px; text-decoration:none; color:inherit;">
              <span style="font-weight:600; font-size:14px; color:#1c1e21;">{{ $p->name }}</span>
              @if ($p->type === 'demographic')
                <span style="font-size:11px; padding:2px 7px; border-radius:10px; background:#e7f3ff; color:#1877f2; font-weight:600;">Demografi</span>
              @endif
              @if ($p->default_in_new_blueprints)
                <span style="font-size:11px; padding:2px 7px; border-radius:10px; background:#dcfce7; color:#166534; font-weight:600;" title="Indsættes som standard i nye blueprints"><i class="fa-solid fa-check"></i> Standard</span>
              @endif
              @if ($p->description)
                <span style="color:#65676b; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; min-width:0;">{{ $p->description }}</span>
              @endif
              <span style="margin-left:auto; color:#65676b; font-size:12px;">{{ count($p->facets) }} facetter</span>
            </a>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>
@endif

<div id="createModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:12px; padding:28px; width:100%; max-width:520px; box-shadow:0 8px 40px rgba(0,0,0,.2);">
    <h2 style="margin:0 0 18px; font-size:18px;">Ny dimension</h2>
    <form method="POST" action="{{ url('/simulation/admin/blueprint-library') }}">
      @csrf
      <div style="margin-bottom:14px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Navn *</label>
        <input type="text" name="name" required autofocus placeholder="fx empati, verbositet, partiskhed_dk"
          style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
      </div>
      <div style="margin-bottom:14px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Kategori</label>
        <select name="category" style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
          <option value="">Egne dimensioner</option>
          <option value="demografi">Demografi</option>
          <option value="psykometri">Psykometri</option>
          <option value="politik">Politik</option>
          <option value="sprog_adfaerd">Sprog & adfærd</option>
          <option value="subkultur">Subkultur</option>
        </select>
      </div>
      <div style="margin-bottom:14px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Beskrivelse</label>
        <input type="text" name="description" placeholder="Kort forklaring til biblioteks-pickeren"
          style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
      </div>
      <div style="margin-bottom:14px; display:flex; gap:12px; align-items:flex-end;">
        <div style="flex:1;">
          <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Type</label>
          <select name="type" style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
            <option value="personality">Personlighed</option>
            <option value="demographic">Demografi (facet-navn er værdien)</option>
          </select>
        </div>
        <label style="display:flex; align-items:center; gap:8px; padding:9px 10px; background:#f0f2f5; border-radius:6px; cursor:pointer; font-size:12px; user-select:none;">
          <input type="hidden" name="default_in_new_blueprints" value="0">
          <input type="checkbox" name="default_in_new_blueprints" value="1">
          <span>Medtag som standard</span>
        </label>
      </div>
      <div style="margin-bottom:14px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:6px;">Facetter (mindst 2) *</label>
        <div id="facets"></div>
        <button type="button" class="btn btn-secondary" style="font-size:12px; margin-top:6px;" onclick="addFacet()">
          <i class="fa-solid fa-plus"></i> Tilføj facet
        </button>
      </div>
      <div style="display:flex; gap:8px; justify-content:flex-end;">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('createModal').style.display='none'">Annuller</button>
        <button type="submit" class="btn btn-primary">Opret</button>
      </div>
    </form>
  </div>
</div>

<script>
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
        <input type="number" name="facets[${i}][weight]" min="0" max="100" step="1" value="${weight}"
          style="width:60px; padding:6px 8px; border:1px solid #dadde1; border-radius:4px; font-size:13px; font-family:inherit; text-align:right;">
        <span style="font-size:13px; color:#65676b;">%</span>
      </div>
      <button type="button" onclick="this.closest('div').parentElement.remove()" style="background:none; border:none; color:#b91c1c; cursor:pointer; font-size:14px;">
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>
    <textarea name="facets[${i}][text]" required rows="3" placeholder="Håndskreven psykologi/kommunikations-konsekvens-tekst"
      style="width:100%; padding:8px 10px; border:1px solid #dadde1; border-radius:4px; font-size:13px; font-family:inherit; resize:vertical;"></textarea>
  `;
  wrap.appendChild(div);
  if (name) div.querySelector('input[name$="[name]"]').value = name;
  if (text) div.querySelector('textarea').value = text;
}
addFacet();
addFacet();
</script>

@endsection
