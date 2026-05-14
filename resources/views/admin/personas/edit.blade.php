@extends('layouts.app')
@section('content')

@php $base = '/simulation/admin/populations/'.$population->id; @endphp

<div class="view-header">
  <h1>
    <a href="{{ url("$base/personas/".$p['id']) }}" style="color: #1877f2;"><i class="fa-solid fa-arrow-left"></i> {{ $p['name'] }}</a>
  </h1>
</div>

<style>
.edit-grid { display: grid; grid-template-columns: 220px 1fr; gap: 14px; align-items: start; }
@media (max-width: 900px) { .edit-grid { grid-template-columns: 1fr; } }
.edit-card { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); margin-bottom: 14px; }
.edit-card h2 { font-size: 14px; color: #65676b; text-transform: uppercase; letter-spacing: 0.4px; margin: 0 0 12px; }
.field { margin-bottom: 14px; }
.field:last-child { margin-bottom: 0; }
.field label { display: block; font-size: 12px; font-weight: 600; color: #65676b; margin-bottom: 4px; }
.field input[type=text], .field input[type=number], .field textarea, .field select {
  width: 100%; padding: 8px 10px; border: 1px solid #dadde1; border-radius: 6px; font-size: 14px; font-family: inherit;
}
.field textarea { resize: vertical; min-height: 80px; }
.dim-row { display: grid; grid-template-columns: 1fr 1.4fr; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0f2f5; align-items: center; }
.dim-row:last-child { border-bottom: none; }
.dim-label { font-size: 13px; }
.dim-label strong { display: block; }
.dim-label .dtype { font-size: 11px; color: #65676b; }
.facet-text { font-size: 11px; color: #65676b; margin-top: 4px; line-height: 1.4; max-height: 40px; overflow: hidden; }
.actions { display: flex; gap: 8px; justify-content: flex-end; padding-top: 8px; }
.avatar-side img { width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 12px; display: block; }
.avatar-side .ph { width: 100%; aspect-ratio: 1/1; background: #e4e6eb; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 42px; font-weight: 700; color: #65676b; }
</style>

<form method="POST" action="{{ url("$base/personas/".$p['id']) }}">
  @csrf
  @method('PATCH')

  <div class="edit-grid">
    <div class="avatar-side">
      @if (!empty($p['image_file']))
        <img src="{{ url("$base/personas/".$p['id']."/image") }}">
      @else
        <div class="ph">{{ strtoupper(substr($p['name'] ?? '?', 0, 2)) }}</div>
      @endif
      <div style="font-size: 11px; color: #65676b; margin-top: 6px; text-align: center;">ID: {{ $p['id'] }}</div>
    </div>

    <div>
      <div class="edit-card">
        <h2>Identitet</h2>
        <div class="field">
          <label>Navn</label>
          <input type="text" name="name" value="{{ old('name', $p['name'] ?? '') }}" required>
        </div>
        <div class="field">
          <label>Bio (kort 1-linjers tagline)</label>
          <input type="text" name="bio" value="{{ old('bio', $p['bio'] ?? '') }}">
        </div>
        <div style="display: grid; grid-template-columns: 120px 1fr; gap: 12px;">
          <div class="field">
            <label>Alder</label>
            <input type="number" name="age" value="{{ old('age', $personaModel->age ?? ($p['demographics']['age'] ?? '')) }}" min="1" max="120">
          </div>
          <div class="field">
            <label>Region</label>
            <input type="text" name="region" value="{{ old('region', $personaModel->region ?? ($p['demographics']['region'] ?? '')) }}">
          </div>
        </div>
      </div>

      <div class="edit-card">
        <h2>Narrativ</h2>
        <div class="field">
          <label>Beskrivelse af hvem personaen er</label>
          <textarea name="narrative" rows="6">{{ old('narrative', $p['narrative'] ?? '') }}</textarea>
        </div>
      </div>

      @if (!empty($p['dimensions']))
        <div class="edit-card">
          <h2>Dimensioner</h2>
          <div style="font-size: 12px; color: #65676b; margin-bottom: 10px; line-height: 1.5;">
            Skift hvilken facet personaen rammer på hver dimension. Den valgte facets tekst går direkte i kommentar-prompten.
          </div>
          @foreach ($p['dimensions'] as $dim)
            @php
              $param = $paramsById[$dim['dimension_id'] ?? null] ?? null;
              $facets = $param['facets'] ?? [];
              $currentFacetId = $dim['facet_id'] ?? null;
              $currentText = collect($facets)->firstWhere('id', $currentFacetId)['text'] ?? ($dim['text'] ?? '');
            @endphp
            <div class="dim-row">
              <div class="dim-label">
                <strong>{{ $dim['dimension'] ?? ($param['name'] ?? '—') }}</strong>
                <span class="dtype">{{ $dim['type'] ?? 'personality' }}</span>
              </div>
              <div>
                @if (!empty($facets))
                  <select name="facets[{{ $dim['dimension_id'] }}]" data-dim="{{ $dim['dimension_id'] }}" class="facet-select">
                    @foreach ($facets as $f)
                      <option value="{{ $f['id'] ?? '' }}"
                        data-text="{{ $f['text'] ?? '' }}"
                        {{ ($f['id'] ?? null) === $currentFacetId ? 'selected' : '' }}>
                        {{ $f['name'] ?? '—' }}
                      </option>
                    @endforeach
                  </select>
                  <div class="facet-text" data-text-for="{{ $dim['dimension_id'] }}">{{ $currentText }}</div>
                @else
                  <em style="font-size: 12px; color: #65676b;">Ingen facetter tilgængelige</em>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @endif

      <div class="actions">
        <a href="{{ url("$base/personas/".$p['id']) }}" class="btn btn-secondary">Annullér</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Gem ændringer</button>
      </div>
    </div>
  </div>
</form>

<script>
document.querySelectorAll('.facet-select').forEach(sel => {
  sel.addEventListener('change', () => {
    const opt = sel.options[sel.selectedIndex];
    const text = opt?.dataset?.text || '';
    const target = document.querySelector(`[data-text-for="${sel.dataset.dim}"]`);
    if (target) target.textContent = text;
  });
});
</script>

@endsection
