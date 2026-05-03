@extends('layouts.app')
@section('content')

@php
$base = '/slophub/admin/populations/'.$population->id;

$labelMap = [
  'gender'    => 'Køn',
  'region'    => 'Region',
  'city_type' => 'Bytype',
  'education' => 'Uddannelse',
  'heritage'  => 'Herkomst',
];

$dimDescriptions = [
  'age_brackets' => 'Aldersgrupper hvorfra personas samples. Vægt = relativ sandsynlighed.',
  'gender'       => 'Kønsfordeling.',
  'region'       => 'Geografisk fordeling på de fem danske regioner.',
  'city_type'    => 'Urbaniseringsgrad.',
  'education'    => 'Højest fuldførte uddannelse. Bemærk: fordelingen modificeres automatisk for unge (<22 år) og ældre (≥65 år).',
  'heritage'     => 'Etnisk baggrund.',
];

$isOverridden = fn($dim) => isset($overrides[$dim]);
@endphp

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
@if ($errors->any())
  <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<style>
  .demo-card { margin-bottom: 14px; }
  .demo-card .dim-header {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; margin-bottom: 12px; flex-wrap: wrap;
  }
  .demo-card h3 { margin: 0; font-size: 15px; font-weight: 700; }
  .demo-card .dim-desc { font-size: 12px; color: #65676b; margin: 2px 0 0; }
  .demo-card .badge-overridden {
    background:#fef3c7; color:#92400e; font-size:11px; font-weight:700;
    padding:2px 8px; border-radius:20px; white-space:nowrap;
  }
  .demo-card .badge-default {
    background:#f0f2f5; color:#65676b; font-size:11px; font-weight:600;
    padding:2px 8px; border-radius:20px; white-space:nowrap;
  }
  .reset-btn {
    background: none; border: none; color: #65676b; font-size: 12px; cursor: pointer;
    padding: 2px 6px; text-decoration: underline;
  }
  .reset-btn:hover { color: #1877f2; }
  .demo-row {
    display: grid; grid-template-columns: 180px 90px 1fr 50px; gap: 10px;
    align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f2f5;
  }
  .demo-row:last-child { border-bottom: none; }
  .demo-row .label { font-size: 13px; color: #1c1e21; }
  .demo-row input[type="number"] {
    width: 100%; padding: 5px 8px; border: 1px solid #dadde1; border-radius: 6px;
    font-size: 13px; font-family: inherit; text-align: right;
  }
  .demo-row .bar-track {
    height: 8px; background: #f0f2f5; border-radius: 4px; overflow: hidden;
  }
  .demo-row .bar-fill {
    height: 100%; background: #1877f2; transition: width 0.15s ease;
  }
  .demo-row .pct { font-size: 12px; color: #65676b; text-align: right; font-variant-numeric: tabular-nums; }

  .age-row {
    display: grid; grid-template-columns: 70px 70px 90px 1fr 50px 30px; gap: 10px;
    align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f2f5;
  }
  .age-row:last-child { border-bottom: none; }
  .age-row input[type="number"] {
    width: 100%; padding: 5px 8px; border: 1px solid #dadde1; border-radius: 6px;
    font-size: 13px; font-family: inherit; text-align: right;
  }
  .age-row .row-del {
    background: none; border: none; color: #b91c1c; cursor: pointer; font-size: 14px; padding: 4px;
  }
  .age-row .row-del:hover { color: #7f1d1d; }
  .age-headers {
    display: grid; grid-template-columns: 70px 70px 90px 1fr 50px 30px; gap: 10px;
    font-size: 11px; font-weight: 600; color: #65676b; text-transform: uppercase; padding-bottom: 4px;
  }
  .add-row-btn {
    margin-top: 8px; background: #f0f2f5; border: 1px dashed #b0b3b8; color: #1877f2;
    padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600;
  }
  .add-row-btn:hover { background: #e4e6eb; }

  .info-card {
    background: #f0f7ff; border: 1px solid #cfe2ff; border-radius: 8px;
    padding: 12px 14px; margin-bottom: 14px; font-size: 13px; color: #1c1e21;
  }
  .info-card code { background: rgba(0,0,0,0.05); padding: 1px 5px; border-radius: 3px; font-size: 12px; }
</style>

<div style="max-width: 900px;">
  <div class="info-card">
    <strong>Vægte styrer hvordan personas samples i denne population.</strong>
    Vægt 0 udelukker en kategori. Højere vægt giver højere sandsynlighed.
    Procenttal opdateres live. Klik <em>Nulstil</em> for at vende tilbage til den globale standard
    fra <code>config/personas.php</code>.
  </div>

  <form method="POST" action="{{ url("$base/demografi") }}" id="demografiForm">
    @csrf @method('PATCH')

    {{-- AGE BRACKETS --}}
    <div class="card demo-card" data-dim="age_brackets">
      <div class="dim-header">
        <div>
          <h3>Aldersgrupper</h3>
          <p class="dim-desc">{{ $dimDescriptions['age_brackets'] }}</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
          @if ($isOverridden('age_brackets'))
            <span class="badge-overridden"><i class="fa-solid fa-pen"></i> Tilpasset</span>
            <form method="POST" action="{{ url("$base/demografi/age_brackets/reset") }}" style="display:inline;">
              @csrf
              <button type="submit" class="reset-btn" onclick="return confirm('Nulstil aldersgrupper til global standard?')">Nulstil</button>
            </form>
          @else
            <span class="badge-default">Global standard</span>
          @endif
        </div>
      </div>

      <div class="age-headers">
        <div>Fra</div><div>Til</div><div style="text-align:right;">Vægt</div><div></div><div style="text-align:right;">%</div><div></div>
      </div>
      <div id="ageBracketsBody">
        @foreach ($effective['age_brackets'] as $i => $b)
          <div class="age-row">
            <input type="number" name="age_brackets[{{ $i }}][min]" value="{{ $b['range'][0] }}" min="0" max="120" required>
            <input type="number" name="age_brackets[{{ $i }}][max]" value="{{ $b['range'][1] }}" min="0" max="120" required>
            <input type="number" name="age_brackets[{{ $i }}][weight]" value="{{ $b['weight'] }}" min="0" max="1000" step="0.1" required class="weight-input">
            <div class="bar-track"><div class="bar-fill" style="width:0%"></div></div>
            <div class="pct">0%</div>
            <button type="button" class="row-del" title="Slet" onclick="this.parentElement.remove(); recalc(this.closest('.demo-card'));">×</button>
          </div>
        @endforeach
      </div>
      <button type="button" class="add-row-btn" onclick="addAgeBracket()">+ Tilføj aldersgruppe</button>
    </div>

    {{-- FIXED-ROW DIMENSIONS --}}
    @foreach (['gender', 'region', 'city_type', 'education', 'heritage'] as $dim)
      <div class="card demo-card" data-dim="{{ $dim }}">
        <div class="dim-header">
          <div>
            <h3>{{ $labelMap[$dim] }}</h3>
            <p class="dim-desc">{{ $dimDescriptions[$dim] }}</p>
          </div>
          <div style="display:flex; gap:10px; align-items:center;">
            @if ($isOverridden($dim))
              <span class="badge-overridden"><i class="fa-solid fa-pen"></i> Tilpasset</span>
              <form method="POST" action="{{ url("$base/demografi/$dim/reset") }}" style="display:inline;">
                @csrf
                <button type="submit" class="reset-btn" onclick="return confirm('Nulstil {{ strtolower($labelMap[$dim]) }} til global standard?')">Nulstil</button>
              </form>
            @else
              <span class="badge-default">Global standard</span>
            @endif
          </div>
        </div>

        @foreach ($effective[$dim] as $key => $weight)
          <div class="demo-row">
            <div class="label">{{ $key }}</div>
            <input type="number" name="{{ $dim }}[{{ $key }}]" value="{{ $weight }}" min="0" max="1000" step="0.1" class="weight-input">
            <div class="bar-track"><div class="bar-fill" style="width:0%"></div></div>
            <div class="pct">0%</div>
          </div>
        @endforeach
      </div>
    @endforeach

    <div style="display:flex; justify-content:flex-end; align-items:center; padding:12px 0;">
      <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-floppy-disk"></i> Gem ændringer
      </button>
    </div>
  </form>
</div>

<script>
function recalc(card) {
  const inputs = card.querySelectorAll('.weight-input');
  let total = 0;
  inputs.forEach(i => total += parseFloat(i.value) || 0);
  inputs.forEach(i => {
    const v = parseFloat(i.value) || 0;
    const pct = total > 0 ? (v / total * 100) : 0;
    const row = i.closest('.demo-row, .age-row');
    const fill = row.querySelector('.bar-fill');
    const pctEl = row.querySelector('.pct');
    fill.style.width = pct.toFixed(1) + '%';
    pctEl.textContent = pct.toFixed(1) + '%';
  });
}

document.querySelectorAll('.demo-card').forEach(card => {
  card.addEventListener('input', e => {
    if (e.target.classList.contains('weight-input')) recalc(card);
  });
  recalc(card);
});

let nextAgeIdx = {{ count($effective['age_brackets']) }};
function addAgeBracket() {
  const body = document.getElementById('ageBracketsBody');
  const i = nextAgeIdx++;
  const row = document.createElement('div');
  row.className = 'age-row';
  row.innerHTML = `
    <input type="number" name="age_brackets[${i}][min]" value="0" min="0" max="120" required>
    <input type="number" name="age_brackets[${i}][max]" value="0" min="0" max="120" required>
    <input type="number" name="age_brackets[${i}][weight]" value="10" min="0" max="1000" step="0.1" required class="weight-input">
    <div class="bar-track"><div class="bar-fill" style="width:0%"></div></div>
    <div class="pct">0%</div>
    <button type="button" class="row-del" title="Slet" onclick="this.parentElement.remove(); recalc(this.closest('.demo-card'));">×</button>
  `;
  body.appendChild(row);
  recalc(body.closest('.demo-card'));
}
</script>

@endsection
