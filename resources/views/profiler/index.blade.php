@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Profiler <span style="color: #65676b; font-weight: 400; font-size: 14px;">({{ $personas->count() }} / {{ $total }})</span></h1>
</div>

@include('profiler._tabs')

<div class="card">
  <form method="GET" action="{{ url('/slophub/profiler') }}" class="filter-grid">
    <input type="text" name="q" value="{{ $q }}" placeholder="Søg navn, bio, job, subkultur..." style="padding: 8px 12px;">
    <select name="subculture" onchange="this.form.submit()">
      <option value="">Alle subkulturer</option>
      @foreach ($subcultures as $s)
        <option value="{{ $s }}" {{ $subculture === $s ? 'selected' : '' }}>{{ $s }}</option>
      @endforeach
    </select>
    <select name="party" onchange="this.form.submit()">
      <option value="">Alle partier</option>
      @foreach ($parties as $p)
        <option value="{{ $p }}" {{ $party === $p ? 'selected' : '' }}>{{ $p }}</option>
      @endforeach
    </select>
    <select name="region" onchange="this.form.submit()">
      <option value="">Alle regioner</option>
      @foreach ($regions as $r)
        <option value="{{ $r }}" {{ $region === $r ? 'selected' : '' }}>{{ $r }}</option>
      @endforeach
    </select>
    <select name="age" onchange="this.form.submit()">
      <option value="">Alle aldre</option>
      @foreach (['16-24','25-34','35-44','45-54','55-64','65-79','80-99'] as $b)
        <option value="{{ $b }}" {{ $age === $b ? 'selected' : '' }}>{{ $b }} år</option>
      @endforeach
    </select>
  </form>
  @if ($q || $subculture || $party || $region || $age)
    <div style="margin-top: 10px;">
      <a href="{{ url('/slophub/profiler') }}" style="color: #1877f2; font-size: 13px;">Nulstil filtre</a>
    </div>
  @endif
</div>

@if ($personas->isEmpty())
  <div class="card" style="text-align: center; padding: 40px; color: #65676b;">
    Ingen personas matcher dine filtre.
  </div>
@else
<div class="persona-grid">
  @foreach ($personas as $p)
    @include('partials._persona-card', [
      'href'     => url('/slophub/profiler/'.$p['id']),
      'thumbUrl' => url('/slophub/profiler/'.$p['id'].'/thumb'),
    ])
  @endforeach
</div>
@endif
@endsection
