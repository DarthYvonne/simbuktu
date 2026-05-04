@extends('layouts.app')
@section('content')

@include('admin.blueprints._header')

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($blueprints->isEmpty())
  <div style="text-align: center; padding: 60px 20px; color: #65676b;">
    <i class="fa-solid fa-id-card" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: .3;"></i>
    <p>Ingen personligheder endnu.</p>
  </div>
@else
  <div style="display: grid; gap: 8px; max-width: 860px;">
    @foreach ($blueprints as $bp)
      @php $isCurrent = $course && $course->blueprint_id == $bp->id; @endphp
      <div style="display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid {{ $isCurrent ? '#1877f2' : '#dadde1' }}; border-radius: 8px; padding: 12px 16px; {{ $isCurrent ? 'background: #f5f9ff;' : '' }}">
        <a href="{{ url('/simulation/admin/blueprints/'.$bp->id) }}"
           style="flex: 1; min-width: 0; text-decoration: none; color: inherit; display: flex; align-items: baseline; gap: 10px;">
          <span style="font-weight: 700; font-size: 15px; color: #1c1e21; white-space: nowrap;">{{ $bp->name }}</span>
          @if ($bp->description)
            <span style="color: #65676b; font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0;">{{ $bp->description }}</span>
          @endif
          <span style="margin-left: auto; color: #65676b; font-size: 12px; white-space: nowrap; flex-shrink: 0;">{{ count($bp->parameters ?? []) }} dimensioner</span>
        </a>

        <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
          @if ($isCurrent)
            <span style="background: #dcfce7; color: #166534; font-size: 12px; font-weight: 700; padding: 3px 12px; border-radius: 20px; white-space: nowrap;">
              <i class="fa-solid fa-check"></i> Valgt
            </span>
          @elseif ($course)
            <form method="POST" action="{{ url('/simulation/admin/courses/'.$course->id.'/blueprint') }}">
              @csrf @method('PATCH')
              <input type="hidden" name="blueprint_id" value="{{ $bp->id }}">
              <button type="submit" class="btn btn-secondary" style="font-size: 12px;">Vælg</button>
            </form>
          @endif
        </div>
      </div>
    @endforeach
  </div>
@endif

@endsection
