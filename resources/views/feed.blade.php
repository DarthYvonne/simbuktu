@extends('layouts.app')
@section('content')

<style>
.post-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 16px; overflow: hidden; max-width: 450px; scroll-margin-top: 20px; }
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
.pc-comment .bubble a.mention { color: #1877f2; font-weight: 600; text-decoration: none; }
.pc-comment .bubble a.mention:hover { text-decoration: underline; }
.pc-comment .cm-meta { font-size: 11px; color: #65676b; padding: 2px 12px; }
.pc-comment.thread { margin-left: 28px; border-left: 2px solid #e4e6eb; padding-left: 10px; }
.pc-comment.student .bubble { background: #e7f3ff; }
.student-badge { display: inline-block; margin-left: 6px; padding: 1px 6px; background: #1877f2; color: #fff; font-size: 10px; font-weight: 700; letter-spacing: 0.3px; border-radius: 8px; vertical-align: middle; }
.reply-link { display: inline-block; margin-left: 8px; font-size: 11px; color: #1877f2; cursor: pointer; font-weight: 600; }
.reply-link:hover { text-decoration: underline; }
.reply-form { margin-top: 6px; display: flex; gap: 6px; }
.reply-form input { flex: 1; padding: 6px 10px; border: 1px solid #dadde1; border-radius: 14px; font-size: 12px; font-family: inherit; }
.reply-form button { padding: 4px 10px; border: none; background: #1877f2; color: #fff; border-radius: 14px; cursor: pointer; font-size: 12px; font-weight: 600; }
.cm-reactions { display: inline-flex; gap: 4px; align-items: center; font-size: 12px; margin-left: 4px; }
.cm-reactions .cm-rx-pill { display: inline-flex; align-items: center; gap: 2px; background: #f0f2f5; border-radius: 10px; padding: 1px 6px; font-size: 11px; cursor: default; }
.cm-react-btn { cursor: pointer; font-size: 11px; color: #1877f2; font-weight: 600; position: relative; }
.cm-react-btn:hover { text-decoration: underline; }
.cm-react-picker { position: absolute; bottom: calc(100% + 6px); left: -10px; background: #fff; border-radius: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.15); padding: 4px 6px; display: none; white-space: nowrap; z-index: 100; }
.cm-react-picker.open { display: flex; gap: 2px; }
.cm-react-picker span { cursor: pointer; padding: 4px 6px; border-radius: 50%; font-size: 18px; transition: transform 0.15s; }
.cm-react-picker span:hover { transform: scale(1.3); background: #f0f2f5; }
.comment-input { padding: 10px 16px 14px; display: none; border-top: 1px solid #f0f2f5; }
.comment-input.open { display: block; }
.comment-input form { display: flex; gap: 8px; }
.comment-input input { flex: 1; padding: 8px 14px; border: 1px solid #dadde1; border-radius: 20px; font-size: 13px; font-family: inherit; }
.comment-input button { padding: 6px 16px; border: none; background: #1877f2; color: #fff; border-radius: 20px; cursor: pointer; font-size: 13px; font-weight: 600; }
.why-name { cursor: pointer; border-bottom: 1px dotted rgba(24,119,242,0.35); }
.why-name:hover { border-bottom-color: #1877f2; }
#whyBubble { position: fixed; background: #1c1e21; color: #fff; padding: 8px 12px; border-radius: 8px; font-size: 12px; max-width: 280px; line-height: 1.4; z-index: 9999; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); pointer-events: none; transform: translateY(-100%); }
#whyBubble::after { content: ''; position: absolute; bottom: -6px; left: 16px; border-left: 6px solid transparent; border-right: 6px solid transparent; border-top: 6px solid #1c1e21; }
#whyBubble.via_friend { border-left: 3px solid #22c55e; }
#whyBubble.discovery { border-left: 3px solid #9ca3af; }
.post-card.highlight { box-shadow: 0 0 0 3px #1877f2, 0 1px 2px rgba(0,0,0,0.1); transition: box-shadow 0.3s; }
.post-card .pc-img { cursor: zoom-in; }
.lightbox { position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 9999; display: none; align-items: center; justify-content: center; cursor: zoom-out; }
.lightbox.open { display: flex; }
.lightbox img { max-width: 95vw; max-height: 95vh; object-fit: contain; border-radius: 4px; }
.lightbox .close { position: absolute; top: 16px; right: 20px; background: rgba(255,255,255,0.15); color: #fff; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 18px; }
.pc-head { display: flex; gap: 10px; align-items: center; padding: 12px 16px 0; }
.pc-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #a1c4fd, #c2e9fb); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
.pc-meta { font-weight: 600; }
.pc-meta small { display: block; color: #65676b; font-weight: 400; font-size: 12px; }
.pc-body { padding: 8px 16px 12px; line-height: 1.45; }
.pc-body > span { white-space: pre-wrap; }
.pc-body .see-more { color: #65676b; font-weight: 600; cursor: pointer; margin-left: 2px; white-space: normal; }
.pc-body .see-more:hover { text-decoration: underline; }
.pc-img { display: block; width: 100%; max-height: 600px; object-fit: cover; }
.pc-link { display: flex; border-top: 1px solid #f0f2f5; border-bottom: 1px solid #f0f2f5; text-decoration: none; color: inherit; }
.pc-link:hover { background: #f8f9fa; }
.pc-link img { width: 180px; object-fit: cover; flex-shrink: 0; background: #e4e6eb; }
.pc-link .lm { padding: 12px 14px; flex: 1; min-width: 0; }
.pc-link .site { font-size: 11px; color: #65676b; text-transform: uppercase; }
.pc-link .title { font-weight: 600; font-size: 15px; margin: 2px 0; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.pc-link .desc { font-size: 13px; color: #65676b; line-height: 1.35; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.pc-stats { display: flex; justify-content: space-between; padding: 10px 16px; font-size: 13px; color: #65676b; border-top: 1px solid #f0f2f5; }
.pc-stats .reactions { display: inline-flex; align-items: center; gap: 4px; }
.pc-stats .reactions .emoji { display: inline-flex; }
.pc-stats .reactions .emoji i { font-size: 14px; margin-right: 4px; }
.pc-stat-icon { display: none; }
@media (max-width: 600px) {
  .pc-stats-text { display: inline-flex; gap: 14px; align-items: center; }
  .pc-stat-icon { display: inline-block; margin-right: 4px; }
  .pc-stat-label, .pc-stat-sep { display: none; }
}
.pc-actions { display: flex; padding: 4px 8px; border-top: 1px solid #f0f2f5; }
.pc-actions a { flex: 1; text-align: center; padding: 8px; border-radius: 6px; color: #65676b; font-weight: 600; font-size: 14px; }
.pc-actions a:hover { background: #f0f2f5; }
.pc-actions a i { margin-right: 6px; }
.empty-feed { background: #fff; border-radius: 12px; padding: 60px 20px; text-align: center; color: #65676b; }
</style>

@php
$reactionIcon = ['like'=>'thumbs-up','love'=>'heart','haha'=>'face-laugh-squint','wow'=>'face-surprise','sad'=>'face-sad-tear','angry'=>'face-angry'];
$reactionBg = ['like'=>'#1877f2','love'=>'#e0245e','haha'=>'#f7b928','wow'=>'#f7b928','sad'=>'#3b82f6','angry'=>'#e9710f'];
@endphp

<style>
.feed-actions { display: flex; gap: 8px; align-items: center; }
.feed-iconbtn { position: relative; background: #e4e6eb; color: #1c1e21; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; text-decoration: none; }
.feed-iconbtn:hover { background: #d8dadf; }
.feed-iconbtn .badge { position: absolute; top: -2px; right: -2px; background: #e11d48; color: #fff; font-size: 11px; font-weight: 700; padding: 1px 6px; border-radius: 10px; min-width: 18px; line-height: 1.3; text-align: center; border: 2px solid #f0f2f5; display: none; }
.feed-iconbtn .badge.show { display: inline-block; }
.notif-wrap { position: relative; }
.notif-dropdown { position: absolute; top: calc(100% + 8px); right: 0; width: 380px; max-width: calc(100vw - 40px); max-height: 70vh; background: #fff; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.18); z-index: 9997; display: none; flex-direction: column; overflow: hidden; }
.notif-dropdown.open { display: flex; }
.notif-dropdown-head { padding: 12px 16px; border-bottom: 1px solid #f0f2f5; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: space-between; }
.notif-dropdown-head .live { display:none; width:8px; height:8px; background:#22c55e; border-radius:50%; margin-left:6px; animation:pulse 1.5s infinite; vertical-align:middle; }
.notif-dropdown-body { overflow-y: auto; flex: 1; padding: 6px; }
.alert-item { display: flex; gap: 10px; padding: 10px; border-radius: 8px; text-decoration: none; color: #1c1e21; transition: background 0.1s; }
.alert-item + .alert-item { border-top: 1px solid #f0f2f5; }
.alert-item:hover { background: #f8f9fa; }
.alert-avatar-wrap { position: relative; flex-shrink: 0; }
.alert-avatar-wrap img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; display: block; }
.alert-avatar-wrap .ph { width: 40px; height: 40px; border-radius: 50%; background: #e4e6eb; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
.alert-icon { position: absolute; bottom: -3px; right: -3px; width: 20px; height: 20px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; box-shadow: 0 0 0 1px rgba(0,0,0,0.05); }
.alert-icon i { font-size: 11px; line-height: 1; display: block; }
.alert-body { flex: 1; min-width: 0; font-size: 13px; line-height: 1.4; }
.alert-body strong { font-weight: 600; }
.alert-body .snippet { color: #65676b; font-size: 12px; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.alert-time { color: #65676b; font-size: 11px; margin-top: 2px; }
.alerts-empty { color: #65676b; font-size: 13px; padding: 24px 16px; text-align: center; }
</style>

<div class="view-header">
  <h1>Feed</h1>
  <div class="feed-actions">
    <a href="{{ url('/simulation/beskeder') }}" class="feed-iconbtn" title="Beskeder" aria-label="Beskeder">
      <i class="fa-regular fa-envelope"></i>
      <span class="badge" id="hdrMessagesBadge">0</span>
    </a>
    <div class="notif-wrap">
      <button type="button" class="feed-iconbtn" id="hdrNotifBtn" title="Notifikationer" aria-label="Notifikationer" aria-expanded="false">
        <i class="fa-regular fa-bell"></i>
        <span class="badge" id="hdrNotifBadge">0</span>
      </button>
      <div class="notif-dropdown" id="notifDropdown" role="menu" aria-labelledby="hdrNotifBtn">
        <div class="notif-dropdown-head">
          <span>Notifikationer <span id="alertsLive" class="live"></span></span>
        </div>
        <div class="notif-dropdown-body" id="alertsList">
          @if (empty($alerts))
            <div class="alerts-empty">Ingen notifikationer endnu. Lav et opslag og vent på reaktioner.</div>
          @else
            @php
            $reactionIconAlert = ['like'=>'thumbs-up','love'=>'heart','haha'=>'face-laugh-squint','wow'=>'face-surprise','sad'=>'face-sad-tear','angry'=>'face-angry'];
            $reactionColorAlert = ['like'=>'#1877f2','love'=>'#e0245e','haha'=>'#f7b928','wow'=>'#f7b928','sad'=>'#3b82f6','angry'=>'#e9710f'];
            @endphp
            @foreach ($alerts as $a)
              @php $key = $a['type'].':'.$a['post_id'].':'.($a['persona']['id'] ?? '').':'.$a['time']->toIso8601String(); @endphp
              <a class="alert-item" data-key="{{ $key }}" data-post-id="{{ $a['post_id'] }}" href="#">
                <div class="alert-avatar-wrap">
                  @if (!empty($a['persona']) && !empty($a['persona']['image_file']))
                    <img src="{{ url('/simulation/profiler/'.$a['persona']['id'].'/thumb') }}">
                  @else
                    <div class="ph">{{ strtoupper(substr($a['persona_name'], 0, 2)) }}</div>
                  @endif
                  <span class="alert-icon">
                    @if ($a['type'] === 'reply')
                      <i class="fa-solid fa-reply" style="color: #8b5cf6;"></i>
                    @elseif ($a['type'] === 'comment')
                      <i class="fa-solid fa-comment-dots" style="color: #1877f2;"></i>
                    @elseif ($a['type'] === 'share')
                      <i class="fa-solid fa-share" style="color: #22c55e;"></i>
                    @else
                      <i class="fa-solid fa-{{ $reactionIconAlert[$a['reaction_type']] ?? 'thumbs-up' }}" style="color: {{ $reactionColorAlert[$a['reaction_type']] ?? '#1877f2' }};"></i>
                    @endif
                  </span>
                </div>
                <div class="alert-body">
                  @if ($a['type'] === 'reply')
                    <strong>{{ $a['persona_name'] }}</strong> svarede på din kommentar
                    <div class="snippet">"{{ $a['body_snippet'] }}"</div>
                  @elseif ($a['type'] === 'comment')
                    <strong>{{ $a['persona_name'] }}</strong> kommenterede dit opslag
                    <div class="snippet">"{{ $a['body_snippet'] }}"</div>
                  @elseif ($a['type'] === 'share')
                    <strong>{{ $a['persona_name'] }}</strong> delte dit opslag
                  @else
                    <strong>{{ $a['persona_name'] }}</strong> reagerede på dit opslag
                  @endif
                  <div class="alert-time">{{ $a['time']->diffForHumans() }}</div>
                </div>
              </a>
            @endforeach
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<div class="feed-main">

@if ($posts->isEmpty())
  <div class="empty-feed">
    <h3 style="color: #1c1e21; margin-bottom: 8px;">Ingen opslag endnu</h3>
    <p>Vær den første. <a href="{{ url('/simulation/posts/create') }}" style="color: #1877f2;">Skriv et opslag</a>.</p>
  </div>
@else
  @foreach ($posts as $post)
    <div class="post-card" data-post-id="{{ $post->id }}">
      <div class="pc-head">
        @php $_img = $post->currentAuthorImage(); $_name = $post->currentAuthorName(); @endphp
        @if ($_img)
          <img src="{{ Storage::url($_img) }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
        @else
          <div class="pc-avatar">{{ strtoupper(substr($_name, 0, 2)) }}</div>
        @endif
        <div class="pc-meta">
          {{ $_name }}
          <small>{{ $post->created_at->diffForHumans() }}</small>
        </div>
      </div>

      @php
        $linkify = fn ($text) => preg_replace('#(https?://[^\s<>"\']+)#i', '<a href="$1" target="_blank" rel="noopener" style="color:#1877f2;word-break:break-all;">$1</a>', e($text));
        $bodyWords = preg_split('/\s+/', trim($post->body), -1, PREG_SPLIT_NO_EMPTY);
        $bodyIsLong = count($bodyWords) > 50;
      @endphp
      <div class="pc-body" data-post-id="{{ $post->id }}">@if ($bodyIsLong)<span class="pc-body-short">{!! $linkify(implode(' ', array_slice($bodyWords, 0, 50))) !!}… <a class="see-more" onclick="toggleBody({{ $post->id }})">Se mere</a></span><span class="pc-body-full" hidden>{!! $linkify($post->body) !!} <a class="see-more" onclick="toggleBody({{ $post->id }})">Vis mindre</a></span>@else<span>{!! $linkify($post->body) !!}</span>@endif</div>

      @if ($post->image_path)
        <img class="pc-img" src="{{ Storage::url($post->image_path) }}" alt="" onclick="openLightbox(this.src)">
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

      <div class="pc-stats">
        <span class="reactions" data-role="reactions" onclick="if(this.querySelector('[data-role=reaction-total]')) openReactions({{ $post->id }})" style="cursor: pointer;">
          @if ($post->reactionTotal() > 0)
            <span class="emoji" data-role="reaction-icons">
              @foreach ($post->topReactions(3) as $type)
                <i class="fa-solid fa-{{ $reactionIcon[$type] ?? 'thumbs-up' }}" style="color: {{ $reactionBg[$type] ?? '#1877f2' }};"></i>
              @endforeach
            </span>
            <span style="margin-left: 6px;" data-role="reaction-total">{{ $post->reactionTotal() }}</span>
          @endif
        </span>
        <span class="pc-stats-text">
          <span class="pc-stat-item"><i class="fa-regular fa-eye pc-stat-icon"></i><span class="pc-stat-label">Set af </span><span data-role="reach">{{ $post->reach }}</span></span><span class="pc-stat-sep"> · </span><span class="pc-stat-item comments-toggle" onclick="toggleComments({{ $post->id }})"><i class="fa-regular fa-comment pc-stat-icon"></i><span data-role="comments">{{ $post->comments_count }}</span><span class="pc-stat-label"> kommentarer</span></span><span class="pc-stat-sep"> · </span><span class="pc-stat-item"><i class="fa-solid fa-share pc-stat-icon"></i><span data-role="shares">{{ $post->shares ?? 0 }}</span><span class="pc-stat-label"> delinger</span></span>
        </span>
      </div>

      <div class="comments-list" id="comments-{{ $post->id }}" data-loaded="0"></div>
      @if ($canComment)
        <div class="comment-input" id="comment-input-{{ $post->id }}">
          <form onsubmit="return submitComment(event, {{ $post->id }})">
            <input type="text" name="body" placeholder="Skriv en kommentar..." maxlength="2000" required>
            <button type="submit">Send</button>
          </form>
        </div>
      @endif

    </div>
  @endforeach
@endif
</div><!-- /feed-main -->

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
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9998; display: none; align-items: center; justify-content: center; padding: 20px; }
.modal-backdrop.open { display: flex; }
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

<style>
@keyframes slideIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }
.alert-item.new { animation: slideIn 0.4s ease; }
</style>

<script>
function openLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightbox').classList.add('open');
}
function closeLightbox() { document.getElementById('lightbox').classList.remove('open'); }
function closeReactions() { document.getElementById('reactionsModal').classList.remove('open'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeLightbox(); closeReactions(); } });

const RX_ICON = {like:'thumbs-up', love:'heart', haha:'face-laugh-squint', wow:'face-surprise', sad:'face-sad-tear', angry:'face-angry'};
const RX_COLOR = {like:'#1877f2', love:'#e0245e', haha:'#f7b928', wow:'#f7b928', sad:'#3b82f6', angry:'#e9710f'};
const RX_LABEL = {like:'Like', love:'Kærlighed', haha:'Haha', wow:'Wow', sad:'Trist', angry:'Vred'};

async function openReactions(postId) {
  const modal = document.getElementById('reactionsModal');
  document.getElementById('rxTabs').innerHTML = '<div style="padding:10px;color:#65676b;font-size:13px;">Indlæser...</div>';
  document.getElementById('rxList').innerHTML = '';
  document.getElementById('rxTitle').textContent = 'Reaktioner';
  modal.classList.add('open');
  try {
    const res = await fetch(`/simulation/posts/${postId}/reactions`);
    const data = await res.json();
    renderReactions(data);
  } catch (e) { document.getElementById('rxTabs').innerHTML = '<div style="padding:10px;color:#b91c1c;font-size:13px;">Kunne ikke hente.</div>'; }
}

function renderReactions(data) {
  const tabs = document.getElementById('rxTabs');
  const list = document.getElementById('rxList');
  const allPersonas = [];
  Object.entries(data.personas).forEach(([type, ps]) => ps.forEach(p => allPersonas.push({...p, reaction: type})));

  // Build tabs (All + each type that has personas, sorted by count desc)
  const types = Object.entries(data.counts).filter(([t,c]) => c > 0).sort((a,b) => b[1]-a[1]);
  let html = `<button class="active" data-type="all">Alle <span style="color:#65676b;">${data.total}</span></button>`;
  types.forEach(([t, c]) => {
    html += `<button data-type="${t}"><i class="fa-solid fa-${RX_ICON[t]}" style="color:${RX_COLOR[t]};"></i> <span style="color:#65676b;">${c}</span></button>`;
  });
  tabs.innerHTML = html;

  const PERSONA_URL = '/simulation/profiler';
  const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  function renderList(type) {
    const items = type === 'all' ? allPersonas : (data.personas[type] || []).map(p => ({...p, reaction: type}));
    if (items.length === 0) { list.innerHTML = '<div style="padding:14px;color:#65676b;font-size:13px;text-align:center;">Ingen reaktioner.</div>'; return; }
    list.innerHTML = items.map(p => {
      const img = p.has_image
        ? `<img class="av" src="${PERSONA_URL}/${p.id}/thumb">`
        : `<div class="av ph">${escapeHtml((p.name||'?').substring(0,2).toUpperCase())}</div>`;
      const whyAttrs = p.why ? `class="nm why-name" data-why="${escapeHtml(p.why.text)}" data-type="${p.why.type}"` : 'class="nm"';
      return `<a class="rx-item" href="${PERSONA_URL}/${p.id}">
        ${img}
        <div style="flex:1; min-width:0;">
          <div ${whyAttrs}>${escapeHtml(p.name)}</div>
          <div class="meta">${p.age ? p.age + ', ' : ''}${escapeHtml(p.occupation || '')}</div>
        </div>
        <i class="fa-solid fa-${RX_ICON[p.reaction]} badge" style="color:${RX_COLOR[p.reaction]};"></i>
      </a>`;
    }).join('');
  }
  renderList('all');

  tabs.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
    tabs.querySelectorAll('button').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    renderList(b.dataset.type);
  }));
}
</script>

<script>
(function () {
  const FEED_URL = '{{ url("/simulation/feed-data") }}';
  const PROFILER_URL = '{{ url("/simulation/profiler") }}';
  const POSTS_URL = '{{ url("/simulation/posts") }}';
  const reactionIcon = {like:'thumbs-up', love:'heart', haha:'face-laugh-squint', wow:'face-surprise', sad:'face-sad-tear', angry:'face-angry'};
  const reactionColor = {like:'#1877f2', love:'#e0245e', haha:'#f7b928', wow:'#f7b928', sad:'#3b82f6', angry:'#e9710f'};

  function escapeHtml(s) { return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

  function updatePost(p) {
    const card = document.querySelector(`.post-card[data-post-id="${p.id}"]`);
    if (!card) return;
    const reach = card.querySelector('[data-role="reach"]');
    const cm = card.querySelector('[data-role="comments"]');
    const sh = card.querySelector('[data-role="shares"]');
    if (reach) reach.textContent = p.reach;
    if (cm) cm.textContent = p.comments_count;
    if (sh) sh.textContent = p.shares;

    const reactionsWrap = card.querySelector('[data-role="reactions"]');
    if (!reactionsWrap) return;
    if (p.reaction_total > 0) {
      const iconsHtml = p.top_reactions.map(t =>
        `<i class="fa-solid fa-${reactionIcon[t] || 'thumbs-up'}" style="color: ${reactionColor[t] || '#1877f2'};"></i>`
      ).join('');
      reactionsWrap.innerHTML = `<span class="emoji" data-role="reaction-icons">${iconsHtml}</span><span style="margin-left: 6px;" data-role="reaction-total">${p.reaction_total}</span>`;
    } else {
      reactionsWrap.innerHTML = '';
    }
  }

  function renderAlert(a, isNew) {
    const avatar = a.persona_has_image
      ? `<img src="${PROFILER_URL}/${a.persona_id}/thumb">`
      : `<div class="ph">${escapeHtml((a.persona_name || '?').substring(0,2).toUpperCase())}</div>`;
    let badge, body;
    if (a.type === 'reply') {
      badge = `<i class="fa-solid fa-reply" style="color:#8b5cf6;"></i>`;
      body = `<strong>${escapeHtml(a.persona_name)}</strong> svarede på din kommentar<div class="snippet">"${escapeHtml(a.body_snippet || '')}"</div>`;
    } else if (a.type === 'comment') {
      badge = `<i class="fa-solid fa-comment-dots" style="color:#1877f2;"></i>`;
      body = `<strong>${escapeHtml(a.persona_name)}</strong> kommenterede dit opslag<div class="snippet">"${escapeHtml(a.body_snippet || '')}"</div>`;
    } else if (a.type === 'share') {
      badge = `<i class="fa-solid fa-share" style="color:#22c55e;"></i>`;
      body = `<strong>${escapeHtml(a.persona_name)}</strong> delte dit opslag`;
    } else {
      const rIcon = reactionIcon[a.reaction_type] || 'thumbs-up';
      const rCol = reactionColor[a.reaction_type] || '#1877f2';
      badge = `<i class="fa-solid fa-${rIcon}" style="color:${rCol};"></i>`;
      body = `<strong>${escapeHtml(a.persona_name)}</strong> reagerede på dit opslag`;
    }
    return `<a class="alert-item ${isNew ? 'new' : ''}" data-key="${a.type}:${a.post_id}:${a.persona_id}:${a.time}" data-post-id="${a.post_id}" href="#">
      <div class="alert-avatar-wrap">${avatar}<span class="alert-icon">${badge}</span></div>
      <div class="alert-body">${body}<div class="alert-time">${escapeHtml(a.time_human)}</div></div>
    </a>`;
  }

  document.getElementById('alertsList').addEventListener('click', (e) => {
    const item = e.target.closest('.alert-item[data-post-id]');
    if (!item) return;
    e.preventDefault();
    const card = document.querySelector(`.post-card[data-post-id="${item.dataset.postId}"]`);
    if (card) {
      card.scrollIntoView({ behavior: 'smooth', block: 'center' });
      card.classList.add('highlight');
      setTimeout(() => card.classList.remove('highlight'), 1500);
    }
    document.getElementById('notifDropdown').classList.remove('open');
    document.getElementById('hdrNotifBtn').setAttribute('aria-expanded', 'false');
  });

  // Header icon dropdown + badges
  const notifBtn = document.getElementById('hdrNotifBtn');
  const notifDropdown = document.getElementById('notifDropdown');
  const notifBadge = document.getElementById('hdrNotifBadge');
  const msgBadge = document.getElementById('hdrMessagesBadge');

  function setBadge(el, n) {
    if (!el) return;
    if (n > 0) {
      el.textContent = n > 99 ? '99+' : n;
      el.classList.add('show');
    } else {
      el.classList.remove('show');
    }
  }

  notifBtn.addEventListener('click', async (e) => {
    e.stopPropagation();
    const wasOpen = notifDropdown.classList.contains('open');
    notifDropdown.classList.toggle('open');
    notifBtn.setAttribute('aria-expanded', !wasOpen);
    if (!wasOpen) {
      try {
        await fetch('{{ url("/simulation/mark-feed-seen") }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        });
        setBadge(notifBadge, 0);
      } catch {}
    }
  });

  document.addEventListener('click', (e) => {
    if (!notifDropdown.classList.contains('open')) return;
    if (notifDropdown.contains(e.target) || notifBtn.contains(e.target)) return;
    notifDropdown.classList.remove('open');
    notifBtn.setAttribute('aria-expanded', 'false');
  });

  async function pollCounts() {
    try {
      const res = await fetch('{{ url("/simulation/unread-count") }}');
      const data = await res.json();
      setBadge(notifBadge, data.unread || 0);
      setBadge(msgBadge, data.messages || 0);
    } catch {}
  }
  pollCounts();
  setInterval(pollCounts, 15000);

  const seenKeys = new Set();
  // Seed with server-rendered items (avoid duplicate animation on first poll)
  document.querySelectorAll('#alertsList .alert-item[data-key]').forEach(el => seenKeys.add(el.dataset.key));
  let firstPoll = true;

  async function poll() {
    try {
      const res = await fetch(FEED_URL);
      const data = await res.json();
      data.posts.forEach(updatePost);

      const list = document.getElementById('alertsList');
      const currentKeys = new Set(data.alerts.map(a => `${a.type}:${a.post_id}:${a.persona_id}:${a.time}`));

      if (data.alerts.length === 0) {
        list.innerHTML = '<div class="alerts-empty" style="color: #65676b; font-size: 13px; padding: 10px 0;">Ingen notifikationer endnu. Lav et opslag og vent på reaktioner.</div>';
        seenKeys.clear();
        return;
      }

      const liveDot = document.getElementById('alertsLive');
      let addedNew = false;
      // Rebuild from top with animations for new ones
      list.innerHTML = data.alerts.map(a => {
        const key = `${a.type}:${a.post_id}:${a.persona_id}:${a.time}`;
        const isNew = !firstPoll && !seenKeys.has(key);
        if (isNew) addedNew = true;
        return renderAlert(a, isNew);
      }).join('');
      seenKeys.clear();
      currentKeys.forEach(k => seenKeys.add(k));
      firstPoll = false;

      if (addedNew) {
        liveDot.style.display = 'inline-block';
        setTimeout(() => { liveDot.style.display = 'none'; }, 3000);
      }
    } catch (e) { console.warn('feed poll failed', e); }
  }

  setInterval(poll, 10000);
})();

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

const OPEN_COMMENTS_KEY = 'slophub:openComments';
const openComments = new Set(
  (() => { try { return JSON.parse(localStorage.getItem(OPEN_COMMENTS_KEY) || '[]'); } catch { return []; } })()
);
function persistOpenComments() {
  try { localStorage.setItem(OPEN_COMMENTS_KEY, JSON.stringify([...openComments])); } catch {}
}

async function toggleComments(postId) {
  const wrap = document.getElementById('comments-' + postId);
  const input = document.getElementById('comment-input-' + postId);
  if (!wrap) return;
  if (wrap.classList.contains('open')) {
    wrap.classList.remove('open');
    if (input) input.classList.remove('open');
    openComments.delete(postId);
    persistOpenComments();
    return;
  }
  wrap.classList.add('open');
  if (input) input.classList.add('open');
  openComments.add(postId);
  persistOpenComments();
  await loadComments(postId);
}

// Restore expanded comment threads on page load
(function restoreOpenComments() {
  const toRestore = [...openComments];
  for (const postId of toRestore) {
    const wrap = document.getElementById('comments-' + postId);
    const input = document.getElementById('comment-input-' + postId);
    if (!wrap) { openComments.delete(postId); continue; }
    wrap.classList.add('open');
    if (input) input.classList.add('open');
    loadComments(postId);
  }
  persistOpenComments();
})();

// Persisted "Se mere" state for long post bodies
const OPEN_BODIES_KEY = 'slophub:openBodies';
const openBodies = new Set(
  (() => { try { return JSON.parse(localStorage.getItem(OPEN_BODIES_KEY) || '[]'); } catch { return []; } })()
);
function persistOpenBodies() {
  try { localStorage.setItem(OPEN_BODIES_KEY, JSON.stringify([...openBodies])); } catch {}
}
function setBodyExpanded(postId, expanded) {
  const wrap = document.querySelector(`.pc-body[data-post-id="${postId}"]`);
  if (!wrap) return;
  const short = wrap.querySelector('.pc-body-short');
  const full = wrap.querySelector('.pc-body-full');
  if (!short || !full) return;
  short.hidden = expanded;
  full.hidden = !expanded;
}
function toggleBody(postId) {
  if (openBodies.has(postId)) {
    openBodies.delete(postId);
    setBodyExpanded(postId, false);
  } else {
    openBodies.add(postId);
    setBodyExpanded(postId, true);
  }
  persistOpenBodies();
}
(function restoreOpenBodies() {
  for (const postId of [...openBodies]) {
    const wrap = document.querySelector(`.pc-body[data-post-id="${postId}"]`);
    if (!wrap) { openBodies.delete(postId); continue; }
    setBodyExpanded(postId, true);
  }
  persistOpenBodies();
})();

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
const CAN_COMMENT = @json($canComment);
const RX_EMOJI = {like:'👍', love:'❤️', haha:'😂', wow:'😮', sad:'😢', angry:'😡'};

async function submitComment(event, postId, parentId) {
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
    const res = await fetch(`/simulation/posts/${postId}/comments`, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: fd,
    });
    if (!res.ok) throw new Error('status ' + res.status);
    input.value = '';
    await loadComments(postId);
  } catch (e) {
    alert('Kunne ikke sende kommentaren.');
  }
  return false;
}

function openReply(postId, parentId) {
  const existing = document.getElementById('reply-form-' + parentId);
  if (existing) { existing.querySelector('input').focus(); return; }
  const comment = document.getElementById('comment-' + parentId);
  if (!comment) return;
  const form = document.createElement('form');
  form.className = 'reply-form';
  form.id = 'reply-form-' + parentId;
  form.innerHTML = `<input type="text" name="body" placeholder="Svar..." maxlength="2000" required autofocus><button type="submit">Send</button>`;
  form.onsubmit = e => submitComment(e, postId, parentId);
  comment.querySelector('.cm-meta').insertAdjacentElement('afterend', form);
  form.querySelector('input').focus();
}

async function loadComments(postId) {
  const wrap = document.getElementById('comments-' + postId);
  if (!wrap) return;
  try {
    const res = await fetch(`/simulation/posts/${postId}/feed`);
    const data = await res.json();
    renderComments(wrap, data.comments);
  } catch (e) { wrap.innerHTML = '<div style="color:#b91c1c;padding:6px;font-size:12px;">Kunne ikke hente kommentarer.</div>'; }
}

function renderComments(wrap, comments) {
  if (!comments || comments.length === 0) {
    wrap.innerHTML = '<div style="color:#65676b;padding:6px;font-size:12px;">Ingen kommentarer endnu.</div>';
    return;
  }
  const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  const timeAgoDa = iso => {
    const s = Math.max(0, Math.floor((Date.now() - new Date(iso).getTime()) / 1000));
    if (s < 45) return 'få sek. siden';
    if (s < 3600) return Math.max(1, Math.round(s / 60)) + ' min siden';
    if (s < 86400) return Math.round(s / 3600) + ' t siden';
    if (s < 604800) return Math.round(s / 86400) + ' d siden';
    return Math.round(s / 604800) + ' u siden';
  };
  const PERSONA_URL = '/simulation/profiler';
  const childrenOf = {};
  const topLevel = [];
  comments.forEach(c => {
    if (c.parent_id) (childrenOf[c.parent_id] = childrenOf[c.parent_id] || []).push(c);
    else topLevel.push(c);
  });
  const postId = wrap.id.replace('comments-', '');

  // Build mention link table: any persona who has commented in this thread
  // can be @-tagged by name in another comment's body.
  const mentionToId = {};
  const escNames = [];
  const seenPids = new Set();
  comments.forEach(c => {
    if (!c.persona_id || c.from_student || seenPids.has(c.persona_id)) return;
    seenPids.add(c.persona_id);
    const esc = escapeHtml(c.persona_name);
    mentionToId[esc] = c.persona_id;
    escNames.push(esc);
  });
  escNames.sort((a, b) => b.length - a.length);
  const escapeRegex = s => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const mentionRe = escNames.length
    ? new RegExp(`(?<![\\p{L}\\p{N}])(${escNames.map(escapeRegex).join('|')})(?![\\p{L}\\p{N}])`, 'gu')
    : null;
  const linkifyMentions = body => {
    if (!mentionRe) return body;
    return body.replace(mentionRe, m => {
      const id = mentionToId[m];
      return id ? `<a class="mention" href="${PERSONA_URL}/${id}">${m}</a>` : m;
    });
  };
  const renderOne = (c, depth) => {
    const kids = (childrenOf[c.id] || []).map(k => renderOne(k, depth + 1)).join('');
    const classes = ['pc-comment'];
    if (depth > 0) classes.push('thread');
    if (c.from_student) classes.push('student');
    const whyAttrs = c.why ? `class="why-name" data-why="${escapeHtml(c.why.text)}" data-type="${c.why.type}"` : '';
    const badge = c.from_student ? '<span class="student-badge">Kursist</span>' : '';
    const initials = escapeHtml((c.persona_name||'??').substring(0,2).toUpperCase());
    const avatar = c.from_student
      ? (c.author_image
          ? `<div class="av"><img src="/storage/${escapeHtml(c.author_image)}" onerror="this.outerHTML='${initials}'"></div>`
          : `<div class="av" style="background:#1877f2;color:#fff;">${initials}</div>`)
      : `<div class="av"><img src="${PERSONA_URL}/${c.persona_id}/thumb" onerror="this.outerHTML='${initials}'"></div>`;
    const nameLink = c.from_student
      ? `<span class="nm">${escapeHtml(c.persona_name)}${badge}</span>`
      : `<div class="nm"><a href="${PERSONA_URL}/${c.persona_id}" ${whyAttrs}>${escapeHtml(c.persona_name)}</a></div>`;
    const replyLink = CAN_COMMENT ? `<span class="reply-link" onclick="openReply(${postId}, ${c.id})">Svar</span>` : '';

    // Reaction pills (aggregated counts)
    const rxObj = c.reactions || {};
    const rxTotal = Object.values(rxObj).reduce((a,b)=>a+b, 0);
    let rxPills = '';
    if (rxTotal > 0) {
      const types = Object.entries(rxObj).filter(([,v])=>v>0).sort((a,b)=>b[1]-a[1]);
      const emojis = types.map(([t])=> RX_EMOJI[t]||'👍').join('');
      rxPills = `<span class="cm-reactions"><span class="cm-rx-pill">${emojis} ${rxTotal}</span></span>`;
    }

    // React button with picker (only for persona comments)
    const myRx = c.my_reaction || '';
    const dot = (replyLink && c.persona_id) ? '<span style="margin: 0 4px; color: #65676b;">·</span>' : '';
    const reactBtn = c.persona_id
      ? `${dot}<span class="cm-react-btn ${myRx ? 'active' : ''}" data-comment-id="${c.id}" onclick="toggleReactPicker(this)">${myRx ? RX_EMOJI[myRx] : 'Reager'}<div class="cm-react-picker" data-comment-id="${c.id}">${Object.entries(RX_EMOJI).map(([t,e])=>`<span data-type="${t}" onclick="event.stopPropagation(); sendCommentReaction(${c.id},'${t}',this)">${e}</span>`).join('')}</div></span>`
      : '';

    return `<div class="${classes.join(' ')}" id="comment-${c.id}">
      ${avatar}
      <div style="flex:1; min-width:0;">
        <div class="bubble">${nameLink}<div>${linkifyMentions(escapeHtml(c.body))}</div>${rxPills}</div>
        <div class="cm-meta">${timeAgoDa(c.created_at)}${replyLink}${reactBtn}</div>
        ${kids}
      </div>
    </div>`;
  };
  wrap.innerHTML = topLevel.map(c => renderOne(c, 0)).join('');
}

function toggleReactPicker(btn) {
  const picker = btn.querySelector('.cm-react-picker');
  if (!picker) return;
  document.querySelectorAll('.cm-react-picker.open').forEach(p => { if (p !== picker) p.classList.remove('open'); });
  picker.classList.toggle('open');
}
document.addEventListener('click', e => {
  if (!e.target.closest('.cm-react-btn')) {
    document.querySelectorAll('.cm-react-picker.open').forEach(p => p.classList.remove('open'));
  }
});

async function sendCommentReaction(commentId, type, el) {
  const picker = el.closest('.cm-react-picker');
  if (picker) picker.classList.remove('open');
  try {
    const res = await fetch(`/simulation/comments/${commentId}/react`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ type }),
    });
    if (!res.ok) return;
    const data = await res.json();
    const commentEl = document.getElementById('comment-' + commentId);
    if (!commentEl) return;
    const btn = commentEl.querySelector('.cm-react-btn');
    if (btn) {
      if (data.my_reaction) {
        btn.classList.add('active');
        btn.childNodes[0].textContent = RX_EMOJI[data.my_reaction];
      } else {
        btn.classList.remove('active');
        btn.childNodes[0].textContent = 'Reager';
      }
    }
    const bubble = commentEl.querySelector('.bubble');
    if (!bubble) return;
    let pillWrap = bubble.querySelector('.cm-reactions');
    if (data.total > 0) {
      const types = Object.entries(data.reactions).filter(([,v])=>v>0).sort((a,b)=>b[1]-a[1]);
      const emojis = types.map(([t])=> RX_EMOJI[t]||'👍').join('');
      const html = `<span class="cm-rx-pill">${emojis} ${data.total}</span>`;
      if (pillWrap) { pillWrap.innerHTML = html; }
      else { bubble.insertAdjacentHTML('beforeend', `<span class="cm-reactions">${html}</span>`); }
    } else if (pillWrap) {
      pillWrap.remove();
    }
  } catch (e) { console.warn('reaction failed', e); }
}

// Refresh open comment threads when feed polls — but skip if user is typing a reply or comment
setInterval(() => {
  const focused = document.activeElement;
  const isTyping = focused && (focused.tagName === 'INPUT' || focused.tagName === 'TEXTAREA');
  if (isTyping) return;
  openComments.forEach(postId => loadComments(postId));
}, 10000);
</script>

@endsection
