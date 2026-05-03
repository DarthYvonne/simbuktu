@extends('layouts.app')
@section('content')

@php
$base = '/slophub/admin/populations/'.$population->id;
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
  .sub-card { margin-bottom: 14px; }
  .sub-card .dim-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 12px; margin-bottom: 12px; flex-wrap: wrap;
  }
  .sub-card h3 { margin: 0; font-size: 15px; font-weight: 700; }
  .sub-card .dim-desc { font-size: 12px; color: #65676b; margin: 2px 0 0; max-width: 600px; }
  .sub-card .badge-overridden {
    background:#fef3c7; color:#92400e; font-size:11px; font-weight:700;
    padding:2px 8px; border-radius:20px; white-space:nowrap;
  }
  .sub-card .badge-default {
    background:#f0f2f5; color:#65676b; font-size:11px; font-weight:600;
    padding:2px 8px; border-radius:20px; white-space:nowrap;
  }
  .reset-btn {
    background: none; border: none; color: #65676b; font-size: 12px; cursor: pointer;
    padding: 2px 6px; text-decoration: underline;
  }
  .reset-btn:hover { color: #1877f2; }
  .sub-headers {
    display: grid; grid-template-columns: 1fr 90px 160px 50px 30px; gap: 10px;
    font-size: 11px; font-weight: 600; color: #65676b; text-transform: uppercase; padding-bottom: 4px;
  }
  .sub-row {
    display: grid; grid-template-columns: 1fr 90px 160px 50px 30px; gap: 10px;
    align-items: center; padding: 5px 0; border-bottom: 1px solid #f0f2f5;
  }
  .sub-row:last-child { border-bottom: none; }
  .sub-row input[type="text"] {
    width: 100%; padding: 5px 8px; border: 1px solid #dadde1; border-radius: 6px;
    font-size: 13px; font-family: inherit;
  }
  .sub-row input[type="number"] {
    width: 100%; padding: 5px 8px; border: 1px solid #dadde1; border-radius: 6px;
    font-size: 13px; font-family: inherit; text-align: right;
  }
  .sub-row .bar-track {
    height: 8px; background: #f0f2f5; border-radius: 4px; overflow: hidden;
  }
  .sub-row .bar-fill {
    height: 100%; background: #1877f2; transition: width 0.15s ease;
  }
  .sub-row .pct {
    font-size: 12px; color: #65676b; text-align: right; font-variant-numeric: tabular-nums;
  }
  .sub-row .row-del {
    background: none; border: none; color: #b91c1c; cursor: pointer; font-size: 16px; padding: 4px;
  }
  .sub-row .row-del:hover { color: #7f1d1d; }
  .add-row-btn {
    margin-top: 10px; background: #f0f2f5; border: 1px dashed #b0b3b8; color: #1877f2;
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
    <strong>Subkulturer er den pulje hver persona kan tilhøre 1–2 af.</strong>
    Tilpas listen til denne population — fx for et gymnasium kan du fjerne <code>boomer-Facebook</code>
    og tilføje <code>BookTok</code>, <code>Genshin Impact</code> osv. Vægt = relativ sandsynlighed.
    <br><br>
    <em>Bemærk:</em> samplingen modificerer stadig vægte automatisk efter alder/køn/uddannelse for de
    standard-subkulturer der er bevaret (fx <code>gaming</code> får boost for unge mænd). Helt nye navne
    får ingen automatiske modifikatorer.
  </div>

  <div class="card sub-card">
    <div class="dim-header">
      <div>
        <h3>Subkulturer i denne population</h3>
        <p class="dim-desc">Hver persona får tildelt 1–2 subkulturer fra denne pulje. Vægt 0 udelukker.</p>
      </div>
      <div style="display:flex; gap:10px; align-items:center;">
        @if ($isOverridden)
          <span class="badge-overridden"><i class="fa-solid fa-pen"></i> Tilpasset</span>
          <form method="POST" action="{{ url("$base/subkultur/reset") }}" style="display:inline;">
            @csrf
            <button type="submit" class="reset-btn" onclick="return confirm('Nulstil subkulturer til global standard?')">Nulstil</button>
          </form>
        @else
          <span class="badge-default">Global standard</span>
        @endif
      </div>
    </div>

    <form method="POST" action="{{ url("$base/subkultur") }}" id="subkulturForm">
      @csrf @method('PATCH')

      <div class="sub-headers">
        <div>Navn</div><div style="text-align:right;">Vægt</div><div></div><div style="text-align:right;">%</div><div></div>
      </div>
      <div id="subBody">
        @php $i = 0; @endphp
        @foreach ($effective as $name => $weight)
          <div class="sub-row">
            <input type="text" name="subcultures[{{ $i }}][name]" value="{{ $name }}" required>
            <input type="number" name="subcultures[{{ $i }}][weight]" value="{{ $weight }}" min="0" max="1000" step="0.1" required class="weight-input">
            <div class="bar-track"><div class="bar-fill" style="width:0%"></div></div>
            <div class="pct">0%</div>
            <button type="button" class="row-del" title="Fjern" onclick="this.parentElement.remove(); recalc();">×</button>
          </div>
          @php $i++; @endphp
        @endforeach
      </div>
      <button type="button" class="add-row-btn" onclick="addRow()">+ Tilføj subkultur</button>

      <div style="display:flex; justify-content:flex-end; align-items:center; padding-top:14px; margin-top:14px; border-top:1px solid #f0f2f5;">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Gem ændringer
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function recalc() {
  const inputs = document.querySelectorAll('#subBody .weight-input');
  let total = 0;
  inputs.forEach(i => total += parseFloat(i.value) || 0);
  inputs.forEach(i => {
    const v = parseFloat(i.value) || 0;
    const pct = total > 0 ? (v / total * 100) : 0;
    const row = i.closest('.sub-row');
    row.querySelector('.bar-fill').style.width = pct.toFixed(1) + '%';
    row.querySelector('.pct').textContent = pct.toFixed(1) + '%';
  });
}

document.getElementById('subBody').addEventListener('input', e => {
  if (e.target.classList.contains('weight-input')) recalc();
});

let nextIdx = {{ count($effective) }};
function addRow() {
  const body = document.getElementById('subBody');
  const i = nextIdx++;
  const row = document.createElement('div');
  row.className = 'sub-row';
  row.innerHTML = `
    <input type="text" name="subcultures[${i}][name]" value="" placeholder="Navn på subkultur" required>
    <input type="number" name="subcultures[${i}][weight]" value="5" min="0" max="1000" step="0.1" required class="weight-input">
    <div class="bar-track"><div class="bar-fill" style="width:0%"></div></div>
    <div class="pct">0%</div>
    <button type="button" class="row-del" title="Fjern" onclick="this.parentElement.remove(); recalc();">×</button>
  `;
  body.appendChild(row);
  row.querySelector('input[type="text"]').focus();
  recalc();
}

recalc();
</script>

@endsection
