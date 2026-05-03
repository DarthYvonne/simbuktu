@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Opsætning @if ($course)<span style="font-weight: 400; color: #65676b; font-size: 14px;">· {{ $course->name }}</span>@endif</h1>
  <form method="POST" action="{{ url('/slophub/admin/algorithm/reset') }}">
    @csrf
    <button class="btn btn-secondary" onclick="return confirm('Nulstil til standard?')">Nulstil til standard</button>
  </form>
</div>

@include('admin._opsaetning_tabs')

@if (!$course)
  <div class="alert alert-error">Du har intet aktivt kursus. Opret ét først i Kursusmanager.</div>
@endif

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ url('/slophub/admin/algorithm') }}">
  @csrf

  {{-- Students commenting toggle --}}
  <div class="card">
    <h3 style="margin-bottom: 8px;">Studerende-kommentarer</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 10px;">Tillad studerende at kommentere på opslag i feedet og svare på personas' kommentarer. Slået fra som standard.</p>
    @php $canComment = $config['students_can_comment'] ?? false; @endphp
    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
      <input type="checkbox" name="students_can_comment" value="1" {{ $canComment ? 'checked' : '' }} style="width: 18px; height: 18px;">
      <span>Tillad studerende at deltage i kommentarer</span>
    </label>
  </div>

  {{-- Comment model --}}
  <div class="card">
    <h3 style="margin-bottom: 8px;">Model til kommentarer</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 10px;">LLM der genererer personaernes kommentarer. Priser er USD per 1M tokens (input / output). 1000 danske tegn ≈ 300 tokens.</p>
    <select name="comment_model" style="width: 100%; max-width: 520px; padding: 8px 12px; border: 1px solid #dadde1; border-radius: 6px; font-size: 14px;">
      @php $currentModel = $config['comment_model'] ?? 'gemini-2.5-flash'; @endphp
      @foreach ($models as $id => $m)
        <option value="{{ $id }}" {{ $currentModel === $id ? 'selected' : '' }}>
          {{ $m['label'] }} — ${{ number_format($m['price_in'], 3) }} in / ${{ number_format($m['price_out'], 3) }} out per 1M tokens
        </option>
      @endforeach
    </select>
  </div>

  {{-- Round duration: primary setting at top --}}
  <div class="card" style="background: #e7f3ff; border: 1px solid #b6d8fb;">
    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
      <div style="flex: 1; min-width: 240px;">
        <h3 style="margin-bottom: 4px;">Runde-varighed</h3>
        <p style="color: #1c1e21; font-size: 13px; margin: 0;">Hvor mange minutter går der mellem hver runde i simuleringen.</p>
      </div>
      <div style="display: flex; align-items: center; gap: 8px; font-size: 16px;">
        Post hver
        <input type="number" name="round_duration_minutes" min="1" max="60"
               value="{{ $config['round_duration_minutes'] ?? 1 }}"
               style="width: 70px; font-size: 16px; font-weight: 700; text-align: center; padding: 6px 8px; border: 1px solid #1877f2; border-radius: 6px;">
        minut
      </div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-bottom: 12px;">Preset</h3>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;" id="presetGroup">
      @foreach ($presets as $key => $vals)
        <label class="preset-chip" data-preset='@json($vals)' style="padding: 8px 14px; border: 1px solid #dadde1; border-radius: 16px; cursor: pointer; font-size: 13px;">
          <input type="radio" name="preset" value="{{ $key }}" style="margin-right: 6px;"> {{ ucfirst($key) }}-agtig
        </label>
      @endforeach
      <button type="button" id="customBtn" class="preset-chip" style="padding: 8px 14px; border: 1px solid #dadde1; border-radius: 16px; cursor: pointer; font-size: 13px; background: transparent;">Custom</button>
    </div>
    <div style="margin-top: 10px; font-size: 12px; color: #65676b;">
      Klik en preset for at flytte sliderne til den konfiguration. Du kan stadig finjustere bagefter.
      <br><strong>OBS:</strong> Disse presets er kvalificerede gæt baseret på platformenes omdømme — ikke leaked algoritme-specs.
    </div>
  </div>

  <script>
  document.querySelectorAll('.preset-chip[data-preset]').forEach(chip => {
    chip.addEventListener('click', () => {
      const vals = JSON.parse(chip.dataset.preset);
      Object.entries(vals).forEach(([key, val]) => {
        const slider = document.querySelector(`input[name="${key}"]`);
        if (slider) {
          slider.value = val;
          const label = slider.nextElementSibling;
          if (label) label.textContent = parseFloat(slider.step) < 1 ? Number(val).toFixed(2) : val;
        }
      });
    });
  });
  document.getElementById('customBtn').addEventListener('click', () => {
    document.querySelectorAll('input[name="preset"]').forEach(r => r.checked = false);
  });
  </script>

  @php
  $sliders = [
    ['Reach-mekanik', [
      ['base_exposure_per_round', 'Base eksponering per runde', 'antal nye personas runde 1', 5, 50, 1],
      ['exposure_growth_factor', 'Eksponerings-vækstfaktor', 'multiplikator ved højt engagement', 1.0, 3.0, 0.1],
      ['trending_threshold', 'Trending threshold', 'engagement der udløser viralitet', 5, 50, 1],
    ]],
    ['Persona-adfærd', [
      ['max_llm_calls_per_round', 'Max LLM-kald per runde', 'cost control', 5, 50, 1],
      ['notification_return_probability', 'Notifikations-returrate', 'chance for at en svaret-persona kommer tilbage', 0.0, 1.0, 0.05],
      ['comments_visible_to_personas', 'Synlige kommentarer per persona', 'antal seneste kommentarer en persona ser før de beslutter sig', 1, 50, 1],
    ]],
    ['Graf-baseret spredning', [
      ['reaction_spread_rate', 'Reaktions-spredning (%)', '% af en reaktørs venner der ser opslaget næste runde', 0, 100, 1],
      ['share_spread_rate', 'Delings-spredning (%)', '% af en deler\'s venner der ser opslaget næste runde', 0, 100, 1],
    ]],
    ['Runde-grænser', [
      ['max_rounds', 'Max runder', 'opslag stoppes efter dette', 5, 50, 1],
      ['inactive_threshold_rounds', 'Inaktivitets-grænse', 'opslag stoppes efter X runder uden aktivitet', 2, 15, 1],
    ]],
  ];
  @endphp

  @foreach ($sliders as [$section, $fields])
    <div class="card">
      <h3 style="margin-bottom: 10px;">{{ $section }}</h3>
      @foreach ($fields as [$key, $label, $hint, $min, $max, $step])
        @php $val = $config[$key] ?? $defaults[$key] ?? $min; @endphp
        <div style="display: grid; grid-template-columns: 240px 1fr 70px; gap: 14px; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f2f5;">
          <label>
            <strong>{{ $label }}</strong>
            @if ($hint)<br><small style="color: #65676b;">{{ $hint }}</small>@endif
          </label>
          <input type="range" name="{{ $key }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" value="{{ $val }}"
                 oninput="this.nextElementSibling.textContent = ({{ $step < 1 ? "(+this.value).toFixed(2)" : 'this.value' }})">
          <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">
            {{ $step < 1 ? number_format($val, 2) : $val }}
          </span>
        </div>
      @endforeach
    </div>
  @endforeach

  <div style="text-align: right; margin: 20px 0;">
    <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem konfiguration</button>
  </div>
</form>
@endsection
