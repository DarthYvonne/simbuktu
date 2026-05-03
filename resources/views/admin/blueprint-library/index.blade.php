@extends('layouts.app')
@section('content')

<div class="view-header">
  <div>
    <h1 style="margin-bottom: 3px;">Dimensions-bibliotek</h1>
    <div style="font-size: 13px; color: #65676b;">Genbrugelige parametre der kan indsættes i blueprints som snapshots.</div>
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('createModal').style.display='flex'">
    <i class="fa-solid fa-plus"></i> Ny dimension
  </button>
</div>

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($parameters->isEmpty())
  <div style="text-align: center; padding: 60px 20px; color: #65676b;">
    <i class="fa-solid fa-sliders" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: .3;"></i>
    <p>Ingen dimensioner i biblioteket endnu.</p>
  </div>
@else
  <div style="display: grid; gap: 8px; max-width: 860px;">
    @foreach ($parameters as $p)
      <a href="{{ url('/simulation/admin/blueprint-library/'.$p->id) }}"
         style="display:flex; align-items:center; gap:12px; background:#fff; border:1px solid #dadde1; border-radius:8px; padding:12px 16px; text-decoration:none; color:inherit;">
        <span style="font-weight:700; font-size:15px; color:#1c1e21;">{{ $p->name }}</span>
        @if ($p->description)
          <span style="color:#65676b; font-size:13px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; min-width:0;">{{ $p->description }}</span>
        @endif
        <span style="margin-left:auto; color:#65676b; font-size:12px;">{{ count($p->levels) }} niveauer</span>
      </a>
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
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Beskrivelse</label>
        <input type="text" name="description" placeholder="Kort forklaring til biblioteks-pickeren"
          style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
      </div>
      <div style="margin-bottom:14px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:6px;">Niveauer (mindst 2) *</label>
        <div id="levels"></div>
        <button type="button" class="btn btn-secondary" style="font-size:12px; margin-top:6px;" onclick="addLevel()">
          <i class="fa-solid fa-plus"></i> Tilføj niveau
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
let levelIdx = 0;
function addLevel(name = '', text = '') {
  const wrap = document.getElementById('levels');
  const i = levelIdx++;
  const div = document.createElement('div');
  div.style.cssText = 'border:1px solid #dadde1; border-radius:6px; padding:10px; margin-bottom:8px; background:#f8f9fa;';
  div.innerHTML = `
    <div style="display:flex; gap:8px; align-items:center; margin-bottom:6px;">
      <input type="text" name="levels[${i}][name]" required placeholder="Niveau-navn (fx lav)"
        style="flex:1; padding:6px 10px; border:1px solid #dadde1; border-radius:4px; font-size:13px; font-family:inherit;">
      <button type="button" onclick="this.closest('div').parentElement.remove()" style="background:none; border:none; color:#b91c1c; cursor:pointer; font-size:14px;">
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>
    <textarea name="levels[${i}][text]" required rows="3" placeholder="Håndskreven psykologi/kommunikations-konsekvens-tekst"
      style="width:100%; padding:8px 10px; border:1px solid #dadde1; border-radius:4px; font-size:13px; font-family:inherit; resize:vertical;"></textarea>
  `;
  wrap.appendChild(div);
  if (name) div.querySelector('input').value = name;
  if (text) div.querySelector('textarea').value = text;
}
addLevel();
addLevel();
</script>

@endsection
