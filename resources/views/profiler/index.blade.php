@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Profiler <span style="color: #65676b; font-weight: 400; font-size: 14px;">({{ $personas->count() }} / {{ $total }})</span></h1>
</div>

@include('profiler._tabs')

<div class="card">
  <form method="GET" action="{{ url('/simulation/profiler') }}">
    <input type="text" name="q" value="{{ $q }}" placeholder="Søg navn, bio, beskrivelse, dimensioner…" style="width: 100%; padding: 8px 12px; border: 1px solid #dadde1; border-radius: 6px; font-size: 14px;">
  </form>
  @if ($q)
    <div style="margin-top: 10px;">
      <a href="{{ url('/simulation/profiler') }}" style="color: #1877f2; font-size: 13px;">Nulstil søgning</a>
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
      'href'     => url('/simulation/profiler/'.$p['id']),
      'thumbUrl' => url('/simulation/profiler/'.$p['id'].'/thumb'),
    ])
  @endforeach
</div>
@endif
@endsection
