@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>System</h1>
</div>

@include('admin._opsaetning_tabs')

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
  <div class="alert alert-error">{{ session('error') }}</div>
@endif

<style>
.prompts-layout { display: grid; grid-template-columns: 260px 1fr; gap: 14px; }
@media (max-width: 767px) { .prompts-layout { grid-template-columns: 1fr; } }
.prompts-subtabs { display: inline-flex; gap: 2px; background: #f0f2f5; border-radius: 8px; padding: 3px; margin-bottom: 14px; }
.prompts-subtabs a { padding: 7px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; color: #65676b; }
.prompts-subtabs a.active { background: #fff; color: #1c1e21; box-shadow: 0 1px 2px rgba(0,0,0,0.06); }
.prompts-subhint { font-size: 12px; color: #65676b; line-height: 1.5; margin-bottom: 12px; padding: 0 4px; }
</style>

<div class="prompts-subtabs">
  <a href="{{ url('/simulation/admin/prompts?tab=globals') }}" class="{{ $tab === 'globals' ? 'active' : '' }}">Globale prompts</a>
  <a href="{{ url('/simulation/admin/prompts?tab=templates') }}" class="{{ $tab === 'templates' ? 'active' : '' }}">Promptskabeloner</a>
</div>
<div class="prompts-subhint">
  @if ($tab === 'templates')
    Skabeloner kopieres ind i en personlighed når den oprettes. Ændringer her påvirker <strong>kun nye personligheder</strong> — eksisterende beholder deres egne kopier.
  @else
    Globale prompts gælder hele platformen og bruges direkte ved hvert kald. Ændringer slår igennem med det samme overalt.
  @endif
</div>
<div class="prompts-layout">
  <div class="card" style="padding: 8px 0; height: fit-content;">
    @foreach ($prompts as $p)
      <a href="{{ url('/simulation/admin/prompts/'.$p->key) }}"
         style="display: block; padding: 10px 14px; border-left: 3px solid {{ $current && $current->id === $p->id ? '#1877f2' : 'transparent' }}; background: {{ $current && $current->id === $p->id ? '#e7f3ff' : 'transparent' }}; color: #1c1e21; text-decoration: none;">
        <div style="font-weight: 600; font-size: 14px;">{{ $p->name }}</div>
        @if ($p->description)
          <div style="font-size: 12px; color: #65676b; line-height: 1.4; margin-top: 2px;">{{ $p->description }}</div>
        @endif
      </a>
    @endforeach
  </div>

  <div>
    @if ($current)
      <div class="card">
        <h3 style="margin-bottom: 6px;">{{ $current->name }}</h3>
        <div style="font-size: 12px; color: #65676b; font-family: monospace; margin-bottom: 10px;">{{ $current->key }}</div>
        @if ($current->description)
          <div style="background: #fef9e7; padding: 10px 14px; border-radius: 6px; font-size: 13px; line-height: 1.5; margin-bottom: 12px;">
            {{ $current->description }}
          </div>
        @endif
        @if (!empty($current->placeholders))
          <details style="margin-bottom: 12px;">
            <summary style="cursor: pointer; color: #1877f2; font-size: 13px; font-weight: 600;">Tilgængelige placeholders ({{ count($current->placeholders) }})</summary>
            <div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;">
              @foreach ($current->placeholders as $ph)
                <code class="ph-chip" data-ph="{{ $ph }}" style="background: #f0f2f5; padding: 2px 8px; border-radius: 4px; font-size: 12px; cursor: pointer;">&#123;&#123;{{ $ph }}&#125;&#125;</code>
              @endforeach
            </div>
          </details>
        @endif

        <form method="POST" action="{{ url('/simulation/admin/prompts/'.$current->id) }}">
          @csrf
          @method('PATCH')
          <textarea name="body" id="promptBody" style="width: 100%; min-height: 500px; font-family: 'Cascadia Code', 'Consolas', monospace; font-size: 13px; padding: 12px; border: 1px solid #dadde1; border-radius: 8px; resize: vertical;">{{ $current->body }}</textarea>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
            <form method="POST" action="{{ url('/simulation/admin/prompts/'.$current->id.'/reset') }}" style="display: inline;" onsubmit="return confirm('Nulstil til standard?')">
              @csrf
              <button type="submit" class="btn btn-secondary">Nulstil til standard</button>
            </form>
            <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem prompt</button>
          </div>
        </form>
      </div>
    @else
      <div class="card" style="padding: 40px; text-align: center; color: #65676b;">Ingen prompts.</div>
    @endif
  </div>
</div>

<script>
document.querySelectorAll('.ph-chip').forEach(el => {
  el.addEventListener('click', () => {
    const ta = document.getElementById('promptBody');
    if (!ta) return;
    const token = '{' + '{' + el.dataset.ph + '}' + '}';
    const start = ta.selectionStart, end = ta.selectionEnd;
    ta.value = ta.value.slice(0, start) + token + ta.value.slice(end);
    ta.focus();
    ta.setSelectionRange(start + token.length, start + token.length);
  });
});
</script>
@endsection
