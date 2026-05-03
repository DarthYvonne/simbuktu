@php
$tabs = [
  ['Personlighedsstruktur', url('/simulation/admin/blueprints'),         'simulation/admin/blueprints'],
  ['Dimensionsbibliotek',   url('/simulation/admin/blueprint-library'),  'simulation/admin/blueprint-library'],
];
@endphp
<div style="display: flex; gap: 2px; border-bottom: 1px solid #dadde1; margin-bottom: 14px;">
  @foreach ($tabs as [$label, $url, $path])
    @php $active = request()->is($path) || request()->is($path.'/*'); @endphp
    <a href="{{ $url }}" style="padding: 10px 18px; border-bottom: 3px solid {{ $active ? '#1877f2' : 'transparent' }}; color: {{ $active ? '#1877f2' : '#65676b' }}; font-weight: 600; font-size: 14px; text-decoration: none;">{{ $label }}</a>
  @endforeach
</div>
