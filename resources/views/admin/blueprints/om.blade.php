@extends('layouts.app')
@section('content')

@include('admin.blueprints._header')

@component('admin.blueprints._subtabs', ['blueprint' => $blueprint])
  <button type="submit" form="om-form" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem</button>
@endcomponent

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

<div style="max-width:760px;">
  <form id="om-form" method="POST" action="{{ url('/simulation/admin/blueprints/'.$blueprint->id.'/meta') }}" class="card">
    @csrf @method('PATCH')

    <div style="margin-bottom:14px;">
      <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Navn *</label>
      <input type="text" name="name" required value="{{ old('name', $blueprint->name) }}" placeholder="fx Klima-shitstorm"
        style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
    </div>

    <div style="margin-bottom:14px;">
      <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Beskrivelse</label>
      <textarea name="description" rows="3" placeholder="Kort beskrivelse af denne personlighed"
        style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit; resize:vertical;">{{ old('description', $blueprint->description) }}</textarea>
    </div>

    <div style="margin-top:18px; padding-top:14px; border-top:1px solid #f0f2f5;">
      <button type="button" class="btn btn-secondary" style="font-size:12px; color:#b91c1c;"
        onclick="if(confirm('Slet denne personlighed? Personas der er genereret med den påvirkes ikke.')) document.getElementById('delete-form').submit()">
        <i class="fa-solid fa-trash"></i> Slet personlighed
      </button>
    </div>
  </form>
</div>

<form id="delete-form" method="POST" action="{{ url('/simulation/admin/blueprints/'.$blueprint->id) }}" style="display:none;">
  @csrf @method('DELETE')
</form>

@endsection
