@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>
    <a href="{{ url('/simulation/admin/populations') }}" style="color: #1877f2;"><i class="fa-solid fa-arrow-left"></i></a>
    {{ $population->name }}
  </h1>
</div>

@include('admin.populations._tabs', ['population' => $population])
@include('admin.populations._konfig_subtabs', ['population' => $population])

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div style="max-width: 760px;">
  <form method="POST" action="{{ url('/simulation/admin/populations/'.$population->id) }}" class="card">
    @csrf @method('PATCH')

    <div style="margin-bottom: 14px;">
      <label style="display: block; font-weight: 600; font-size: 13px; color: #65676b; margin-bottom: 4px;">Navn *</label>
      <input type="text" name="name" value="{{ old('name', $population->name) }}" required
        style="width: 100%; padding: 9px 12px; border: 1px solid #dadde1; border-radius: 6px; font-size: 14px; font-family: inherit;">
    </div>

    <div style="margin-bottom: 14px;">
      <label style="display: block; font-weight: 600; font-size: 13px; color: #65676b; margin-bottom: 4px;">Beskrivelse</label>
      <textarea name="description" rows="3" placeholder="Kort beskrivelse af populationen"
        style="width: 100%; padding: 9px 12px; border: 1px solid #dadde1; border-radius: 6px; font-size: 14px; font-family: inherit; resize: vertical;">{{ old('description', $population->description) }}</textarea>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center;">
      <button type="button" class="btn btn-danger" style="font-size: 13px;"
        onclick="if(confirm('Slet populationen og alle dens personas?')) document.getElementById('deletePopForm').submit()">
        <i class="fa-solid fa-trash"></i> Slet population
      </button>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem</button>
    </div>
  </form>

  <form id="deletePopForm" method="POST" action="{{ url('/simulation/admin/populations/'.$population->id) }}" style="display:none;">
    @csrf @method('DELETE')
  </form>

  @if ($course)
    <div class="card" style="margin-top: 14px;">
      <label style="display: block; font-weight: 600; font-size: 13px; color: #65676b; margin-bottom: 4px;">Personlighed</label>
      <div style="display: flex; gap: 8px; align-items: stretch;">
        <select id="bpSelect" onchange="onBpChange(this)" data-original="{{ $course->blueprint_id ?? '' }}"
          style="flex: 1; padding: 9px 12px; border: 1px solid #dadde1; border-radius: 6px; font-size: 14px; font-family: inherit;">
          <option value="" {{ $course->blueprint_id ? '' : 'selected' }}>Ingen</option>
          @foreach ($blueprints as $bp)
            <option value="{{ $bp->id }}" {{ $course->blueprint_id == $bp->id ? 'selected' : '' }}>{{ $bp->name }}</option>
          @endforeach
          <option value="__new__">+ Ny personlighed…</option>
        </select>
        @if ($course->blueprint_id)
          <a href="{{ url('/simulation/admin/blueprints/'.$course->blueprint_id) }}" class="btn btn-secondary" style="font-size: 13px; white-space: nowrap;">
            <i class="fa-solid fa-pen"></i> Redigér
          </a>
        @endif
      </div>
    </div>

    <form id="bpSetForm" method="POST" action="{{ url('/simulation/admin/courses/'.$course->id.'/blueprint') }}" style="display:none;">
      @csrf @method('PATCH')
      <input type="hidden" name="blueprint_id" id="bpSetId" value="">
    </form>
    <form id="bpNewForm" method="POST" action="{{ url('/simulation/admin/blueprints') }}" style="display:none;">
      @csrf
      <input type="hidden" name="name" id="bpNewName" value="">
    </form>
  @endif
</div>

<script>
// "+ Ny" is the only branch that needs to act immediately (it creates a blueprint
// and redirects). Picking an existing blueprint just stages the selection — it's
// committed when the user presses Gem.
function onBpChange(sel) {
  if (sel.value === '__new__') {
    const name = prompt('Navn på den nye personlighed:');
    if (!name || !name.trim()) { sel.value = sel.dataset.original; return; }
    document.getElementById('bpNewName').value = name.trim();
    document.getElementById('bpNewForm').submit();
  }
}

// Hook the population form's submit so the blueprint selection is saved alongside.
(function () {
  const popForm = document.querySelector('form.card[action$="populations/{{ $population->id }}"]');
  const sel     = document.getElementById('bpSelect');
  if (!popForm || !sel) return;

  popForm.addEventListener('submit', async (e) => {
    if (sel.value === '__new__' || sel.value === sel.dataset.original) return;
    e.preventDefault();
    const setForm = document.getElementById('bpSetForm');
    const fd = new FormData(setForm);
    fd.set('blueprint_id', sel.value);
    try {
      await fetch(setForm.action, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
    } catch (_) {}
    sel.dataset.original = sel.value;
    popForm.submit();
  });
})();
</script>

@endsection
