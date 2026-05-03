@php
$base = '/simulation/admin/populations/'.$population->id;
$path = request()->path();

$onPersonas = str_starts_with($path, ltrim("$base/personas", '/'))
    && !str_starts_with($path, ltrim("$base/personas/graph", '/'));
$onGraph    = str_starts_with($path, ltrim("$base/personas/graph", '/'));
$onKonfig   = $path === ltrim($base, '/')
    || str_starts_with($path, ltrim("$base/demografi", '/'))
    || str_starts_with($path, ltrim("$base/subkultur", '/'))
    || str_starts_with($path, ltrim("$base/personlighed", '/'))
    || str_starts_with($path, ltrim("$base/prompts", '/'));

$tabs = [
  ['Personas',     url("$base/personas"),            $onPersonas],
  ['Social graf',  url("$base/personas/graph/view"), $onGraph],
  ['Konfiguration', url($base),                      $onKonfig],
];
@endphp
<div style="display: flex; gap: 2px; border-bottom: 1px solid #dadde1; margin-bottom: 14px;">
  @foreach ($tabs as [$label, $url, $active])
    <a href="{{ $url }}" style="padding: 10px 18px; border-bottom: 3px solid {{ $active ? '#1877f2' : 'transparent' }}; color: {{ $active ? '#1877f2' : '#65676b' }}; font-weight: 600; font-size: 14px; text-decoration: none; white-space: nowrap;">{{ $label }}</a>
  @endforeach
</div>
