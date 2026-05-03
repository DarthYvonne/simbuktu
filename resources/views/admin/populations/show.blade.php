@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>
    <a href="{{ url('/slophub/admin/populations') }}" style="color: #1877f2;"><i class="fa-solid fa-arrow-left"></i></a>
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
  <form method="POST" action="{{ url('/slophub/admin/populations/'.$population->id) }}" class="card">
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

    <div style="margin-bottom: 16px; padding: 10px 12px; background: #f0f7ff; border: 1px solid #cfe2ff; border-radius: 6px; font-size: 13px; color: #1c1e21;">
      <i class="fa-solid fa-circle-info" style="color:#1877f2; margin-right:4px;"></i>
      Demografiske vægte (alder, køn, region, uddannelse osv.) konfigureres på fanen
      <a href="{{ url('/slophub/admin/populations/'.$population->id.'/demografi') }}" style="font-weight:600; color:#1877f2;">Demografi</a>.
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center;">
      <button type="button" class="btn btn-danger" style="font-size: 13px;"
        onclick="if(confirm('Slet populationen og alle dens personas?')) document.getElementById('deletePopForm').submit()">
        <i class="fa-solid fa-trash"></i> Slet population
      </button>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem</button>
    </div>
  </form>

  <form id="deletePopForm" method="POST" action="{{ url('/slophub/admin/populations/'.$population->id) }}" style="display:none;">
    @csrf @method('DELETE')
  </form>
</div>

@endsection
