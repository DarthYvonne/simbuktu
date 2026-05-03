@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1><a href="{{ url('/simulation/posts') }}" style="color: #1877f2;">← Mine opslag</a></h1>
  <div style="display: flex; gap: 8px; align-items: center;">
    <span id="liveBadge" style="display: inline-flex; align-items: center; gap: 8px; background: #e7f3ff; color: #1877f2; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 600;">
      <span style="display: inline-block; width: 8px; height: 8px; background: #22c55e; border-radius: 50%; animation: pulse 1.5s infinite;"></span>
      <span id="roundLabel">Runde {{ $post->round }}</span> · <span id="statusLabel">{{ $post->statusLabel() }}</span>
    </span>
    <form method="POST" action="{{ url('/simulation/posts/'.$post->id) }}" onsubmit="return confirm('Slet opslag?')">
      @csrf
      @method('DELETE')
      <button class="btn btn-danger">Slet</button>
    </form>
  </div>
</div>

<style>
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }
@keyframes slideIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
.new-comment { animation: slideIn 0.4s ease; }
.comment { display: flex; gap: 10px; padding: 10px 0; }
.comment .avatar { width: 36px; height: 36px; border-radius: 50%; background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; color: #65676b; flex-shrink: 0; }
.comment .avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
.bubble { background: #f0f2f5; padding: 8px 14px; border-radius: 14px; line-height: 1.4; }
.bubble .author { font-weight: 600; font-size: 13px; }
.bubble .author a { color: #050505; }
.bubble .author a:hover { text-decoration: underline; }
.cmeta { font-size: 11px; color: #65676b; padding: 2px 12px; }
.thread { margin-left: 32px; border-left: 2px solid #e4e6eb; padding-left: 14px; }
.comment.student .bubble { background: #e7f3ff; }
.student-badge { display: inline-block; margin-left: 6px; padding: 1px 6px; background: #1877f2; color: #fff; font-size: 10px; font-weight: 700; letter-spacing: 0.3px; border-radius: 8px; vertical-align: middle; }
.reply-link { display: inline-block; margin-left: 8px; font-size: 11px; color: #1877f2; cursor: pointer; font-weight: 600; }
.reply-link:hover { text-decoration: underline; }
.reply-form { margin-top: 6px; display: flex; gap: 6px; }
.reply-form input { flex: 1; padding: 6px 10px; border: 1px solid #dadde1; border-radius: 14px; font-size: 12px; font-family: inherit; }
.reply-form button { padding: 4px 10px; border: none; background: #1877f2; color: #fff; border-radius: 14px; cursor: pointer; font-size: 12px; font-weight: 600; }
.comment-input { padding: 10px 0 0; border-top: 1px solid #f0f2f5; margin-top: 10px; }
.comment-input form { display: flex; gap: 8px; }
.comment-input input { flex: 1; padding: 8px 14px; border: 1px solid #dadde1; border-radius: 20px; font-size: 13px; font-family: inherit; }
.comment-input button { padding: 6px 16px; border: none; background: #1877f2; color: #fff; border-radius: 20px; cursor: pointer; font-size: 13px; font-weight: 600; }
.metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.metric-card { background: #f8f9fa; padding: 10px 14px; border-radius: 8px; text-align: center; }
.metric-card .num { font-size: 22px; font-weight: 700; color: #1877f2; }
.metric-card .lbl { font-size: 11px; color: #65676b; text-transform: uppercase; }
</style>

<style>
.post-col { max-width: 450px; }
.attach-img { max-height: 500px; width: 100%; object-fit: cover; border-radius: 8px; margin-top: 10px; display: block; cursor: zoom-in; }
.lightbox { position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 9999; display: none; align-items: center; justify-content: center; cursor: zoom-out; }
.lightbox.open { display: flex; }
.lightbox img { max-width: 95vw; max-height: 95vh; object-fit: contain; border-radius: 4px; }
.lightbox .close { position: absolute; top: 16px; right: 20px; background: rgba(255,255,255,0.15); color: #fff; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 18px; }
.link-card-view { display: flex; border: 1px solid #dadde1; border-radius: 8px; margin-top: 10px; overflow: hidden; text-decoration: none; color: inherit; }
.link-card-view:hover { background: #f8f9fa; }
.link-card-view img { width: 180px; object-fit: cover; flex-shrink: 0; background: #e4e6eb; }
.link-card-view .lm { padding: 12px 14px; flex: 1; min-width: 0; }
.link-card-view .site { font-size: 11px; color: #65676b; text-transform: uppercase; }
.link-card-view .title { font-weight: 600; font-size: 15px; margin: 2px 0; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.link-card-view .desc { font-size: 13px; color: #65676b; line-height: 1.35; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
</style>

<div class="post-col">

<div class="card">
  <div style="display: flex; gap: 12px;">
    @php $_img = $post->currentAuthorImage(); $_name = $post->currentAuthorName(); @endphp
    @if ($_img)
      <img src="{{ Storage::url($_img) }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
    @else
      <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #a1c4fd, #c2e9fb); display: flex; align-items: center; justify-content: center; font-weight: 700;">{{ strtoupper(substr($_name, 0, 2)) }}</div>
    @endif
    <div style="flex: 1;">
      <div style="font-weight: 600;">{{ $_name }} <span style="color: #65676b; font-weight: 400; font-size: 12px;">· deltager · {{ $post->created_at->diffForHumans() }}</span></div>
      <div style="margin: 8px 0; line-height: 1.45; white-space: pre-wrap;">{!! preg_replace('#(https?://[^\s<>"\']+)#i', '<a href="$1" target="_blank" rel="noopener" style="color:#1877f2;word-break:break-all;">$1</a>', e($post->body)) !!}</div>

      @if ($post->image_path)
        <img class="attach-img" id="postImg" src="{{ Storage::url($post->image_path) }}" alt="" onclick="document.getElementById('lightbox').classList.add('open')">
      @elseif ($post->link_url)
        <a class="link-card-view" href="{{ $post->link_url }}" target="_blank" rel="noopener">
          @if ($post->link_image)
            <img src="{{ $post->link_image }}" onerror="this.style.display='none'">
          @endif
          <div class="lm">
            <div class="site">{{ $post->link_site_name }}</div>
            <div class="title">{{ $post->link_title ?? $post->link_url }}</div>
            <div class="desc">{{ $post->link_description }}</div>
          </div>
        </a>
      @endif

      @php
        $reactionIcon = ['like'=>'thumbs-up','love'=>'heart','haha'=>'face-laugh-squint','wow'=>'face-surprise','sad'=>'face-sad-tear','angry'=>'face-angry'];
        $reactionBg = ['like'=>'#1877f2','love'=>'#e0245e','haha'=>'#f7b928','wow'=>'#f7b928','sad'=>'#3b82f6','angry'=>'#e9710f'];
      @endphp
      <div style="display: flex; justify-content: space-between; margin-top: 12px; padding-top: 10px; border-top: 1px solid #f0f2f5; font-size: 13px; color: #65676b;">
        <span>
          @if ($post->reactionTotal() > 0)
            <span style="display: inline-flex; align-items: center;">
              @foreach ($post->topReactions(3) as $type)
                <i class="fa-solid fa-{{ $reactionIcon[$type] ?? 'thumbs-up' }}" style="color: {{ $reactionBg[$type] ?? '#1877f2' }}; font-size: 14px; margin-right: 4px;"></i>
              @endforeach
              <span id="reactTotal" style="margin-left: 8px;">{{ $post->reactionTotal() }}</span>
            </span>
          @endif
        </span>
        <span>
          <span id="reachLabel">Set af {{ $post->reach }}</span>
          · <span id="ccountLabel">{{ $post->comments->count() }}</span> kommentarer
          · <span id="sharesLabel">{{ $post->shares ?? 0 }}</span> delinger
        </span>
      </div>
    </div>
  </div>
</div>


<div class="card">
  <div id="comments"></div>
  <div id="empty" style="text-align: center; padding: 30px; color: #65676b; display: {{ $post->comments->count() === 0 ? 'block' : 'none' }};">
    Ingen kommentarer endnu.
  </div>
  @if ($canComment)
    <div class="comment-input">
      <form onsubmit="return submitComment(event)">
        <input type="text" name="body" placeholder="Skriv en kommentar..." maxlength="2000" required>
        <button type="submit">Send</button>
      </form>
    </div>
  @endif
</div>

</div><!-- /post-col -->

@if ($post->image_path)
<div class="lightbox" id="lightbox" onclick="this.classList.remove('open')">
  <button class="close" onclick="event.stopPropagation(); document.getElementById('lightbox').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
  <img src="{{ Storage::url($post->image_path) }}" alt="">
</div>
<script>
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') document.getElementById('lightbox').classList.remove('open'); });
</script>
@endif

<script>
const POST_ID = {{ $post->id }};
const FEED_URL = '{{ url("/simulation/posts/$post->id/feed") }}';
const PERSONA_URL = '{{ url("/simulation/admin/personas") }}';
let lastSeenId = 0;
const seenIds = new Set();

function avatar(personaId, name) {
  return `<div class="avatar"><img src="${PERSONA_URL}/${personaId}/image" onerror="this.outerHTML='${(name||'??').substring(0,2).toUpperCase()}'"></div>`;
}

function renderComment(c, isNew) {
  const classes = ['comment'];
  if (isNew) classes.push('new-comment');
  if (c.from_student) classes.push('student');
  const indent = c.parent_id ? 'thread' : '';
  const badge = c.from_student ? '<span class="student-badge">Studerende</span>' : '';
  const av = c.from_student
    ? `<div class="avatar" style="background:#1877f2;color:#fff;">${(c.persona_name||'??').substring(0,2).toUpperCase()}</div>`
    : avatar(c.persona_id, c.persona_name);
  const authorLine = c.from_student
    ? `<div class="author">${escapeHtml(c.persona_name)}${badge}</div>`
    : `<div class="author"><a href="${PERSONA_URL}/${c.persona_id}" target="_blank">${escapeHtml(c.persona_name)}</a></div>`;
  const replyLink = CAN_COMMENT && !c.parent_id ? `<span class="reply-link" onclick="openReply(${c.id})">Svar</span>` : '';
  return `
    <div class="${indent}">
      <div class="${classes.join(' ')}" id="c-${c.id}">
        ${av}
        <div style="flex: 1;">
          <div class="bubble">
            ${authorLine}
            <div>${escapeHtml(c.body)}</div>
          </div>
          <div class="cmeta">Runde ${c.round} · ${new Date(c.created_at).toLocaleTimeString('da-DK')}${replyLink}</div>
        </div>
      </div>
    </div>`;
}

function escapeHtml(s) {
  return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

async function poll() {
  try {
    const res = await fetch(`${FEED_URL}?since=${lastSeenId}`);
    const data = await res.json();
    document.getElementById('reachLabel').textContent = 'Set af ' + data.post.reach;
    document.getElementById('ccountLabel').textContent = data.post.comment_count;
    document.getElementById('roundLabel').textContent = 'Runde ' + data.post.round;
    document.getElementById('statusLabel').textContent = ({draft:'Kladde',active:'Aktivt',finished:'Dødt'})[data.post.status] || data.post.status;

    if (data.comments.length > 0) {
      document.getElementById('empty').style.display = 'none';
      const wrap = document.getElementById('comments');
      data.comments.forEach((c, i) => {
        if (seenIds.has(c.id)) return;
        seenIds.add(c.id);
        const el = document.createElement('div');
        el.innerHTML = renderComment(c, true);
        // stagger
        setTimeout(() => wrap.appendChild(el.firstElementChild), i * 250);
        lastSeenId = Math.max(lastSeenId, c.id);
      });
    }
  } catch (e) { console.warn(e); }
}

const CAN_COMMENT = @json($canComment);
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

async function submitComment(event, parentId) {
  event.preventDefault();
  const form = event.target;
  const input = form.querySelector('input[name=body]');
  const body = input.value.trim();
  if (!body) return false;
  const fd = new FormData();
  fd.append('_token', CSRF_TOKEN);
  fd.append('body', body);
  if (parentId) fd.append('parent_id', parentId);
  try {
    const res = await fetch(`/simulation/posts/${POST_ID}/comments`, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: fd,
    });
    if (!res.ok) throw new Error('status ' + res.status);
    input.value = '';
    await poll();
  } catch (e) { alert('Kunne ikke sende kommentaren.'); }
  return false;
}

function openReply(parentId) {
  const existing = document.getElementById('reply-form-' + parentId);
  if (existing) { existing.querySelector('input').focus(); return; }
  const wrapper = document.getElementById('c-' + parentId);
  if (!wrapper) return;
  const form = document.createElement('form');
  form.className = 'reply-form';
  form.id = 'reply-form-' + parentId;
  form.innerHTML = `<input type="text" name="body" placeholder="Svar..." maxlength="2000" required><button type="submit">Send</button>`;
  form.onsubmit = e => submitComment(e, parentId);
  wrapper.querySelector('.cmeta').insertAdjacentElement('afterend', form);
  form.querySelector('input').focus();
}

// Initial render of existing comments
@foreach ($post->comments as $c)
  seenIds.add({{ $c->id }});
  lastSeenId = Math.max(lastSeenId, {{ $c->id }});
  document.getElementById('comments').insertAdjacentHTML('beforeend', renderComment({
    id: {{ $c->id }},
    parent_id: {{ $c->parent_id ?? 'null' }},
    persona_id: @json($c->persona_id),
    persona_name: @json($c->persona_name),
    body: @json($c->body),
    round: {{ $c->round }},
    created_at: @json($c->created_at->toIso8601String()),
    from_student: {{ $c->user_id ? 'true' : 'false' }}
  }, false));
@endforeach

setInterval(poll, 5000);
</script>
@endsection
