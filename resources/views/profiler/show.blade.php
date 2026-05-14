@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1><a href="{{ url('/simulation/profiler') }}" style="color: #1877f2;"><i class="fa-solid fa-arrow-left"></i> Profiler</a></h1>
</div>

<style>
.profile-header { background: #fff; border-radius: 12px; overflow: hidden; margin-bottom: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
.profile-cover { height: 160px; background: linear-gradient(135deg, #a1c4fd, #c2e9fb); background-size: cover; background-position: center; position: relative; }
.profile-cover-credit { position: absolute; bottom: 4px; right: 8px; color: #fff; font-size: 10px; text-shadow: 0 1px 2px rgba(0,0,0,0.6); opacity: 0.85; }
.profile-cover-credit a { color: #fff; text-decoration: underline; }
.profile-body { padding: 0 24px 18px; margin-top: -30px; position: relative; }
.profile-avatar { width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 5px solid #fff; background: #fff; display: block; }
.profile-avatar-placeholder { width: 140px; height: 140px; border-radius: 50%; border: 5px solid #fff; background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-size: 42px; font-weight: 700; color: #65676b; }
.profile-name { margin-top: 10px; font-size: 28px; font-weight: 800; letter-spacing: -0.3px; }
.profile-meta { color: #65676b; font-size: 14px; margin-top: 2px; }
.profile-bio { font-style: italic; color: #1c1e21; margin-top: 10px; font-size: 15px; }
.narrative-box { background: #fef9e7; padding: 12px 14px; border-radius: 6px; font-size: 14px; line-height: 1.55; margin-top: 14px; }
.profile-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 14px; }
@media (max-width: 900px) { .profile-grid { grid-template-columns: 1fr; } }
.panel { background: #fff; border-radius: 12px; padding: 16px 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
.panel h3 { font-size: 12px; color: #65676b; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 10px; }
.attr-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f0f2f5; font-size: 13px; }
.attr-row:last-child { border-bottom: none; }
.attr-row span { color: #65676b; }
.attr-row strong { text-align: right; }
.tabs { display: flex; gap: 2px; border-bottom: 1px solid #dadde1; margin-bottom: 12px; }
.tabs button { padding: 10px 18px; border: none; background: transparent; border-bottom: 3px solid transparent; cursor: pointer; font-weight: 600; font-size: 14px; color: #65676b; }
.tabs button.active { color: #1877f2; border-bottom-color: #1877f2; }
.mini-post { background: #fff; border: 1px solid #e4e6eb; border-radius: 10px; padding: 12px 14px; margin-bottom: 10px; }
.mini-post-head { display: flex; gap: 10px; align-items: center; margin-bottom: 6px; }
.mini-post-head img { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; }
.mini-post-head .ph { width: 34px; height: 34px; border-radius: 50%; background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: #65676b; }
.mini-post-head .mn { font-weight: 600; font-size: 13px; }
.mini-post-head .mt { color: #65676b; font-size: 11px; }
.mini-post-body { font-size: 14px; line-height: 1.45; color: #1c1e21; white-space: pre-wrap; }
.friend-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; }
.friend-item { display: flex; gap: 8px; align-items: center; padding: 6px 8px; background: #f8f9fa; border-radius: 8px; text-decoration: none; color: #1c1e21; }
.friend-item:hover { background: #e7f3ff; }
.profile-id-row { display: flex; gap: 18px; align-items: flex-end; }
.profile-id-text { flex: 1; padding-bottom: 10px; }
.profile-id-action { padding-bottom: 10px; }
@media (max-width: 600px) {
  .profile-cover { height: 110px; }
  .profile-body { padding: 0 16px 16px; margin-top: -50px; }
  .profile-id-row { flex-direction: column; align-items: center; gap: 10px; text-align: center; }
  .profile-id-text { padding-bottom: 0; }
  .profile-id-action { padding-bottom: 0; width: 100%; }
  .profile-id-action .btn { width: 100%; }
  .profile-avatar, .profile-avatar-placeholder { width: 120px; height: 120px; }
  .profile-avatar-placeholder { font-size: 36px; }
  .profile-name { font-size: 24px; margin-top: 4px; }
}
</style>

<div class="profile-header">
  <div class="profile-cover" @if (!empty($cover)) style="background-image: url('{{ $cover['url'] }}&w=2400&h=640&fit=crop&crop=center&q=85');" @endif>
    @if (!empty($cover))
      <div class="profile-cover-credit">Photo: <a href="{{ $cover['author_url'] }}?utm_source=slophub&utm_medium=referral" target="_blank" rel="noopener">{{ $cover['author'] }}</a> / Unsplash</div>
    @endif
  </div>
  <div class="profile-body">
    <div class="profile-id-row">
      @if (!empty($p['image_file']))
        <img class="profile-avatar" src="{{ url('/simulation/profiler/'.$p['id'].'/image') }}">
      @else
        <div class="profile-avatar-placeholder">{{ strtoupper(substr($p['name'] ?? '?', 0, 2)) }}</div>
      @endif
      <div class="profile-id-text">
        @php
          $_age = $p['demographics']['age'] ?? null;
          $_metaBits = array_filter([
            $p['demographics']['occupation_hint'] ?? null,
            $p['demographics']['region'] ?? null,
            $p['demographics']['family'] ?? null,
          ]);
        @endphp
        <div class="profile-name">{{ $p['name'] }}@if($_age), {{ $_age }}@endif</div>
        <div class="profile-meta">{{ implode(' · ', $_metaBits) }}</div>
        <div class="profile-bio">"{{ $p['bio'] }}"</div>
      </div>
      <div class="profile-id-action">
        <button type="button" class="btn btn-primary" onclick="openPersonaChat('{{ $p['id'] }}', {{ json_encode($p['name']) }})">
          <i class="fa-regular fa-message"></i> Send besked
        </button>
      </div>
    </div>

    @php $shownDims = collect($p['dimensions'] ?? [])->filter(fn ($d) => !empty($d['show_on_profile'])); @endphp
    @if ($shownDims->isNotEmpty())
      <div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 6px;">
        @foreach ($shownDims as $d)
          <span style="font-size: 12px; padding: 3px 10px; border-radius: 12px; background: #e7f3ff; color: #1877f2; font-weight: 600;" title="{{ $d['dimension'] ?? '' }}">{{ $d['facet'] ?? '' }}</span>
        @endforeach
      </div>
    @endif

    <div class="narrative-box">
      <strong>Hvem er {{ explode(' ', $p['name'])[0] }}?</strong><br>
      {{ $p['narrative'] }}
    </div>

  </div>
</div>

<div class="profile-grid">
  <div class="panel">
    @php
      $dims = $p['dimensions'] ?? [];
      $demoDims = collect($dims)->filter(fn ($d) => ($d['type'] ?? 'personality') === 'demographic');
      $persDims = collect($dims)->filter(fn ($d) => ($d['type'] ?? 'personality') === 'personality');
    @endphp
    @if ($demoDims->isNotEmpty())
      <h3>Demografi</h3>
      @foreach ($demoDims as $d)
        <div class="attr-row"><span>{{ $d['dimension'] ?? '' }}</span><strong>{{ $d['facet'] ?? '—' }}</strong></div>
      @endforeach
    @endif

    <h3 style="margin-top: 18px;">Personlighed</h3>
    @forelse ($persDims as $d)
      <div class="attr-row"><span>{{ $d['dimension'] ?? '' }}</span><strong>{{ $d['facet'] ?? '' }}</strong></div>
    @empty
      <div style="font-size:12px; color:#65676b; padding: 6px 0;">Ingen personlighed tilknyttet.</div>
    @endforelse
  </div>

  <div class="panel">
    <div class="tabs">
      <button class="active" data-tab="opslag">Opslag</button>
      <button data-tab="venner">Venner ({{ count($friends ?? []) }})</button>
    </div>

    <div data-pane="opslag">
      @if (empty($p['older_posts']))
        <div style="color: #65676b; font-size: 13px; padding: 10px 0;">Ingen opslag.</div>
      @else
        @php $ages = ['2 dage siden','1 uge siden','3 uger siden','1 måned siden','2 måneder siden']; @endphp
        @foreach ($p['older_posts'] as $idx => $post)
          <div class="mini-post">
            <div class="mini-post-head">
              @if (!empty($p['image_file']))
                <img src="{{ url('/simulation/profiler/'.$p['id'].'/thumb') }}">
              @else
                <div class="ph">{{ strtoupper(substr($p['name'] ?? '?', 0, 2)) }}</div>
              @endif
              <div>
                <div class="mn">{{ $p['name'] }}</div>
                <div class="mt">{{ $ages[$idx] ?? 'flere måneder siden' }}</div>
              </div>
            </div>
            <div class="mini-post-body">{{ $post }}</div>
          </div>
        @endforeach
      @endif
    </div>

    <div data-pane="venner" style="display: none;">
      @if (empty($friends))
        <div style="color: #65676b; font-size: 13px; padding: 10px 0;">Ingen venner endnu.</div>
      @else
        <div class="friend-grid">
          @foreach ($friends as $f)
            <a class="friend-item" href="{{ url('/simulation/profiler/'.$f['id']) }}">
              @if (!empty($f['image_file']))
                <img src="{{ url('/simulation/profiler/'.$f['id'].'/thumb') }}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
              @else
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; flex-shrink: 0;">{{ strtoupper(substr($f['name'] ?? '?', 0, 2)) }}</div>
              @endif
              <div style="min-width: 0; flex: 1;">
                <div style="font-weight: 600; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $f['name'] }}</div>
                <div style="font-size: 11px; color: #65676b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">@php $_fAge = $f['demographics']['age'] ?? null; $_fOcc = $f['demographics']['occupation_hint'] ?? ''; @endphp{{ trim(($_fAge ? $_fAge.($_fOcc ? ', ' : '') : '').$_fOcc) }}</div>
              </div>
            </a>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.tabs button[data-tab]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tabs button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('[data-pane]').forEach(p => p.style.display = 'none');
    document.querySelector('[data-pane="'+btn.dataset.tab+'"]').style.display = 'block';
  });
});
</script>

@endsection
