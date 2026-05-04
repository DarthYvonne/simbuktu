@php
$bpHeaderCourse = \Illuminate\Support\Facades\Auth::user()?->currentCourse();
@endphp
<div class="view-header">
  <div>
    <h1 style="margin-bottom: 3px;">Personligheder</h1>
    @if ($bpHeaderCourse)
      <div style="font-size: 13px; color: #65676b;">
        {{ $bpHeaderCourse->name }} &nbsp;·&nbsp;
        Aktiv personlighed:
        <strong style="color: {{ $bpHeaderCourse->blueprint_id ? '#1c1e21' : '#b91c1c' }};">
          {{ $bpHeaderCourse->blueprint?->name ?? 'Ingen valgt' }}
        </strong>
      </div>
    @else
      <div style="font-size: 13px; color: #65676b;">Komplette persona-skabeloner: en ordnet liste af dimensioner med håndskrevne facetter.</div>
    @endif
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('createModal').style.display='flex'">
    <i class="fa-solid fa-plus"></i> Ny personlighed
  </button>
</div>

@include('admin._personligheder_tabs')

<div id="createModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:12px; padding:28px; width:100%; max-width:460px; box-shadow:0 8px 40px rgba(0,0,0,.2);">
    <h2 style="margin:0 0 18px; font-size:18px;">Ny personlighed</h2>
    <form method="POST" action="{{ url('/simulation/admin/blueprints') }}">
      @csrf
      <div style="margin-bottom:14px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Navn *</label>
        <input type="text" name="name" required autofocus placeholder="fx Klima-shitstorm, Boomer-Facebook"
          style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
      </div>
      <div style="margin-bottom:20px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Beskrivelse</label>
        <input type="text" name="description" placeholder="Valgfri kort beskrivelse"
          style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
      </div>
      <div style="display:flex; gap:8px; justify-content:flex-end;">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('createModal').style.display='none'">Annuller</button>
        <button type="submit" class="btn btn-primary">Opret</button>
      </div>
    </form>
  </div>
</div>
