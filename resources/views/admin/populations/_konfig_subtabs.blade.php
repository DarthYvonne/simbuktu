@php
$base = '/simulation/admin/populations/'.$population->id;
$path = request()->path();

$subtabs = [
  ['Indstillinger', url($base),                $path === ltrim($base, '/')],
  ['Demografi',     url("$base/demografi"),    str_starts_with($path, ltrim("$base/demografi", '/'))],
  ['Subkultur',     url("$base/subkultur"),    str_starts_with($path, ltrim("$base/subkultur", '/'))],
  ['Personlighed',  url("$base/personlighed"), str_starts_with($path, ltrim("$base/personlighed", '/'))],
  ['Prompts',       url("$base/prompts"),      str_starts_with($path, ltrim("$base/prompts", '/'))],
];
@endphp
<div style="display: inline-flex; gap: 2px; background: #f0f2f5; border-radius: 8px; padding: 3px; margin-bottom: 16px;">
  @foreach ($subtabs as [$label, $url, $active])
    <a href="{{ $url }}" style="padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; color: {{ $active ? '#1c1e21' : '#65676b' }}; background: {{ $active ? '#fff' : 'transparent' }}; box-shadow: {{ $active ? '0 1px 2px rgba(0,0,0,0.06)' : 'none' }};">{{ $label }}</a>
  @endforeach
</div>
