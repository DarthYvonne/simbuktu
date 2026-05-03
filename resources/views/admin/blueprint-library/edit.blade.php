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
        onsubmit="return confirm('Slet dimension {{ addslashes($parameter->name) }}? Eksisterende blueprints påvirkes ikke.')">
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
    <div>
      <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Beskrivelse</label>
      <input type="text" name="description" value="{{ old('description', $parameter->description) }}"
        placeholder="Kort forklaring til biblioteks-pickeren"
        style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
    </div>
  </div>

  <div style="background:#fff; border:1px solid #dadde1; border-radius:8px; padding:18px; margin-bottom:14px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
      <div style="font-weight:600; font-size:14px;">Niveauer</div>
      <button type="button" class="btn btn-secondary" style="font-size:12px;" onclick="addLevel()">
        <i class="fa-solid fa-plus"></i> Tilføj niveau
      </button>
    </div>
    <div id="levels"></div>
  </div>

  <div style="display:flex; justify-content:flex-end;">
    <button type="submit" class="btn btn-primary">Gem ændringer</button>
  </div>
</form>

<script>
const existing = @json($parameter->levels ?? []);
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
    <textarea name="levels[${i}][text]" required rows="4" placeholder="Håndskreven tekst"
      style="width:100%; padding:8px 10px; border:1px solid #dadde1; border-radius:4px; font-size:13px; font-family:inherit; resize:vertical;"></textarea>
  `;
  wrap.appendChild(div);
  div.querySelector('input').value = name;
  div.querySelector('textarea').value = text;
}

if (existing.length) {
  existing.forEach(l => addLevel(l.name ?? '', l.text ?? ''));
} else {
  addLevel(); addLevel();
}
</script>

@endsection
