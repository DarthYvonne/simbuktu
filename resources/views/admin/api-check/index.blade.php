@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>API-tjek</h1>
  <a href="{{ url('/simulation/admin/api-check') }}" class="btn btn-primary" style="font-size:13px;">
    <i class="fa-solid fa-rotate"></i> Tjek igen
  </a>
</div>

@include('admin._opsaetning_tabs')

<style>
.api-row { display: grid; grid-template-columns: 22px 200px 100px 1fr; gap: 12px; align-items: center; padding: 12px 14px; border-bottom: 1px solid #f0f2f5; }
.api-row:last-child { border-bottom: none; }
.api-row .ico { font-size: 16px; text-align: center; }
.api-row .ico.ok { color: #16a34a; }
.api-row .ico.error { color: #b91c1c; }
.api-row .ico.missing { color: #f59e0b; }
.api-row .name { font-weight: 600; font-size: 14px; color: #1c1e21; }
.api-row .ms { font-size: 12px; color: #65676b; font-variant-numeric: tabular-nums; text-align: right; }
.api-row .detail { font-size: 12.5px; color: #65676b; word-break: break-word; }
.api-row.error .detail { color: #b91c1c; }
.api-row.missing .detail { color: #92400e; }

.api-card { background: #fff; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); max-width: 860px; margin-top: 14px; overflow: hidden; }
.api-card .head { padding: 12px 14px; background: #f7f8fa; border-bottom: 1px solid #e4e6eb; font-size: 12px; font-weight: 700; color: #65676b; text-transform: uppercase; letter-spacing: .4px; }
</style>

<div class="api-card">
  <div class="head">Status</div>
  @foreach ($checks as $c)
    @php
      $cls = $c['status']; // ok / error / missing
      $icon = match ($cls) {
        'ok'      => 'fa-circle-check',
        'missing' => 'fa-circle-exclamation',
        default   => 'fa-circle-xmark',
      };
    @endphp
    <div class="api-row {{ $cls }}">
      <span class="ico {{ $cls }}"><i class="fa-solid {{ $icon }}"></i></span>
      <span class="name">{{ $c['name'] }}</span>
      <span class="ms">{{ $c['ms'] }} ms</span>
      <span class="detail">{{ $c['detail'] ?? '' }}</span>
    </div>
  @endforeach
</div>

@endsection
