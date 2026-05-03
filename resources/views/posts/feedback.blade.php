@extends('layouts.app')
@section('content')

@php $_isOwn = $post->user_id === auth()->id(); @endphp

<style>
.posts-tabs { display: flex; gap: 4px; align-items: center; }
.posts-tabs a { padding: 8px 14px; border-radius: 8px; font-weight: 600; color: #65676b; text-decoration: none; font-size: 15px; }
.posts-tabs a.active { background: #e7f3ff; color: #1877f2; }
.posts-tabs a:hover:not(.active) { background: #f0f2f5; color: #1c1e21; }
</style>

<div class="view-header">
  <div class="posts-tabs">
    <a href="{{ url('/slophub/posts') }}" class="{{ $_isOwn ? 'active' : '' }}">Mine opslag</a>
    <a href="{{ url('/slophub/posts/all') }}" class="{{ $_isOwn ? '' : 'active' }}">Alles opslag</a>
  </div>
  <a href="{{ url('/slophub/posts/create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nyt opslag</a>
</div>

<h2 style="font-size: 15px; font-weight: 600; color: #65676b; margin-bottom: 12px;">Feedback</h2>

<style>
.fb-layout { display: grid; grid-template-columns: minmax(320px, 480px) 1fr; gap: 20px; align-items: start; }
@media (max-width: 960px) { .fb-layout { grid-template-columns: 1fr; } }
.fb-post { background: #fff; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); overflow: hidden; }
.fb-post .pc-head { display: flex; gap: 10px; align-items: center; padding: 12px 16px 0; }
.fb-post .pc-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #a1c4fd, #c2e9fb); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
.fb-post .pc-avatar-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.fb-post .pc-meta { font-weight: 600; }
.fb-post .pc-meta small { display: block; color: #65676b; font-weight: 400; font-size: 12px; }
.fb-post .pc-body { padding: 10px 16px 14px; line-height: 1.45; white-space: pre-wrap; font-size: 14px; }
.fb-post .pc-img { display: block; width: 100%; max-height: 520px; object-fit: cover; }
.fb-post .pc-link { display: flex; border-top: 1px solid #f0f2f5; text-decoration: none; color: inherit; }
.fb-post .pc-link img { width: 160px; object-fit: cover; flex-shrink: 0; background: #e4e6eb; }
.fb-post .pc-link .lm { padding: 12px 14px; flex: 1; min-width: 0; }
.fb-post .pc-link .site { font-size: 11px; color: #65676b; text-transform: uppercase; }
.fb-post .pc-link .title { font-weight: 600; font-size: 15px; margin: 2px 0; line-height: 1.3; }
.fb-post .pc-link .desc { font-size: 13px; color: #65676b; line-height: 1.35; }

.fb-panel { background: #fff; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; }
.fb-panel-head { padding: 12px 16px; border-bottom: 1px solid #dadde1; font-weight: 700; font-size: 15px; color: #1c1e21; }
.fb-panel-head small { display: block; font-weight: 400; font-size: 12px; color: #65676b; margin-top: 2px; }
.fb-comments { padding: 14px 16px 6px; display: flex; flex-direction: column; gap: 12px; }
.fb-comment { display: flex; gap: 10px; align-items: flex-start; }
.fb-comment .av { width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0; background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: #65676b; overflow: hidden; }
.fb-comment .av img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
.fb-comment .content { flex: 1; min-width: 0; }
.fb-comment .bubble { background: #f0f2f5; padding: 8px 12px; border-radius: 14px; display: inline-block; max-width: 100%; }
.fb-comment .who { font-size: 13px; margin-bottom: 2px; }
.fb-comment .who strong { font-weight: 600; color: #050505; }
.fb-comment .body { font-size: 14px; line-height: 1.4; color: #1c1e21; word-wrap: break-word; white-space: pre-wrap; }
.fb-comment .time { font-size: 11px; color: #65676b; margin-top: 4px; padding-left: 12px; }
.teacher-badge { display: inline-block; margin-left: 6px; padding: 1px 7px; background: #7c3aed; color: #fff; font-size: 10px; font-weight: 700; letter-spacing: 0.3px; border-radius: 8px; vertical-align: middle; text-transform: uppercase; }
.fb-empty { color: #65676b; font-size: 13px; padding: 18px 4px; }
.fb-input { display: flex; gap: 8px; padding: 12px 16px; border-top: 1px solid #f0f2f5; background: #fff; }
.fb-input textarea { flex: 1; min-height: 40px; max-height: 160px; padding: 9px 14px; border: 1px solid #dadde1; border-radius: 18px; resize: none; font-family: inherit; font-size: 14px; }
.fb-input button { background: #1877f2; color: #fff; border: none; border-radius: 18px; padding: 0 16px; cursor: pointer; font-weight: 600; font-size: 13px; font-family: inherit; }
.fb-input button:disabled { opacity: 0.5; cursor: not-allowed; }
</style>

<div class="fb-layout">
  <div class="fb-post">
    <div class="pc-head">
      @php $_img = $post->currentAuthorImage(); $_name = $post->currentAuthorName(); @endphp
      @if ($_img)
        <img src="{{ Storage::url($_img) }}" class="pc-avatar-img">
      @else
        <div class="pc-avatar">{{ strtoupper(substr($_name, 0, 2)) }}</div>
      @endif
      <div class="pc-meta">
        {{ $_name }}
        <small>{{ $post->created_at->diffForHumans() }} · Runde {{ $post->round }}</small>
      </div>
    </div>
    <div class="pc-body">{!! preg_replace('#(https?://[^\s<>"\']+)#i', '<a href="$1" target="_blank" rel="noopener" style="color:#1877f2;word-break:break-all;">$1</a>', e($post->body)) !!}</div>
    @if ($post->image_path)
      <img class="pc-img" src="{{ Storage::url($post->image_path) }}" alt="">
    @elseif ($post->link_url)
      <a class="pc-link" href="{{ $post->link_url }}" target="_blank" rel="noopener">
        @if ($post->link_image)<img src="{{ $post->link_image }}" onerror="this.style.display='none'">@endif
        <div class="lm">
          <div class="site">{{ $post->link_site_name }}</div>
          <div class="title">{{ $post->link_title ?? $post->link_url }}</div>
          <div class="desc">{{ $post->link_description }}</div>
        </div>
      </a>
    @endif
  </div>

  <div class="fb-panel">
    <div class="fb-panel-head">
      Feedback
      <small>Kursister og underviser kommenterer dette opslag</small>
    </div>
    <div class="fb-comments" id="fbComments">
      @if (empty($messages))
        <div class="fb-empty">Ingen kommentarer endnu. Vær den første til at give feedback.</div>
      @else
        @foreach ($messages as $m)
          @include('posts._feedback_message', ['m' => $m])
        @endforeach
      @endif
    </div>
    <form class="fb-input" onsubmit="return fbSend(event)">
      <textarea id="fbInput" placeholder="Skriv en kommentar..." rows="1"
        onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault();fbSend(event);}"></textarea>
      <button type="submit" id="fbSendBtn" title="Send">Kommentér</button>
    </form>
  </div>
</div>

<script>
(function () {
  const FETCH_URL = '{{ url('/slophub/posts/'.$post->id.'/feedback/data') }}';
  const SEND_URL = '{{ url('/slophub/posts/'.$post->id.'/feedback') }}';
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;
  const listEl = document.getElementById('fbComments');
  const input = document.getElementById('fbInput');
  const sendBtn = document.getElementById('fbSendBtn');
  let sending = false;
  const seenIds = new Set();

  function escapeHtml(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

  function renderComment(m) {
    const initials = escapeHtml((m.user.name || '?').slice(0, 2).toUpperCase());
    const av = m.user.image_url
      ? `<div class="av"><img src="${escapeHtml(m.user.image_url)}" alt=""></div>`
      : `<div class="av">${initials}</div>`;
    const badge = m.user.is_instructor ? '<span class="teacher-badge">Underviser</span>' : '';
    return `<div class="fb-comment" data-id="${m.id}">
      ${av}
      <div class="content">
        <div class="bubble">
          <div class="who"><strong>${escapeHtml(m.user.name)}</strong>${badge}</div>
          <div class="body">${escapeHtml(m.body)}</div>
        </div>
        <div class="time">${escapeHtml(m.created_at_human)}</div>
      </div>
    </div>`;
  }

  function appendComment(m) {
    if (seenIds.has(m.id)) return;
    seenIds.add(m.id);
    const empty = listEl.querySelector('.fb-empty');
    if (empty) empty.remove();
    listEl.insertAdjacentHTML('beforeend', renderComment(m));
  }

  listEl.querySelectorAll('.fb-comment[data-id]').forEach(el => seenIds.add(parseInt(el.dataset.id)));

  window.fbSend = async function (ev) {
    ev.preventDefault();
    if (sending) return false;
    const body = input.value.trim();
    if (!body) return false;
    sending = true;
    sendBtn.disabled = true;
    try {
      const res = await fetch(SEND_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ body }),
      });
      if (!res.ok) throw new Error('status ' + res.status);
      const data = await res.json();
      input.value = '';
      appendComment(data.message);
    } catch (e) {
      alert('Kunne ikke sende. Prøv igen.');
    } finally {
      sending = false;
      sendBtn.disabled = false;
      input.focus();
    }
    return false;
  };

  async function poll() {
    try {
      const res = await fetch(FETCH_URL);
      const data = await res.json();
      (data.messages || []).forEach(appendComment);
    } catch {}
  }
  setInterval(poll, 5000);
  input.focus();
})();
</script>

@endsection
