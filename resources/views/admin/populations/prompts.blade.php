@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>
    <a href="{{ url('/slophub/admin/populations') }}" style="color:#1877f2;"><i class="fa-solid fa-arrow-left"></i></a>
    <span style="font-weight:400;color:#65676b;">Population:</span> {{ $population->name }}
  </h1>
</div>

@include('admin.populations._tabs', ['population' => $population])
@include('admin.populations._konfig_subtabs', ['population' => $population])

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@php $base = '/slophub/admin/populations/'.$population->id; @endphp

<div style="max-width: 900px;">
  <div class="card">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:14px; flex-wrap:wrap;">
      <div>
        <div style="font-weight:700; font-size:16px;">{{ $narrative['name'] }}</div>
        <div style="font-size:12px; font-family:monospace; color:#65676b; margin-top:2px;">persona.narrative</div>
        @if ($narrative['description'])
          <div style="font-size:13px; color:#65676b; margin-top:4px;">{{ $narrative['description'] }}</div>
        @endif
      </div>
      @if ($isOverridden)
        <span style="background:#fef3c7; color:#92400e; font-size:12px; font-weight:700; padding:3px 10px; border-radius:20px; white-space:nowrap; flex-shrink:0;">
          <i class="fa-solid fa-pen"></i> Tilpasset denne population
        </span>
      @else
        <span style="background:#f0f2f5; color:#65676b; font-size:12px; font-weight:600; padding:3px 10px; border-radius:20px; white-space:nowrap; flex-shrink:0;">
          Global standard
        </span>
      @endif
    </div>

    @if (!empty($narrative['placeholders']))
      <details style="margin-bottom:12px;">
        <summary style="cursor:pointer; color:#1877f2; font-size:13px; font-weight:600; user-select:none;">
          Tilgængelige placeholders ({{ count($narrative['placeholders']) }})
        </summary>
        <div style="margin-top:8px; display:flex; flex-wrap:wrap; gap:6px;">
          @foreach ($narrative['placeholders'] as $ph)
            <code class="ph-chip" data-ph="{{ $ph }}"
              style="background:#f0f2f5; padding:2px 8px; border-radius:4px; font-size:12px; cursor:pointer;"
              title="Klik for at indsætte">&#123;&#123;{{ $ph }}&#125;&#125;</code>
          @endforeach
        </div>
      </details>
    @endif

    <form method="POST" action="{{ url("$base/prompts") }}">
      @csrf @method('PATCH')
      <textarea name="body" id="promptBody"
        style="width:100%; min-height:520px; font-family:'Cascadia Code','Consolas',monospace; font-size:13px; padding:12px; border:1px solid #dadde1; border-radius:8px; resize:vertical; line-height:1.5;">{{ $current }}</textarea>
      <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
        @if ($isOverridden)
          <button type="button" class="btn btn-secondary" onclick="resetToDefault()">
            <i class="fa-solid fa-rotate-left"></i> Nulstil til global standard
          </button>
        @else
          <span style="font-size:13px; color:#65676b;">Redigér for at tilpasse denne population.</span>
        @endif
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Gem
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('.ph-chip').forEach(el => {
  el.addEventListener('click', () => {
    const ta = document.getElementById('promptBody');
    const token = '{' + '{' + el.dataset.ph + '}' + '}';
    const start = ta.selectionStart, end = ta.selectionEnd;
    ta.value = ta.value.slice(0, start) + token + ta.value.slice(end);
    ta.focus();
    ta.setSelectionRange(start + token.length, start + token.length);
  });
});

function resetToDefault() {
  if (!confirm('Nulstil til global standard for alle populationer?')) return;
  document.getElementById('promptBody').value = {{ json_encode($narrative['body']) }};
  document.querySelector('form').submit();
}
</script>

@endsection
