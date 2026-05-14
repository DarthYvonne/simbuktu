@php
$tabs = [
  ['Simulation', url('/simulation/admin/algorithm'), 'simulation/admin/algorithm'],
  ['Test AI model', url('/simulation/admin/personas/tester'), 'simulation/admin/personas/tester'],
  ['Aktuel kontekst', url('/simulation/admin/context'), 'simulation/admin/context'],
  ['API-tjek', url('/simulation/admin/api-check'), 'simulation/admin/api-check'],
  ['Forbrug', url('/simulation/admin/usage'), 'simulation/admin/usage'],
];
@endphp
<div style="display: flex; gap: 2px; border-bottom: 1px solid #dadde1; margin-bottom: 14px;">
  @foreach ($tabs as [$label, $url, $path])
    @php $active = request()->is($path) || request()->is($path.'/*'); @endphp
    <a href="{{ $url }}" style="padding: 10px 18px; border-bottom: 3px solid {{ $active ? '#1877f2' : 'transparent' }}; color: {{ $active ? '#1877f2' : '#65676b' }}; font-weight: 600; font-size: 14px; text-decoration: none;">{{ $label }}</a>
  @endforeach
</div>
