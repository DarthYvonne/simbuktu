@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1><a href="{{ url('/simulation/posts') }}" style="color: #1877f2;">← Mine opslag</a></h1>
</div>

<style>
.composer { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
.composer-head { display: flex; align-items: center; gap: 10px; padding-bottom: 12px; border-bottom: 1px solid #dadde1; margin-bottom: 12px; }
.composer-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #a1c4fd, #c2e9fb); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
.composer-who strong { display: block; }
.composer-who small { color: #65676b; font-size: 12px; }
.composer-field { border: 1px solid #dadde1; border-radius: 10px; background: #f8f9fa; padding: 12px 14px; transition: border 0.15s, background 0.15s; }
.composer-field:focus-within { border-color: #1877f2; background: #fff; }
.composer textarea { width: 100%; border: none; resize: vertical; font-size: 16px; line-height: 1.5; min-height: 120px; outline: none; font-family: inherit; background: transparent; }
.attach-bar { display: flex; align-items: center; justify-content: space-between; border: 1px solid #dadde1; border-radius: 10px; padding: 10px 14px; margin-top: 12px; }
.attach-btn { background: transparent; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 13px; color: #45bd62; display: flex; align-items: center; gap: 6px; font-weight: 600; }
.attach-btn:hover { background: #f0f2f5; }
.preview-wrap { margin-top: 12px; position: relative; border-radius: 10px; overflow: hidden; border: 1px solid #dadde1; }
.preview-wrap .close { position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.6); color: #fff; border: none; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; z-index: 5; }
.img-preview { max-height: 400px; width: 100%; object-fit: cover; display: block; }
.author-input { border: 1px solid #dadde1; border-radius: 6px; padding: 6px 10px; font-size: 13px; font-family: inherit; }
.note { font-size: 12px; color: #65676b; margin-top: 8px; }
</style>

@if ($errors->any())
  <div class="alert alert-error">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<div class="composer">
  <form method="POST" action="{{ url('/simulation/posts/'.$post->id) }}" enctype="multipart/form-data" id="postForm">
    @csrf
    @method('PATCH')
    <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;">
    <input type="hidden" name="remove_image" id="removeImage" value="0">

    @php $m = auth()->user()->currentMembership(); $posterName = $post->author_name ?: ($m?->poster_name ?: auth()->user()->name); @endphp
    <div class="composer-head">
      @if ($m?->poster_image_path)
        <img src="{{ Storage::url($m->poster_image_path) }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
      @else
        <div class="composer-avatar" id="avatarInitials">{{ strtoupper(substr($posterName, 0, 2)) }}</div>
      @endif
      <div class="composer-who">
        <strong><input type="text" name="author_name" value="{{ $posterName }}" class="author-input" style="font-weight: 700; padding: 2px 6px;" oninput="updateAvatar(this.value)"></strong>
        <small>Rediger opslag</small>
      </div>
    </div>

    <div class="composer-field">
      <textarea name="body" id="bodyInput" required>{{ $post->body }}</textarea>
    </div>

    <div id="imagePreview" class="preview-wrap" style="{{ $post->image_path ? '' : 'display: none;' }}">
      <button type="button" class="close" onclick="clearImage()"><i class="fa-solid fa-xmark"></i></button>
      <img class="img-preview" id="imagePreviewImg" src="{{ $post->image_path ? Storage::url($post->image_path) : '' }}">
    </div>

    <div class="attach-bar">
      <button type="button" class="attach-btn" onclick="document.getElementById('imageInput').click()"><i class="fa-regular fa-image" style="font-size: 20px;"></i> <span>{{ $post->image_path ? 'Skift billede' : 'Tilføj et billede' }}</span></button>
      <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem ændringer</button>
    </div>
    <p class="note">Ændringer slår igennem på fremtidige reaktioner. Tryk "Kør igen" på opslaget hvis du vil slette eksisterende reaktioner og starte simuleringen forfra.</p>
  </form>
</div>

<script>
const imgInput = document.getElementById('imageInput');
const imgPreview = document.getElementById('imagePreview');
const imgEl = document.getElementById('imagePreviewImg');
const removeImage = document.getElementById('removeImage');

imgInput.addEventListener('change', (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    imgEl.src = ev.target.result;
    imgPreview.style.display = 'block';
    removeImage.value = '0';
  };
  reader.readAsDataURL(file);
});

function clearImage() {
  imgInput.value = '';
  imgPreview.style.display = 'none';
  removeImage.value = '1';
}

function updateAvatar(name) {
  const el = document.getElementById('avatarInitials');
  if (!el) return;
  const initials = (name || 'AN').trim().split(/\s+/).map(w => w[0] || '').join('').substring(0, 2).toUpperCase() || 'AN';
  el.textContent = initials;
}
</script>
@endsection
