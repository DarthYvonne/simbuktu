@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>
    <a href="{{ url('/simulation/admin/blueprints') }}" style="color:#1877f2;"><i class="fa-solid fa-arrow-left"></i></a>
    <span style="font-weight:400;color:#65676b;">Personlighed:</span> {{ $blueprint->name }}
  </h1>
</div>

<div style="display:inline-flex; gap:2px; background:#f0f2f5; border-radius:8px; padding:3px; margin-bottom:16px;">
  <a href="{{ url('/simulation/admin/blueprints/'.$blueprint->id) }}"
     style="padding:6px 14px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none; color:#65676b;">Dimensioner</a>
  <a href="{{ url('/simulation/admin/blueprints/'.$blueprint->id.'/prompts') }}"
     style="padding:6px 14px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none; color:#1c1e21; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,0.06);">Prompts</a>
</div>

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-error">
    @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
  </div>
@endif

<form method="POST" action="{{ url('/simulation/admin/blueprints/'.$blueprint->id.'/prompts') }}" style="max-width:980px;">
  @csrf @method('PATCH')

  <div style="font-size:13px; color:#65676b; margin-bottom:14px; line-height:1.5;">
    Disse prompts styrer hvordan denne personlighed bliver brugt. Tom = brug system-standarden. Tilgængelige pladsholdere står over hver prompt.
  </div>

  @foreach ($rows as $row)
    <div style="background:#fff; border:1px solid #dadde1; border-radius:8px; margin-bottom:14px; overflow:hidden;">
      <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 14px; background:#f7f8fa; border-bottom:1px solid #e4e6eb;">
        <div>
          <div style="font-weight:700; font-size:14px;">{{ $row['name'] }} <span style="color:#65676b; font-weight:400; font-size:12px;">({{ $row['key'] }})</span></div>
          <div style="color:#65676b; font-size:12px; margin-top:2px;">{{ $row['description'] }}</div>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
          @if ($row['overridden'])
            <span style="font-size:11px; padding:3px 8px; border-radius:10px; background:#dbeafe; color:#1e40af; font-weight:600;">Overskrevet</span>
          @else
            <span style="font-size:11px; padding:3px 8px; border-radius:10px; background:#f0f2f5; color:#65676b; font-weight:600;">Standard</span>
          @endif
          <button type="button" onclick="resetPrompt('{{ $row['key'] }}', this)" style="background:none; border:1px solid #dadde1; border-radius:6px; padding:5px 10px; font-size:12px; cursor:pointer; color:#65676b;">
            <i class="fa-solid fa-rotate-left"></i> Standard
          </button>
        </div>
      </div>
      @if (!empty($row['placeholders']))
        <div style="padding:8px 14px; background:#fafbfc; border-bottom:1px solid #e4e6eb; font-size:11px; color:#65676b; font-family:monospace;">
          @foreach ($row['placeholders'] as $ph)
            <span style="display:inline-block; padding:1px 6px; background:#f0f2f5; border-radius:3px; margin-right:4px;">@{{{{ $ph }}}}</span>
          @endforeach
        </div>
      @endif
      <textarea
        name="prompts[{{ $row['key'] }}]"
        data-default="{{ $row['default'] }}"
        rows="14"
        style="width:100%; border:none; padding:12px 14px; font-family:'SFMono-Regular', Consolas, 'Liberation Mono', monospace; font-size:12px; line-height:1.55; resize:vertical; outline:none;"
      >{{ $row['current'] }}</textarea>
    </div>
  @endforeach

  <div style="display:flex; justify-content:flex-end; gap:8px;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem prompts</button>
  </div>
</form>

<script>
function resetPrompt(key, btn) {
  const wrap = btn.closest('div').parentElement;
  const ta = wrap.parentElement.querySelector(`textarea[name="prompts[${key}]"]`);
  if (!ta) return;
  if (!confirm('Nulstil denne prompt til system-standarden?')) return;
  ta.value = ta.dataset.default;
}
</script>

@endsection
