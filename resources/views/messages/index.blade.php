@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Beskeder</h1>
</div>

<style>
.conv-list { background: #fff; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); overflow: hidden; }
.conv-item { display: flex; gap: 12px; align-items: center; padding: 12px 16px; border-bottom: 1px solid #f0f2f5; color: #1c1e21; cursor: pointer; }
.conv-item:last-child { border-bottom: none; }
.conv-item:hover { background: #f8f9fa; }
.conv-item img, .conv-item .ph { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.conv-item .ph { background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #65676b; }
.conv-item .info { flex: 1; min-width: 0; }
.conv-item .name { font-weight: 600; font-size: 14px; }
.conv-item .preview { color: #65676b; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-item .when { color: #65676b; font-size: 12px; flex-shrink: 0; }
.empty-state { background: #fff; border-radius: 12px; padding: 48px 24px; text-align: center; color: #65676b; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
.empty-state a { color: #1877f2; font-weight: 600; }
</style>

@if ($conversations->isEmpty())
  <div class="empty-state">
    <i class="fa-regular fa-message" style="font-size: 40px; color: #dadde1; margin-bottom: 12px; display: block;"></i>
    Du har ikke skrevet med nogen endnu.<br>
    <a href="{{ url('/slophub/profiler') }}">Find en persona</a> og klik på <em>Send besked</em>.
  </div>
@else
  <div class="conv-list">
    @foreach ($conversations as $c)
      @php $last = $previews[$c->id] ?? null; @endphp
      <a href="{{ url('/slophub/beskeder/' . $c->id) }}" class="conv-item">
        <img src="{{ url('/slophub/profiler/' . $c->persona_id . '/thumb') }}" alt="" onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'ph',textContent:'{{ strtoupper(substr($c->persona_name, 0, 2)) }}'}))">
        <div class="info">
          <div class="name">{{ $c->persona_name }}</div>
          <div class="preview">
            @if ($last)
              @if ($last->role === 'user') <span style="color:#1c1e21;">Dig:</span> @endif
              {{ \Illuminate\Support\Str::limit($last->body, 80) }}
            @else
              Ingen beskeder endnu
            @endif
          </div>
        </div>
        <div class="when">
          @if ($c->last_message_at)
            {{ $c->last_message_at->diffForHumans(null, true) }}
          @endif
        </div>
      </a>
    @endforeach
  </div>
@endif

@endsection
