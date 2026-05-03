@extends('layouts.app')
@section('content')

@php $base = '/simulation/admin/populations/'.$population->id; @endphp
<div class="view-header">
  <h1>
    <a href="{{ url('/simulation/admin/populations') }}" style="color:#1877f2;"><i class="fa-solid fa-arrow-left"></i></a>
    <span style="font-weight:400;color:#65676b;">Population:</span> {{ $population->name }}
  </h1>
</div>

@include('admin.populations._tabs', ['population' => $population])

<div class="card">
  <div style="display: flex; gap: 14px; margin-bottom: 14px;">
    <div style="flex: 1; background: #f8f9fa; padding: 14px; border-radius: 8px;">
      <div style="font-size: 26px; font-weight: 700; color: #1877f2;">{{ $personaCount }}</div>
      <div style="font-size: 12px; color: #65676b; text-transform: uppercase;">Personas</div>
    </div>
    <div style="flex: 1; background: #f8f9fa; padding: 14px; border-radius: 8px;">
      <div style="font-size: 26px; font-weight: 700; color: #22c55e;">{{ $edgeCount }}</div>
      <div style="font-size: 12px; color: #65676b; text-transform: uppercase;">Venskaber nu</div>
    </div>
    <div style="flex: 1; background: #f8f9fa; padding: 14px; border-radius: 8px;">
      <div style="font-size: 26px; font-weight: 700; color: #7c3aed;">{{ $personaCount > 0 ? round($edgeCount * 2 / $personaCount, 1) : 0 }}</div>
      <div style="font-size: 12px; color: #65676b; text-transform: uppercase;">Venner per persona (snit)</div>
    </div>
  </div>
</div>

<form method="POST" action="{{ url("$base/personas/graph") }}">
  @csrf

  <div class="card">
    <h3 style="margin-bottom: 8px;">Vægte i similaritets-funktionen</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 10px;">Summen bør give ~1.0. Disse vægte bestemmer hvor meget hver faktor betyder for om to personer bliver venner.</p>
    @php
    $sliders = [
      ['demographics_weight', 'Demografi (alder, uddannelse, region)', 0, 1, 0.01, 0.30],
      ['subculture_weight', 'Subkultur-overlap', 0, 1, 0.01, 0.25],
      ['personality_weight', 'Personlighed (A, E, O-homofili)', 0, 1, 0.01, 0.25],
      ['heritage_weight', 'Herkomst', 0, 1, 0.01, 0.07],
      ['political_weight', 'Politisk enighed (bevidst svag)', 0, 1, 0.01, 0.08],
    ];
    @endphp
    @foreach ($sliders as [$key, $label, $min, $max, $step, $default])
      <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0; border-bottom: 1px solid #f0f2f5;">
        <label style="font-size: 13px;"><strong>{{ $label }}</strong></label>
        <input type="range" name="{{ $key }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" value="{{ $default }}" oninput="this.nextElementSibling.textContent = (+this.value).toFixed(2)">
        <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">{{ number_format($default, 2) }}</span>
      </div>
    @endforeach
  </div>

  <div class="card">
    <h3 style="margin-bottom: 8px;">Broer og støj</h3>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Bro-personas (%)</strong><br><small style="color:#65676b;">% af personerne der er "broer" mellem klynger</small></label>
      <input type="range" name="bridge_percentage" min="0" max="10" step="0.5" value="3" oninput="this.nextElementSibling.textContent = this.value + '%'">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">3%</span>
    </div>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Bro-bonus</strong><br><small style="color:#65676b;">ekstra similaritet hvis en af parterne er bro</small></label>
      <input type="range" name="bridge_bonus" min="0" max="0.3" step="0.01" value="0.10" oninput="this.nextElementSibling.textContent = (+this.value).toFixed(2)">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">0.10</span>
    </div>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Støj (%)</strong><br><small style="color:#65676b;">andel tilfældige venskaber (fjerne bekendtskaber)</small></label>
      <input type="range" name="noise_percentage" min="0" max="20" step="1" value="7" oninput="this.nextElementSibling.textContent = this.value + '%'">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">7%</span>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-bottom: 8px;">Antal venner per persona</h3>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Basis-antal</strong><br><small style="color:#65676b;">gennemsnittet (Big Five justerer op/ned)</small></label>
      <input type="range" name="base_friend_count" min="30" max="200" step="5" value="80" oninput="this.nextElementSibling.textContent = this.value">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">80</span>
    </div>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Min</strong></label>
      <input type="range" name="min_friends" min="5" max="50" step="1" value="15" oninput="this.nextElementSibling.textContent = this.value">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">15</span>
    </div>
    <div style="display: grid; grid-template-columns: 280px 1fr 70px; gap: 14px; align-items: center; padding: 6px 0;">
      <label style="font-size: 13px;"><strong>Max</strong></label>
      <input type="range" name="max_friends" min="50" max="500" step="10" value="200" oninput="this.nextElementSibling.textContent = this.value">
      <span style="font-family: monospace; color: #1877f2; font-weight: 600; text-align: right;">200</span>
    </div>
  </div>

  <div style="text-align: right; margin: 20px 0;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-play"></i> Byg graf i baggrunden</button>
  </div>
</form>

@if ($progress)
<div class="card" id="progressCard" style="background: #e7f3ff; border: 1px solid #b6d8fb;">
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <strong id="progPhase">{{ $progress['phase'] ?? '—' }}</strong>
      <span id="progLabel" style="color: #65676b; margin-left: 8px;">{{ $progress['done'] ?? 0 }} / {{ $progress['total'] ?? 0 }}</span>
    </div>
    <span class="spinner" id="progSpinner" style="border-color: #1877f2; border-top-color: transparent;"></span>
  </div>
  <div style="height: 6px; background: #fff; border-radius: 3px; overflow: hidden; margin-top: 10px;">
    <div id="progBar" style="height: 100%; background: #1877f2; width: 0%; transition: width 0.3s;"></div>
  </div>
  @if ($stats)
    <div style="margin-top: 14px; font-size: 13px; color: #1c1e21;">
      <strong>Resultat:</strong> {{ $stats['edges'] }} venskaber · gennemsnit {{ $stats['avg_friends'] }} venner per persona · {{ $stats['bridges'] }} bro-personas · {{ $stats['noise_edges'] }} støj-venskaber
    </div>
  @endif
</div>
@endif

<script>
let sawActiveBuild = false; // only reload if we actually watched a build progress
async function pollGraph() {
  try {
    const res = await fetch('{{ url("$base/personas/graph/status") }}');
    const data = await res.json();
    const p = data.progress;
    if (!p || p.phase === 'idle') return;
    const card = document.getElementById('progressCard');
    if (!card) return;
    if (p.phase !== 'done') {
      sawActiveBuild = true;
      document.getElementById('progPhase').textContent = p.phase;
      document.getElementById('progLabel').textContent = `${p.done || 0} / ${p.total || 0}`;
      const pct = p.total > 0 ? (p.done / p.total) * 100 : 0;
      document.getElementById('progBar').style.width = pct + '%';
    } else if (sawActiveBuild) {
      document.getElementById('progSpinner').style.display = 'none';
      clearInterval(graphTimer);
      setTimeout(() => location.reload(), 800);
    } else {
      clearInterval(graphTimer);
    }
  } catch {}
}
const graphTimer = setInterval(pollGraph, 1500);
pollGraph();
</script>
@endsection
