@php
$bpBase = $urlBase;
$bpPath = request()->path();
$onOm       = str_ends_with($bpPath, '/personlighed/om');
$onPrompts  = str_ends_with($bpPath, '/personlighed/prompts');
$onTest     = str_ends_with($bpPath, '/personlighed/test') || str_contains($bpPath, '/personlighed/test');
$onDim      = !$onOm && !$onPrompts && !$onTest;

$bpSubtabs = [
  ['Om',          url("$bpBase/om"),      $onOm],
  ['Dimensioner', url("$bpBase"),         $onDim],
  ['Prompts',     url("$bpBase/prompts"), $onPrompts],
  ['Test AI',     url("$bpBase/test"),    $onTest],
];
@endphp
<div style="display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:14px;">
  <div style="display:inline-flex; gap:2px; background:#f0f2f5; border-radius:8px; padding:3px;">
    @foreach ($bpSubtabs as [$label, $url, $active])
      <a href="{{ $url }}" style="padding:6px 14px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none; color:{{ $active ? '#1c1e21' : '#65676b' }}; background:{{ $active ? '#fff' : 'transparent' }}; box-shadow:{{ $active ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">{{ $label }}</a>
    @endforeach
  </div>
  <div style="display:flex; gap:8px; align-items:center;">
    {{ $slot ?? '' }}
  </div>
</div>
