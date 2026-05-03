@extends('layouts.app')
@section('content')

<style>
.posts-grid { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-start; }
.post-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); overflow: hidden; display: block; color: inherit; width: 450px; max-width: 100%; flex: 0 0 450px; }
@media (max-width: 960px) { .post-card { flex: 1 1 100%; width: 100%; } }
.comments-toggle { cursor: pointer; user-select: none; }
.comments-toggle:hover { text-decoration: underline; }
.comments-list { border-top: 1px solid #f0f2f5; padding: 10px 16px; display: none; }
.comments-list.open { display: block; }
.pc-comment { display: flex; gap: 10px; padding: 8px 0; }
.pc-comment .av { width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: #65676b; overflow: hidden; }
.pc-comment .av img { width: 100%; height: 100%; object-fit: cover; }
.pc-comment .bubble { background: #f0f2f5; padding: 8px 12px; border-radius: 14px; font-size: 13px; line-height: 1.4; }
.pc-comment .bubble .nm { font-weight: 600; }
.pc-comment .bubble .nm a { color: #050505; }
.pc-comment .cm-meta { font-size: 11px; color: #65676b; padding: 2px 12px; }
.pc-comment.thread { margin-left: 28px; border-left: 2px solid #e4e6eb; padding-left: 10px; }
.why-name { cursor: pointer; border-bottom: 1px dotted rgba(24,119,242,0.35); }
.why-name:hover { border-bottom-color: #1877f2; }
#whyBubble { position: fixed; background: #1c1e21; color: #fff; padding: 8px 12px; border-radius: 8px; font-size: 12px; max-width: 280px; line-height: 1.4; z-index: 9999; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); pointer-events: none; transform: translateY(-100%); }
#whyBubble::after { content: ''; position: absolute; bottom: -6px; left: 16px; border-left: 6px solid transparent; border-right: 6px solid transparent; border-top: 6px solid #1c1e21; }
#whyBubble.via_friend { border-left: 3px solid #22c55e; }
#whyBubble.discovery { border-left: 3px solid #9ca3af; }
.slet-link { color: #b91c1c; font-size: 12px; font-weight: 600; text-decoration: none; padding: 4px 8px; border-radius: 4px; }
.slet-link:hover { background: #fee2e2; }
.feedback-link { color: #1877f2; font-size: 12px; font-weight: 600; text-decoration: none; padding: 4px 8px; border-radius: 4px; }
.feedback-link:hover { background: #e7f3ff; }
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9998; display: none; align-items: center; justify-content: center; padding: 20px; }
.modal-backdrop.open { display: flex; }
.modal-box { background: #fff; border-radius: 12px; max-width: 400px; width: 100%; padding: 20px 22px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
.modal-box h3 { margin-bottom: 10px; font-size: 16px; }
.modal-box p { font-size: 13px; color: #65676b; line-height: 1.5; margin-bottom: 6px; }
.modal-box .snippet-box { background: #f0f2f5; padding: 10px 12px; border-radius: 8px; font-size: 13px; font-style: italic; color: #1c1e21; margin: 10px 0; }
.modal-box .actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px; }
.post-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
.pc-head { display: flex; gap: 10px; align-items: center; padding: 12px 16px 0; }
.pc-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #a1c4fd, #c2e9fb); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
.pc-avatar-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.pc-meta { font-weight: 600; flex: 1; }
.pc-meta small { display: block; color: #65676b; font-weight: 400; font-size: 12px; }
.pc-status { font-size: 11px; padding: 3px 9px; border-radius: 10px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.4px; }
.pc-status.active { background: #dcfce7; color: #166534; }
.pc-status.finished { background: #f0f2f5; color: #65676b; }
.pc-status.draft { background: #fef3c7; color: #92400e; }
.pc-body { padding: 8px 16px 12px; line-height: 1.45; white-space: pre-wrap; }
.pc-img { display: block; width: 100%; max-height: 480px; object-fit: cover; }
.pc-link { display: flex; border-top: 1px solid #f0f2f5; border-bottom: 1px solid #f0f2f5; }
.pc-link img { width: 160px; object-fit: cover; flex-shrink: 0; background: #e4e6eb; }
.pc-link .lm { padding: 12px 14px; flex: 1; min-width: 0; }
.pc-link .site { font-size: 11px; color: #65676b; text-transform: uppercase; }
.pc-link .title { font-weight: 600; font-size: 15px; margin: 2px 0; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.pc-link .desc { font-size: 13px; color: #65676b; line-height: 1.35; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.pc-stats { display: flex; justify-content: space-between; padding: 10px 16px; font-size: 13px; color: #65676b; border-top: 1px solid #f0f2f5; }
.pc-stats .reactions { display: inline-flex; align-items: center; gap: 4px; }
.pc-stats .reactions .emoji { display: inline-flex; }
.pc-stats .reactions .emoji i { font-size: 14px; margin-right: 4px; }
.empty-feed { background: #fff; border-radius: 12px; padding: 60px 20px; text-align: center; color: #65676b; }
</style>

@php
$reactionIcon = ['like'=>'thumbs-up','love'=>'heart','haha'=>'face-laugh-squint','wow'=>'face-surprise','sad'=>'face-sad-tear','angry'=>'face-angry'];
$reactionBg = ['like'=>'#1877f2','love'=>'#e0245e','haha'=>'#f7b928','wow'=>'#f7b928','sad'=>'#3b82f6','angry'=>'#e9710f'];
@endphp

<style>
.posts-tabs { display: flex; gap: 4px; align-items: center; }
.posts-tabs a { padding: 8px 14px; border-radius: 8px; font-weight: 600; color: #65676b; text-decoration: none; font-size: 15px; }
.posts-tabs a.active { background: #e7f3ff; color: #1877f2; }
.posts-tabs a:hover:not(.active) { background: #f0f2f5; color: #1c1e21; }
.posts-filterbar { display: flex; gap: 10px; align-items: center; margin-bottom: 14px; flex-wrap: wrap; }
.posts-filterbar input[type=search], .posts-filterbar select { padding: 8px 12px; border: 1px solid #dadde1; border-radius: 8px; font-size: 14px; font-family: inherit; background: #fff; }
.posts-filterbar input[type=search] { flex: 1; min-width: 220px; }
.posts-filterbar select { min-width: 180px; }
.posts-filterbar .spacer { flex: 1; }
</style>

<div class="view-header">
  <div class="posts-tabs">
    <a href="{{ url('/slophub/posts') }}" class="{{ ($view ?? 'mine') === 'mine' ? 'active' : '' }}">Mine opslag</a>
    <a href="{{ url('/slophub/posts/all') }}" class="{{ ($view ?? 'mine') === 'all' ? 'active' : '' }}">Alles opslag</a>
  </div>
  <a href="{{ url('/slophub/posts/create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nyt opslag</a>
</div>

@if (($view ?? 'mine') === 'all')
  <div class="posts-filterbar">
    <input type="search" id="postsSearch" placeholder="Søg i opslag...">
    <select id="postsParticipant">
      <option value="">Alle deltagere</option>
      @foreach ($participants as $p)
        <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
      @endforeach
    </select>
  </div>
@endif

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($posts->isEmpty())
  <div class="empty-feed">
    <h3 style="color: #1c1e21; margin-bottom: 8px;">Ingen opslag endnu</h3>
    @if (($view ?? 'mine') === 'all')
      <p>Ingen på kurset har skrevet et opslag endnu.</p>
    @else
      <p>Skriv dit første for at starte en simulering.<br><br><a href="{{ url('/slophub/posts/create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nyt opslag</a></p>
    @endif
  </div>
@else
  <div class="posts-grid">
  @foreach ($posts as $post)
    @php $_img = $post->currentAuthorImage(); $_name = $post->currentAuthorName(); @endphp
    <div class="post-card" data-user-id="{{ $post->user_id }}" data-search="{{ mb_strtolower($_name.' '.$post->body) }}">
      <div class="pc-head">
        @if ($_img)
          <img src="{{ Storage::url($_img) }}" class="pc-avatar-img">
        @else
          <div class="pc-avatar">{{ strtoupper(substr($_name, 0, 2)) }}</div>
        @endif
        <div class="pc-meta">
          {{ $_name }}
          <small>{{ $post->created_at->diffForHumans() }} · Runde {{ $post->round }}</small>
        </div>
        <a href="{{ url('/slophub/posts/'.$post->id.'/feedback') }}" class="feedback-link">Feedback</a>
        @if ($post->user_id === auth()->id() || auth()->user()->is_admin)
          <a href="#" class="slet-link" onclick="event.preventDefault(); openDelete({{ $post->id }}, {{ json_encode(\Illuminate\Support\Str::limit($post->body, 60)) }})">Slet</a>
        @endif
      </div>

      <div class="pc-body">{!! preg_replace('#(https?://[^\s<>"\']+)#i', '<span style="color:#1877f2;">$1</span>', e(\Illuminate\Support\Str::limit($post->body, 280))) !!}</div>

      @if ($post->image_path)
        <img class="pc-img" src="{{ Storage::url($post->image_path) }}" alt="" onclick="openLightbox(this.src)" style="cursor: zoom-in;">
      @elseif ($post->link_url)
        <div class="pc-link">
          @if ($post->link_image)<img src="{{ $post->link_image }}" onerror="this.style.display='none'">@endif
          <div class="lm">
            <div class="site">{{ $post->link_site_name }}</div>
            <div class="title">{{ $post->link_title ?? $post->link_url }}</div>
            <div class="desc">{{ $post->link_description }}</div>
          </div>
        </div>
      @endif

      <div class="pc-stats">
        <span class="reactions" onclick="if({{ $post->reactionTotal() }}) openReactions({{ $post->id }})" style="cursor: {{ $post->reactionTotal() > 0 ? 'pointer' : 'default' }};">
          @if ($post->reactionTotal() > 0)
            <span class="emoji">
              @foreach ($post->topReactions(3) as $type)
                <i class="fa-solid fa-{{ $reactionIcon[$type] ?? 'thumbs-up' }}" style="color: {{ $reactionBg[$type] ?? '#1877f2' }};"></i>
              @endforeach
            </span>
            <span style="margin-left: 6px;">{{ $post->reactionTotal() }}</span>
          @endif
        </span>
        <span>
          Set af {{ $post->reach }}
          · <span class="comments-toggle" onclick="toggleComments({{ $post->id }})">{{ $post->comments()->count() }} kommentarer</span>
          · {{ $post->shares ?? 0 }} delinger
        </span>
      </div>
      <div class="comments-list" id="comments-{{ $post->id }}" data-loaded="0"></div>
    </div>
  @endforeach
  </div>
@endif

<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <button class="close" onclick="event.stopPropagation(); closeLightbox()"><i class="fa-solid fa-xmark"></i></button>
  <img id="lightboxImg" src="" alt="">
</div>

<div id="whyBubble"></div>

<div class="modal-backdrop" id="reactionsModal" onclick="if(event.target===this) closeReactions()">
  <div class="rx-box">
    <div class="rx-head">
      <h3 id="rxTitle">Reaktioner</h3>
      <button type="button" class="rx-close" onclick="closeReactions()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="rx-tabs" id="rxTabs"></div>
    <div class="rx-list" id="rxList"></div>
  </div>
</div>

<style>
.rx-box { background: #fff; border-radius: 12px; width: 100%; max-width: 420px; max-height: 80vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
.rx-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #f0f2f5; }
.rx-head h3 { font-size: 17px; font-weight: 700; }
.rx-close { background: none; border: none; font-size: 16px; color: #65676b; cursor: pointer; width: 32px; height: 32px; border-radius: 50%; }
.rx-close:hover { background: #f0f2f5; }
.rx-tabs { display: flex; border-bottom: 1px solid #f0f2f5; overflow-x: auto; }
.rx-tabs button { flex-shrink: 0; background: none; border: none; padding: 10px 14px; cursor: pointer; border-bottom: 3px solid transparent; color: #65676b; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 6px; font-family: inherit; }
.rx-tabs button.active { color: #1877f2; border-bottom-color: #1877f2; }
.rx-tabs button i { font-size: 14px; }
.rx-list { padding: 10px 16px; overflow-y: auto; flex: 1; }
.rx-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f8f9fa; text-decoration: none; color: inherit; }
.rx-item:last-child { border-bottom: none; }
.rx-item:hover { background: #f8f9fa; }
.rx-item .av { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.rx-item .av.ph { background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: #65676b; }
.rx-item .nm { font-weight: 600; font-size: 14px; }
.rx-item .meta { color: #65676b; font-size: 12px; }
.rx-item .badge { margin-left: auto; font-size: 16px; }
</style>

<div class="modal-backdrop" id="deleteModal" onclick="if(event.target===this) closeDelete()">
  <div class="modal-box">
    <h3>Slet opslag?</h3>
    <p>Du er ved at slette dette opslag:</p>
    <div class="snippet-box" id="deleteSnippet"></div>
    <p>Alle kommentarer, reaktioner og engagement-data slettes også. Dette kan ikke fortrydes.</p>
    <div class="actions">
      <button type="button" class="btn btn-secondary" onclick="closeDelete()">Annuller</button>
      <form id="deleteForm" method="POST" action="" style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Slet opslag</button>
      </form>
    </div>
  </div>
</div>

<style>
.lightbox { position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 9999; display: none; align-items: center; justify-content: center; cursor: zoom-out; }
.lightbox.open { display: flex; }
.lightbox img { max-width: 95vw; max-height: 95vh; object-fit: contain; border-radius: 4px; }
.lightbox .close { position: absolute; top: 16px; right: 20px; background: rgba(255,255,255,0.15); color: #fff; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 18px; }
</style>
<script>
function openLightbox(src) { document.getElementById('lightboxImg').src = src; document.getElementById('lightbox').classList.add('open'); }
function closeLightbox() { document.getElementById('lightbox').classList.remove('open'); }
function closeReactions() { document.getElementById('reactionsModal').classList.remove('open'); }

const RX_ICON = {like:'thumbs-up', love:'heart', haha:'face-laugh-squint', wow:'face-surprise', sad:'face-sad-tear', angry:'face-angry'};
const RX_COLOR = {like:'#1877f2', love:'#e0245e', haha:'#f7b928', wow:'#f7b928', sad:'#3b82f6', angry:'#e9710f'};

async function openReactions(postId) {
  const modal = document.getElementById('reactionsModal');
  document.getElementById('rxTabs').innerHTML = '<div style="padding:10px;color:#65676b;font-size:13px;">Indlæser...</div>';
  document.getElementById('rxList').innerHTML = '';
  modal.classList.add('open');
  try {
    const res = await fetch(`/slophub/posts/${postId}/reactions`);
    const data = await res.json();
    renderReactions(data);
  } catch { document.getElementById('rxTabs').innerHTML = '<div style="padding:10px;color:#b91c1c;font-size:13px;">Kunne ikke hente.</div>'; }
}

function renderReactions(data) {
  const tabs = document.getElementById('rxTabs');
  const list = document.getElementById('rxList');
  const allPersonas = [];
  Object.entries(data.personas).forEach(([type, ps]) => ps.forEach(p => allPersonas.push({...p, reaction: type})));

  const types = Object.entries(data.counts).filter(([t,c]) => c > 0).sort((a,b) => b[1]-a[1]);
  let html = `<button class="active" data-type="all">Alle <span style="color:#65676b;">${data.total}</span></button>`;
  types.forEach(([t, c]) => {
    html += `<button data-type="${t}"><i class="fa-solid fa-${RX_ICON[t]}" style="color:${RX_COLOR[t]};"></i> <span style="color:#65676b;">${c}</span></button>`;
  });
  tabs.innerHTML = html;

  const PERSONA_URL = '/slophub/profiler';
  const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  function renderList(type) {
    const items = type === 'all' ? allPersonas : (data.personas[type] || []).map(p => ({...p, reaction: type}));
    if (items.length === 0) { list.innerHTML = '<div style="padding:14px;color:#65676b;font-size:13px;text-align:center;">Ingen reaktioner.</div>'; return; }
    list.innerHTML = items.map(p => {
      const img = p.has_image ? `<img class="av" src="${PERSONA_URL}/${p.id}/thumb">` : `<div class="av ph">${escapeHtml((p.name||'?').substring(0,2).toUpperCase())}</div>`;
      const whyAttrs = p.why ? `class="nm why-name" data-why="${escapeHtml(p.why.text)}" data-type="${p.why.type}"` : 'class="nm"';
      return `<a class="rx-item" href="${PERSONA_URL}/${p.id}">${img}<div style="flex:1; min-width:0;"><div ${whyAttrs}>${escapeHtml(p.name)}</div><div class="meta">${p.age ? p.age + ', ' : ''}${escapeHtml(p.occupation || '')}</div></div><i class="fa-solid fa-${RX_ICON[p.reaction]} badge" style="color:${RX_COLOR[p.reaction]};"></i></a>`;
    }).join('');
  }
  renderList('all');
  tabs.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
    tabs.querySelectorAll('button').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    renderList(b.dataset.type);
  }));
}

function openDelete(postId, snippet) {
  document.getElementById('deleteSnippet').textContent = snippet;
  document.getElementById('deleteForm').action = '/slophub/posts/' + postId;
  document.getElementById('deleteModal').classList.add('open');
}
function closeDelete() { document.getElementById('deleteModal').classList.remove('open'); }

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeLightbox(); closeDelete(); closeReactions(); }
});

const whyBubble = document.getElementById('whyBubble');
document.addEventListener('mouseover', e => {
  const el = e.target.closest('.why-name');
  if (!el) return;
  whyBubble.textContent = el.dataset.why;
  whyBubble.className = el.dataset.type || '';
  const r = el.getBoundingClientRect();
  whyBubble.style.left = (r.left) + 'px';
  whyBubble.style.top = (r.top - 8) + 'px';
  whyBubble.style.display = 'block';
});
document.addEventListener('mouseout', e => {
  if (e.target.closest('.why-name')) whyBubble.style.display = 'none';
});

const openComments = new Set();
async function toggleComments(postId) {
  const wrap = document.getElementById('comments-' + postId);
  if (!wrap) return;
  if (wrap.classList.contains('open')) { wrap.classList.remove('open'); openComments.delete(postId); return; }
  wrap.classList.add('open');
  openComments.add(postId);
  await loadComments(postId);
}
async function loadComments(postId) {
  const wrap = document.getElementById('comments-' + postId);
  if (!wrap) return;
  try {
    const res = await fetch(`/slophub/posts/${postId}/feed`);
    const data = await res.json();
    renderComments(wrap, data.comments);
  } catch { wrap.innerHTML = '<div style="color:#b91c1c;padding:6px;font-size:12px;">Kunne ikke hente kommentarer.</div>'; }
}
function renderComments(wrap, comments) {
  if (!comments || comments.length === 0) { wrap.innerHTML = '<div style="color:#65676b;padding:6px;font-size:12px;">Ingen kommentarer endnu.</div>'; return; }
  const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  const PERSONA_URL = '/slophub/profiler';
  const childrenOf = {};
  const topLevel = [];
  comments.forEach(c => {
    if (c.parent_id) (childrenOf[c.parent_id] = childrenOf[c.parent_id] || []).push(c);
    else topLevel.push(c);
  });
  const renderOne = (c, depth) => {
    const kids = (childrenOf[c.id] || []).map(k => renderOne(k, depth + 1)).join('');
    const cls = depth > 0 ? 'pc-comment thread' : 'pc-comment';
    const whyAttrs = c.why ? `class="why-name" data-why="${escapeHtml(c.why.text)}" data-type="${c.why.type}"` : '';
    return `<div class="${cls}">
      <div class="av"><img src="${PERSONA_URL}/${c.persona_id}/thumb" onerror="this.outerHTML='${escapeHtml((c.persona_name||'??').substring(0,2).toUpperCase())}'"></div>
      <div style="flex:1; min-width:0;">
        <div class="bubble"><div class="nm"><a href="${PERSONA_URL}/${c.persona_id}" ${whyAttrs}>${escapeHtml(c.persona_name)}</a></div><div>${escapeHtml(c.body)}</div></div>
        <div class="cm-meta">Runde ${c.round}</div>
        ${kids}
      </div>
    </div>`;
  };
  wrap.innerHTML = topLevel.map(c => renderOne(c, 0)).join('');
}
setInterval(() => { openComments.forEach(id => loadComments(id)); }, 5000);

// Alles opslag: live search + participant filter
(function () {
  const search = document.getElementById('postsSearch');
  const participant = document.getElementById('postsParticipant');
  if (!search && !participant) return;
  const cards = document.querySelectorAll('.post-card[data-search]');
  function apply() {
    const q = (search?.value || '').trim().toLowerCase();
    const uid = participant?.value || '';
    let shown = 0;
    cards.forEach(card => {
      const matchesQ = !q || (card.dataset.search || '').includes(q);
      const matchesP = !uid || card.dataset.userId === uid;
      const visible = matchesQ && matchesP;
      card.style.display = visible ? '' : 'none';
      if (visible) shown++;
    });
    let empty = document.getElementById('postsFilterEmpty');
    if (shown === 0 && cards.length > 0) {
      if (!empty) {
        empty = document.createElement('div');
        empty.id = 'postsFilterEmpty';
        empty.className = 'empty-feed';
        empty.innerHTML = '<p style="color:#65676b;">Ingen opslag matcher filteret.</p>';
        document.querySelector('.posts-grid').after(empty);
      }
    } else if (empty) {
      empty.remove();
    }
  }
  search?.addEventListener('input', apply);
  participant?.addEventListener('change', apply);
})();
</script>
@endsection
