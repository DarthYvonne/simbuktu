@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Mig</h1>
  @if ($course)<div style="color: #65676b; font-size: 13px;">Denne profil gælder for kurset <strong>{{ $course->name }}</strong></div>@endif
</div>

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<style>
.mig-avatar-grid { display: grid; grid-template-columns: 120px 1fr; gap: 16px; align-items: start; }
@media (max-width: 767px) { .mig-avatar-grid { grid-template-columns: 1fr; } }
</style>

<form method="POST" action="{{ url('/simulation/mig') }}" enctype="multipart/form-data">
  @csrf

  <div class="card">
    <h3 style="margin-bottom: 4px;">Poster som</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 14px;">Identiteten dine opslag sendes fra i dette kursus. Kan være dig selv, et fiktivt firma, eller en anden person.</p>

    <div class="mig-avatar-grid">
      <div>
        <div class="avatar-wrap" style="position: relative; width: 120px; height: 120px;">
          <img id="posterImgPreview" src="{{ ($membership && $membership->poster_image_path) ? Storage::url($membership->poster_image_path) : '' }}" style="{{ ($membership && $membership->poster_image_path) ? '' : 'display:none;' }} width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
          <div id="posterImgPlaceholder" style="display: {{ ($membership && $membership->poster_image_path) ? 'none' : 'flex' }}; width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #a1c4fd, #c2e9fb); align-items: center; justify-content: center; color: #fff; font-size: 36px; font-weight: 700;">{{ strtoupper(substr($membership?->poster_name ?? '?', 0, 2)) }}</div>
        </div>
        <label style="display: block; margin-top: 8px; font-size: 12px; cursor: pointer; color: #1877f2;">
          <input type="file" name="poster_image" accept="image/*" style="display: none;" onchange="previewFile(this, 'posterImgPreview', 'posterImgPlaceholder', 'posterPicked')">
          <span id="posterPicked">Skift billede</span>
        </label>
      </div>

      <div style="display: grid; gap: 10px;">
        <label>Navn (vises som afsender)<input type="text" name="poster_name" value="{{ $membership?->poster_name }}" style="width: 100%;" placeholder="F.eks. Acme A/S, Lars Hansen"></label>
      </div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-bottom: 4px;">Dig</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 14px;">Synlig på dit kursus så andre kan tage kontakt.</p>

    <div class="mig-avatar-grid">
      <div>
        <div class="avatar-wrap" style="position: relative; width: 120px; height: 120px;">
          <img id="selfImgPreview" src="{{ ($membership && $membership->image_path) ? Storage::url($membership->image_path) : '' }}" style="{{ ($membership && $membership->image_path) ? '' : 'display:none;' }} width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
          <div id="selfImgPlaceholder" style="display: {{ ($membership && $membership->image_path) ? 'none' : 'flex' }}; width: 120px; height: 120px; border-radius: 50%; background: #e4e6eb; align-items: center; justify-content: center; color: #65676b; font-size: 36px;"><i class="fa-solid fa-user"></i></div>
        </div>
        <label style="display: block; margin-top: 8px; font-size: 12px; cursor: pointer; color: #1877f2;">
          <input type="file" name="image" accept="image/*" style="display: none;" onchange="previewFile(this, 'selfImgPreview', 'selfImgPlaceholder', 'selfPicked')">
          <span id="selfPicked">Skift billede</span>
        </label>
      </div>

      <div style="display: grid; gap: 10px;">
        <label>Navn<input type="text" name="name" value="{{ $user->name }}" style="width: 100%;"></label>
        <label>Email<input type="email" name="email" value="{{ $user->email }}" style="width: 100%;"></label>
        <label>Titel/rolle (for dette kursus)<input type="text" name="title" value="{{ $membership?->title }}" style="width: 100%;" placeholder="F.eks. Kommunikationskonsulent, MBA-studerende"></label>
        <label>LinkedIn<input type="url" name="linkedin" value="{{ $membership?->linkedin }}" style="width: 100%;" placeholder="https://linkedin.com/in/..."></label>
        <label>Bio<textarea name="bio" style="width: 100%; min-height: 80px;" placeholder="Kort om dig">{{ $membership?->bio }}</textarea></label>
      </div>
    </div>
  </div>

  <div style="text-align: right; margin: 20px 0;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem profil</button>
  </div>
</form>

<style>
form label { font-weight: 600; font-size: 13px; }
form input, form textarea { font-family: inherit; font-size: 14px; padding: 8px 12px; border: 1px solid #dadde1; border-radius: 6px; background: #fff; }
form input:focus, form textarea:focus { outline: none; border-color: #1877f2; }
.file-picked { color: #22c55e !important; font-weight: 700; }
</style>
<script>
function previewFile(input, imgId, phId, labelId) {
  const file = input.files && input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById(imgId);
    const ph = document.getElementById(phId);
    img.src = e.target.result;
    img.style.display = 'block';
    ph.style.display = 'none';
  };
  reader.readAsDataURL(file);
  const lbl = document.getElementById(labelId);
  lbl.textContent = '✓ Valgt — klik Gem profil';
  lbl.classList.add('file-picked');
}
</script>
@endsection
