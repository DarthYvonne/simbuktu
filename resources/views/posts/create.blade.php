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
.composer textarea::placeholder { color: #8a8d91; }
.attach-bar { display: flex; align-items: center; justify-content: space-between; border: 1px solid #dadde1; border-radius: 10px; padding: 10px 14px; margin-top: 12px; }
.attach-bar span { font-weight: 600; font-size: 13px; }
.attach-btns { display: flex; gap: 6px; }
.attach-btn { background: transparent; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 20px; color: #65676b; }
.attach-btn:hover { background: #f0f2f5; }
.attach-btn.active { color: #22c55e; }
.preview-wrap { margin-top: 12px; position: relative; border-radius: 10px; overflow: hidden; border: 1px solid #dadde1; }
.preview-wrap .close { position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.6); color: #fff; border: none; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; z-index: 5; }
.img-preview { max-height: 400px; width: 100%; object-fit: cover; display: block; }
.link-card { display: flex; background: #f0f2f5; }
.link-card img { width: 160px; height: 100%; min-height: 100px; object-fit: cover; flex-shrink: 0; background: #e4e6eb; }
.link-card .link-meta { padding: 10px 14px; flex: 1; min-width: 0; }
.link-card .site { font-size: 11px; color: #65676b; text-transform: uppercase; letter-spacing: 0.5px; }
.link-card .title { font-weight: 600; font-size: 15px; margin: 2px 0; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.link-card .desc { font-size: 13px; color: #65676b; line-height: 1.35; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.link-loading { padding: 14px; color: #65676b; font-size: 13px; text-align: center; }
.submit-row { margin-top: 14px; display: flex; justify-content: flex-end; align-items: center; gap: 10px; }
.author-input { border: 1px solid #dadde1; border-radius: 6px; padding: 6px 10px; font-size: 13px; font-family: inherit; }
</style>

<div class="composer">
  <form method="POST" action="{{ url('/simulation/posts') }}" enctype="multipart/form-data" id="postForm">
    @csrf
    <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;">

    @php $m = auth()->user()->currentMembership(); $posterName = $m?->poster_name ?: auth()->user()->name; $posterImg = $m?->poster_image_path; @endphp
    <div class="composer-head">
      @if ($posterImg)
        <img src="{{ Storage::url($posterImg) }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
      @else
        <div class="composer-avatar" id="avatarInitials">{{ strtoupper(substr($posterName, 0, 2)) }}</div>
      @endif
      <div class="composer-who">
        <strong><input type="text" name="author_name" value="{{ $posterName }}" class="author-input" style="font-weight: 700; padding: 2px 6px;" oninput="updateAvatar(this.value)"></strong>
        <small>Offentligt opslag · <a href="{{ url('/simulation/mig') }}" style="color: #1877f2;">Skift afsendernavn</a></small>
      </div>
    </div>

    <div class="composer-field">
      <textarea name="body" id="bodyInput" placeholder="Skriv et opslag..." required></textarea>
    </div>

    <div id="imagePreview" class="preview-wrap" style="display: none;">
      <button type="button" class="close" onclick="clearImage()"><i class="fa-solid fa-xmark"></i></button>
      <img class="img-preview" id="imagePreviewImg">
    </div>

    <div id="linkPreview" class="preview-wrap" style="display: none;">
      <button type="button" class="close" onclick="clearLinkPreview(true)"><i class="fa-solid fa-xmark"></i></button>
      <div id="linkCard"></div>
    </div>

    <div class="attach-bar">
      <button type="button" class="attach-btn" onclick="document.getElementById('imageInput').click()" title="Billede" id="imgBtn" style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #45bd62; padding: 6px 10px;"><i class="fa-regular fa-image" style="font-size: 20px;"></i> <span style="font-weight: 600;">Tilføj et billede</span></button>
      <button class="btn btn-primary"><i class="fa-solid fa-play"></i> Opret og start simulering</button>
    </div>
  </form>
</div>

<script>
const bodyEl = document.getElementById('bodyInput');
const imgInput = document.getElementById('imageInput');
const imgPreview = document.getElementById('imagePreview');
const imgEl = document.getElementById('imagePreviewImg');
const imgBtn = document.getElementById('imgBtn');
const linkPreviewEl = document.getElementById('linkPreview');
const linkCard = document.getElementById('linkCard');
let lastUrl = null;
let userClearedLink = false;
let debounceTimer = null;

imgInput.addEventListener('change', (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    imgEl.src = ev.target.result;
    imgPreview.style.display = 'block';
    imgBtn.classList.add('active');
    linkPreviewEl.style.display = 'none';
  };
  reader.readAsDataURL(file);
});

function clearImage() {
  imgInput.value = '';
  imgPreview.style.display = 'none';
  imgBtn.classList.remove('active');
  checkForLink();
}

function updateAvatar(name) {
  const initials = (name || 'AN').trim().split(/\s+/).map(w => w[0] || '').join('').substring(0, 2).toUpperCase() || 'AN';
  document.getElementById('avatarInitials').textContent = initials;
}

function extractUrl(text) {
  const m = text.match(/https?:\/\/[^\s<>"']+/i);
  return m ? m[0].replace(/[.,;:!?)]+$/, '') : null;
}

async function checkForLink() {
  if (imgInput.files.length > 0) { linkPreviewEl.style.display = 'none'; return; }
  const url = extractUrl(bodyEl.value);
  if (!url) { linkPreviewEl.style.display = 'none'; lastUrl = null; return; }
  if (url === lastUrl) return;
  if (userClearedLink) return;
  lastUrl = url;

  linkPreviewEl.style.display = 'block';
  linkCard.innerHTML = '<div class="link-loading">Henter preview...</div>';

  try {
    const res = await fetch('{{ url("/simulation/posts/link-preview") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify({ url })
    });
    const data = await res.json();
    if (!data) { linkCard.innerHTML = '<div class="link-loading">Kunne ikke hente preview.</div>'; return; }
    linkCard.innerHTML = `
      <div class="link-card">
        ${data.image ? `<img src="${escapeAttr(data.image)}" onerror="this.style.display='none'">` : ''}
        <div class="link-meta">
          <div class="site">${escapeHtml(data.site_name || '')}</div>
          <div class="title">${escapeHtml(data.title || url)}</div>
          <div class="desc">${escapeHtml(data.description || '')}</div>
        </div>
      </div>`;
  } catch {
    linkCard.innerHTML = '<div class="link-loading">Kunne ikke hente preview.</div>';
  }
}

function clearLinkPreview(byUser) {
  linkPreviewEl.style.display = 'none';
  if (byUser) userClearedLink = true;
}

bodyEl.addEventListener('input', () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(checkForLink, 500);
});

function escapeHtml(s) { return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
function escapeAttr(s) { return escapeHtml(s); }
</script>
@endsection
