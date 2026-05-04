@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Opsætning @if ($course)<span style="font-weight: 400; color: #65676b; font-size: 14px;">· {{ $course->name }}</span>@endif</h1>
</div>

@include('admin._opsaetning_tabs')

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

@if (!$course)
  <div class="alert alert-error">Du har intet aktivt kursus.</div>
@else

@php $mode = $course->context_mode ?? 'auto'; @endphp

<style>
.ak-card { background:#fff; border:1px solid #dadde1; border-radius:10px; padding:18px 20px; margin-bottom:14px; }
.ak-card h3 { margin:0 0 4px; font-size:15px; }
.ak-sub { color:#65676b; font-size:13px; line-height:1.5; }
.ak-mode-tabs { display:inline-flex; gap:2px; background:#f0f2f5; border-radius:8px; padding:3px; margin-bottom:14px; }
.ak-mode-tabs button { background:none; border:none; padding:7px 16px; border-radius:6px; font-size:13px; font-weight:600; color:#65676b; cursor:pointer; font-family:inherit; }
.ak-mode-tabs button.active { background:#fff; color:#1c1e21; box-shadow:0 1px 2px rgba(0,0,0,0.06); }
.ak-row { display:flex; gap:10px; align-items:center; justify-content:space-between; margin-bottom:12px; }
.ak-pills { display:flex; gap:6px; flex-wrap:wrap; font-size:11px; }
.ak-pill { display:inline-block; padding:2px 8px; border-radius:9px; background:#f0f2f5; color:#65676b; font-weight:600; font-family:monospace; }
.ak-pill.active { background:#dbeafe; color:#1e40af; }
.ak-bullet-block { background:#f7f8fa; border-radius:8px; padding:10px 14px; margin-bottom:8px; }
.ak-bullet-block h4 { font-size:11px; font-weight:700; color:#65676b; text-transform:uppercase; letter-spacing:0.4px; margin:0 0 4px; }
.ak-bullet-block ul { margin:0 0 0 18px; font-size:14px; line-height:1.55; }
.ak-bullet-block.warm { background:#fef9e7; }
.ak-bullet-block.warm h4 { color:#92400e; }
.ak-prompt-textarea { width:100%; border:1px solid #dadde1; border-radius:6px; padding:12px 14px; font-family:'SFMono-Regular',Consolas,'Liberation Mono',monospace; font-size:12px; line-height:1.55; resize:vertical; min-height:300px; box-sizing:border-box; }
.ak-prompt-textarea:focus { outline:none; border-color:#1877f2; box-shadow:0 0 0 2px #e7f3ff; }
.ak-headlines summary { cursor:pointer; font-weight:600; font-size:13px; color:#65676b; padding:4px 0; }
.ak-headlines .h { padding:6px 0; border-bottom:1px solid #f0f2f5; font-size:13px; }
.ak-headlines .h:last-child { border:none; }
.ak-headlines .meta { color:#65676b; font-size:11px; text-transform:uppercase; }
.ak-tip { color:#65676b; font-size:12px; margin-top:8px; line-height:1.5; }
</style>

<div class="ak-card">
  <h3>Hvilken kontekst skal personas reagere ud fra?</h3>
  <div class="ak-sub">Vælg dette kursus' samtid. Indsættes som <code style="background:#f0f2f5; padding:1px 5px; border-radius:3px;">@{{current_context}}</code> i alle persona-prompts.</div>
  <form method="POST" action="{{ url('/simulation/admin/context/mode') }}" id="mode-form" style="margin-top:12px;">
    @csrf
    <div class="ak-mode-tabs">
      <button type="submit" name="context_mode" value="auto"   class="{{ $mode === 'auto'   ? 'active' : '' }}"><i class="fa-solid fa-rss"></i> Automatisk nyheds-digest</button>
      <button type="submit" name="context_mode" value="manual" class="{{ $mode === 'manual' ? 'active' : '' }}"><i class="fa-solid fa-pen-to-square"></i> Manuel fri tekst</button>
    </div>
  </form>
</div>

@if ($mode === 'auto')

<style>
.ak-subtabs { display:flex; gap:2px; border-bottom:1px solid #dadde1; margin-bottom:14px; }
.ak-subtabs button { background:none; border:none; padding:10px 18px; border-bottom:3px solid transparent; color:#65676b; font-weight:600; font-size:14px; cursor:pointer; font-family:inherit; }
.ak-subtabs button.active { color:#1877f2; border-bottom-color:#1877f2; }
.ak-pane { display:none; }
.ak-pane.active { display:block; }
.ak-headlines .h { padding:6px 0; border-bottom:1px solid #f0f2f5; font-size:13px; }
.ak-headlines .h:last-child { border:none; }
.ak-headlines .meta { color:#65676b; font-size:11px; text-transform:uppercase; }
</style>

<div class="ak-subtabs">
  <button type="button" class="active" data-pane="opsamling">Aktuel opsamling</button>
  <button type="button" data-pane="raw">Rå overskrifter <span style="color:#9ca3af; font-weight:400;">· {{ $totalHeadlines }}</span></button>
  <button type="button" data-pane="prompt">Prompt</button>
</div>

<div class="ak-pane active" data-pane="opsamling">
  <div class="ak-card">
    <div class="ak-row">
      <div>
        <h3>Aktuel opsamling</h3>
        <div class="ak-sub">Distilleret fra DR · TV2 · Politiken (sidste 14 dage).</div>
      </div>
      <form method="POST" action="{{ url('/simulation/admin/context/build') }}">
        @csrf
        <button class="btn btn-primary"><i class="fa-solid fa-rotate"></i> Lav ny opsamling</button>
      </form>
    </div>

    @if (!$digest)
      <div class="ak-sub" style="padding:8px 0;">Ingen opsamling endnu — klik "Lav ny opsamling".</div>
    @else
      @php
      $ongoing = \App\Services\News\NewsDigester::toArray($digest['ongoing_situations'] ?? []);
      $breaking = \App\Services\News\NewsDigester::toArray($digest['recent_breaking'] ?? []);
      @endphp
      <div class="ak-sub" style="margin-bottom:10px;">Genereret {{ \Carbon\Carbon::parse($digest['generated_at'])->diffForHumans() }} fra {{ $digest['headline_count'] ?? 0 }} overskrifter.</div>

      @if (!empty($ongoing))
        <div class="ak-bullet-block">
          <h4>Igangværende</h4>
          <ul>@foreach ($ongoing as $s)<li>{{ $s }}</li>@endforeach</ul>
        </div>
      @endif
      @if (!empty($breaking))
        <div class="ak-bullet-block warm">
          <h4>Senest (1-2 døgn)</h4>
          <ul>@foreach ($breaking as $s)<li>{{ $s }}</li>@endforeach</ul>
        </div>
      @endif
    @endif
  </div>
</div>

<div class="ak-pane ak-headlines" data-pane="raw">
  <div class="ak-card">
    <div class="ak-row" style="margin-bottom:8px;">
      <div>
        <h3>Rå overskrifter</h3>
        <div class="ak-sub">Seneste {{ $headlines->count() }} af {{ $totalHeadlines }} i buffer.</div>
      </div>
    </div>
    @forelse ($headlines as $h)
      <div class="h">
        <span class="meta">{{ $h->source }} · {{ $h->published_at?->format('d/m H:i') }}</span>
        <div><a href="{{ $h->url }}" target="_blank" style="color:#1c1e21;">{{ $h->title }}</a></div>
      </div>
    @empty
      <div class="ak-sub" style="padding:8px 0;">Ingen overskrifter endnu — overskrifter hentes løbende.</div>
    @endforelse
  </div>
</div>

<div class="ak-pane" data-pane="prompt">
  <div class="ak-card">
    <div class="ak-row" style="margin-bottom:6px;">
      <div>
        <h3>Sådan distilleres nyhederne</h3>
        <div class="ak-sub">Promptet der omdanner overskrifter til opsamlingen. Gælder hele systemet.</div>
      </div>
      <div style="display:flex; gap:8px; align-items:center;">
        @if ($newsOverridden)
          <span style="font-size:11px; padding:3px 8px; border-radius:10px; background:#dbeafe; color:#1e40af; font-weight:600;">Egen</span>
        @else
          <span style="font-size:11px; padding:3px 8px; border-radius:10px; background:#f0f2f5; color:#65676b; font-weight:600;">Standard</span>
        @endif
        @if ($newsOverridden)
          <form method="POST" action="{{ url('/simulation/admin/context/news-prompt/reset') }}" onsubmit="return confirm('Nulstil prompt til standard?')">
            @csrf
            <button type="submit" style="background:none; border:1px solid #dadde1; border-radius:6px; padding:5px 10px; font-size:12px; cursor:pointer; color:#65676b;"><i class="fa-solid fa-rotate-left"></i> Standard</button>
          </form>
        @endif
      </div>
    </div>
    @if (!empty($newsPlaceholders))
      <div class="ak-pills" style="margin-bottom:8px;">
        @foreach ($newsPlaceholders as $ph)
          <span class="ak-pill">@{{{{ $ph }}}}</span>
        @endforeach
      </div>
    @endif
    <form method="POST" action="{{ url('/simulation/admin/context/news-prompt') }}">
      @csrf
      <textarea name="body" class="ak-prompt-textarea">{{ $newsBody }}</textarea>
      <div class="ak-tip">Når du gemmer kører den nye prompt på næste "Lav ny opsamling".</div>
      <div style="display:flex; justify-content:flex-end; margin-top:10px;">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem prompt</button>
      </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('.ak-subtabs button[data-pane]').forEach(btn => {
  btn.addEventListener('click', () => {
    const key = btn.dataset.pane;
    document.querySelectorAll('.ak-subtabs button').forEach(b => b.classList.toggle('active', b === btn));
    document.querySelectorAll('.ak-pane').forEach(p => p.classList.toggle('active', p.dataset.pane === key));
  });
});
</script>

@else

<form method="POST" action="{{ url('/simulation/admin/context/manual') }}" class="ak-card">
  @csrf
  <h3>Manuel kontekst</h3>
  <div class="ak-sub" style="margin-bottom:10px;">Beskriv samtiden personerne skal reagere ud fra. Indsættes direkte i alle persona-prompts.</div>
  <textarea name="manual_context" class="ak-prompt-textarea" style="font-family:inherit; font-size:14px; min-height:260px;" placeholder="F.eks.:&#10;Det er efteråret 2026. Iran og Israel er i åben krig. Danmark har sendt F-35 jetfly...">{{ $course->manual_context }}</textarea>
  <div style="display:flex; justify-content:flex-end; margin-top:10px;">
    <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem</button>
  </div>
</form>

@endif

@endif
@endsection
