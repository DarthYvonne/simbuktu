@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>
    <a href="{{ url('/simulation/admin/blueprints') }}" style="color:#1877f2;"><i class="fa-solid fa-arrow-left"></i></a>
    <span style="font-weight:400;color:#65676b;">Personlighed:</span> {{ $blueprint->name }}
  </h1>
  <button type="submit" form="prompts-form" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem prompts</button>
</div>

<div style="display:inline-flex; gap:2px; background:#f0f2f5; border-radius:8px; padding:3px; margin-bottom:14px;">
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

<style>
.pp-layout { display:grid; grid-template-columns: 240px 1fr; gap:16px; align-items:start; }
.pp-side { position:sticky; top:14px; background:#fff; border-radius:10px; box-shadow:0 1px 2px rgba(0,0,0,0.08); padding:8px; max-height: calc(100vh - 40px); overflow-y:auto; }
.pp-side h4 { font-size:11px; font-weight:700; color:#65676b; text-transform:uppercase; letter-spacing:0.4px; padding:10px 10px 4px; }
.pp-item { display:flex; align-items:center; gap:8px; padding:8px 10px; border-radius:6px; cursor:pointer; user-select:none; }
.pp-item:hover { background:#f0f2f5; }
.pp-item.active { background:#e7f3ff; }
.pp-item.active .pp-name { color:#1877f2; font-weight:700; }
.pp-name { flex:1; font-size:13px; color:#1c1e21; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.pp-badge { font-size:10px; padding:2px 6px; border-radius:9px; font-weight:600; flex-shrink:0; }
.pp-badge.std { background:#f0f2f5; color:#65676b; }
.pp-badge.ov  { background:#dbeafe; color:#1e40af; }
.pp-pane { display:none; background:#fff; border-radius:10px; box-shadow:0 1px 2px rgba(0,0,0,0.08); overflow:hidden; }
.pp-pane.active { display:block; }
.pp-pane-head { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px 16px; background:#f7f8fa; border-bottom:1px solid #e4e6eb; }
.pp-pane-head .meta .t { font-weight:700; font-size:15px; }
.pp-pane-head .meta .k { font-weight:400; color:#65676b; font-size:12px; margin-left:6px; }
.pp-pane-head .meta .d { color:#65676b; font-size:12px; margin-top:3px; }
.pp-placeholders { padding:8px 16px; background:#fafbfc; border-bottom:1px solid #e4e6eb; font-size:11px; color:#65676b; font-family:monospace; line-height:1.7; }
.pp-placeholders .ph { display:inline-block; padding:1px 6px; background:#f0f2f5; border-radius:3px; margin-right:4px; }
.pp-pane textarea { width:100%; border:none; padding:14px 16px; font-family:'SFMono-Regular',Consolas,'Liberation Mono',monospace; font-size:12px; line-height:1.55; resize:vertical; outline:none; min-height: 480px; }
</style>

<form id="prompts-form" method="POST" action="{{ url('/simulation/admin/blueprints/'.$blueprint->id.'/prompts') }}">
  @csrf @method('PATCH')

  <div class="pp-layout">
    <div class="pp-side">
      <h4>Personality-prompts</h4>
      @foreach ($rows as $i => $row)
        <div class="pp-item {{ $i === 0 ? 'active' : '' }}" data-key="{{ $row['key'] }}" onclick="selectPrompt('{{ $row['key'] }}')">
          <span class="pp-name">{{ $row['name'] }}</span>
          <span class="pp-badge {{ $row['overridden'] ? 'ov' : 'std' }}">{{ $row['overridden'] ? 'Egen' : 'Std' }}</span>
        </div>
      @endforeach
    </div>

    <div>
      @foreach ($rows as $i => $row)
        <div class="pp-pane {{ $i === 0 ? 'active' : '' }}" data-pane="{{ $row['key'] }}">
          <div class="pp-pane-head">
            <div class="meta">
              <div><span class="t">{{ $row['name'] }}</span><span class="k">{{ $row['key'] }}</span></div>
              <div class="d">{{ $row['description'] }}</div>
            </div>
            <button type="button" onclick="resetPrompt('{{ $row['key'] }}')" style="background:none; border:1px solid #dadde1; border-radius:6px; padding:6px 12px; font-size:12px; cursor:pointer; color:#65676b;">
              <i class="fa-solid fa-rotate-left"></i> Standard
            </button>
          </div>
          @if (!empty($row['placeholders']))
            <div class="pp-placeholders">
              @foreach ($row['placeholders'] as $ph)
                <span class="ph">@{{{{ $ph }}}}</span>
              @endforeach
            </div>
          @endif
          <textarea name="prompts[{{ $row['key'] }}]" data-default="{{ $row['default'] }}">{{ $row['current'] }}</textarea>
        </div>
      @endforeach
    </div>
  </div>
</form>

<script>
function selectPrompt(key) {
  document.querySelectorAll('.pp-item').forEach(el => el.classList.toggle('active', el.dataset.key === key));
  document.querySelectorAll('.pp-pane').forEach(el => el.classList.toggle('active', el.dataset.pane === key));
}
function resetPrompt(key) {
  if (!confirm('Nulstil denne prompt til system-standarden?')) return;
  const ta = document.querySelector(`textarea[name="prompts[${key}]"]`);
  if (ta) ta.value = ta.dataset.default;
}
</script>

@endsection
