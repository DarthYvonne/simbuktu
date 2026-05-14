@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>System @if ($course)<span style="font-weight: 400; color: #65676b; font-size: 14px;">· {{ $course->name }}</span>@endif</h1>
  <div style="display: flex; gap: 8px;">
    <a href="{{ url('/simulation/admin/usage') }}" class="btn {{ $scope === 'course' ? 'btn-primary' : 'btn-secondary' }}">Dette kursus</a>
    <a href="{{ url('/simulation/admin/usage?scope=all') }}" class="btn {{ $scope === 'all' ? 'btn-primary' : 'btn-secondary' }}">Alle kurser</a>
  </div>
</div>

@include('admin._opsaetning_tabs')

@php
$fmtDkk = function ($v) {
    if ($v === 0.0 || $v === 0) return '0 kr';
    if ($v >= 1) return number_format($v, 2, ',', '.') . ' kr';
    if ($v >= 0.01) return number_format($v, 3, ',', '.') . ' kr';
    return number_format($v, 4, ',', '.') . ' kr';
};
$fmtTok = fn ($v) => $v >= 1000 ? number_format($v / 1000, 1, ',', '.') . 'k' : (string) $v;
$cardLabel = ['today' => 'I dag', 'week' => 'Sidste 7 dage', 'month' => 'Sidste 30 dage', 'all' => 'I alt'];
@endphp

<div class="stats-grid">
  @foreach ($cards as $key => $c)
    <div class="card" style="text-align: center;">
      <div style="font-size: 11px; color: #65676b; text-transform: uppercase; margin-bottom: 4px;">{{ $cardLabel[$key] }}</div>
      <div style="font-size: 22px; font-weight: 700; color: #1877f2;">{{ $fmtDkk($c['dkk']) }}</div>
      <div style="font-size: 12px; color: #65676b; margin-top: 4px;">{{ $c['calls'] }} kald · {{ $fmtTok($c['tokens']) }} tokens</div>
    </div>
  @endforeach
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
  <div class="card">
    <h3 style="font-size: 14px; color: #65676b; text-transform: uppercase; margin-bottom: 12px;">Forbrug per prompt</h3>
    @if ($perPrompt->isEmpty())
      <div style="color: #65676b; font-size: 13px;">Intet forbrug endnu.</div>
    @else
      <table style="width: 100%; font-size: 13px;">
        <thead>
          <tr style="color: #65676b; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #dadde1;">
            <th style="text-align: left; padding: 6px 0;">Prompt</th>
            <th style="text-align: right;">Kald</th>
            <th style="text-align: right;">Tokens (in / out)</th>
            <th style="text-align: right;">Pris</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($perPrompt as $r)
            <tr style="border-bottom: 1px solid #f0f2f5;">
              <td style="padding: 8px 0; font-weight: 600;">{{ $r['name'] }}</td>
              <td style="text-align: right; font-family: monospace;">{{ $r['calls'] }}</td>
              <td style="text-align: right; font-family: monospace; color: #65676b;">{{ $fmtTok($r['in_tok']) }} / {{ $fmtTok($r['out_tok']) }}</td>
              <td style="text-align: right; font-family: monospace; font-weight: 600;">{{ $fmtDkk($r['dkk']) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  <div class="card">
    <h3 style="font-size: 14px; color: #65676b; text-transform: uppercase; margin-bottom: 12px;">Forbrug per model</h3>
    @if ($perModel->isEmpty())
      <div style="color: #65676b; font-size: 13px;">Intet forbrug endnu.</div>
    @else
      <table style="width: 100%; font-size: 13px;">
        <thead>
          <tr style="color: #65676b; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #dadde1;">
            <th style="text-align: left; padding: 6px 0;">Model</th>
            <th style="text-align: right;">Kald</th>
            <th style="text-align: right;">Tokens (in / out)</th>
            <th style="text-align: right;">Pris</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($perModel as $r)
            <tr style="border-bottom: 1px solid #f0f2f5;">
              <td style="padding: 8px 0; font-weight: 600;">{{ $r['model'] }} <span style="color:#65676b;font-weight:400;font-size:11px;">· {{ $r['provider'] }}</span></td>
              <td style="text-align: right; font-family: monospace;">{{ $r['calls'] }}</td>
              <td style="text-align: right; font-family: monospace; color: #65676b;">{{ $fmtTok($r['in_tok']) }} / {{ $fmtTok($r['out_tok']) }}</td>
              <td style="text-align: right; font-family: monospace; font-weight: 600;">{{ $fmtDkk($r['dkk']) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>

<div class="card" style="margin-top: 14px;">
  <h3 style="font-size: 14px; color: #65676b; text-transform: uppercase; margin-bottom: 12px;">Daglig forbrug — sidste 14 dage</h3>
  <div id="usage-chart" style="height: 280px; width: 100%;"></div>
</div>

<details class="card" style="margin-top: 14px;">
  <summary style="cursor: pointer; font-weight: 600;">Seneste 50 kald</summary>
  <table style="width: 100%; font-size: 12px; margin-top: 12px;">
    <thead>
      <tr style="color: #65676b; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid #dadde1;">
        <th style="text-align: left; padding: 6px 0;">Tidspunkt</th>
        <th style="text-align: left;">Prompt</th>
        <th style="text-align: left;">Model</th>
        <th style="text-align: right;">In</th>
        <th style="text-align: right;">Out</th>
        <th style="text-align: right;">ms</th>
        <th style="text-align: right;">Pris</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($latest as $r)
        <tr style="border-bottom: 1px solid #f8f9fa;">
          <td style="padding: 4px 0; color: #65676b; font-family: monospace;">{{ $r['when'] }}</td>
          <td>{{ $r['prompt'] }}</td>
          <td style="color: #65676b;">{{ $r['model'] }}</td>
          <td style="text-align: right; font-family: monospace;">{{ $r['in_tok'] }}</td>
          <td style="text-align: right; font-family: monospace;">{{ $r['out_tok'] }}</td>
          <td style="text-align: right; font-family: monospace; color: #65676b;">{{ $r['latency'] ?? '—' }}</td>
          <td style="text-align: right; font-family: monospace;">{{ $fmtDkk($r['dkk']) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</details>

<script src="https://code.highcharts.com/11.4.8/highcharts.js"></script>
<script>
(function () {
  const daily = @json($daily);
  Highcharts.chart('usage-chart', {
    chart: { backgroundColor: 'transparent', style: { fontFamily: 'inherit' } },
    title: { text: null },
    credits: { enabled: false },
    xAxis: {
      categories: daily.map(d => d.day),
      lineColor: '#dadde1',
      labels: { style: { color: '#65676b', fontSize: '11px' } }
    },
    yAxis: [
      { title: { text: 'Kald', style: { color: '#1877f2' } }, gridLineColor: '#f0f2f5', allowDecimals: false },
      { title: { text: 'kr', style: { color: '#22c55e' } }, opposite: true, gridLineColor: 'transparent' }
    ],
    legend: { itemStyle: { color: '#65676b', fontWeight: '500' } },
    tooltip: { shared: true, backgroundColor: '#fff', borderColor: '#dadde1' },
    plotOptions: { spline: { marker: { enabled: true, radius: 4, symbol: 'circle', lineWidth: 2, lineColor: '#fff' }, lineWidth: 3 } },
    series: [
      { type: 'spline', name: 'Kald', yAxis: 0, data: daily.map(d => d.calls), color: '#1877f2' },
      { type: 'spline', name: 'kr', yAxis: 1, data: daily.map(d => Math.round(d.dkk * 100) / 100), color: '#22c55e' }
    ]
  });
})();
</script>

@endsection
