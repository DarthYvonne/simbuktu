@php
  $brandCourse = auth()->check() ? auth()->user()->currentCourse() : null;
  $brandName = $brandCourse?->platform_name ?: 'Simbuktu';
  $brandLogoUrl = $brandCourse?->logo_path
      ? \Illuminate\Support\Facades\Storage::disk('public')->url($brandCourse->logo_path)
      : url('/img/slophub-logo.png');
  $brandFaviconUrl = $brandCourse?->favicon_path
      ? \Illuminate\Support\Facades\Storage::disk('public')->url($brandCourse->favicon_path)
      : url('/img/favicon.png');
  $brandAccent = $brandCourse?->accent_color ?: '#1877f2';
@endphp
<!DOCTYPE html>
<html lang="da">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? $brandName }}</title>
<link rel="icon" type="image/png" href="{{ $brandFaviconUrl }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
  :root {
    --accent: {{ $brandAccent }};
    --accent-hover: color-mix(in srgb, var(--accent) 92%, #000);
    --accent-soft: color-mix(in srgb, var(--accent) 12%, #fff);
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, "Segoe UI", Roboto, sans-serif; background: #f0f2f5; color: #1c1e21; font-size: 14px; }
  a { color: inherit; text-decoration: none; }
  .app { display: grid; grid-template-columns: 240px 1fr; min-height: 100vh; max-width: 1400px; margin: 0; }
  .sidebar { background: #fff; border-right: 1px solid #dadde1; padding: 16px 12px; position: sticky; top: 0; height: 100vh; display: flex; flex-direction: column; }
  .sidebar-toggle { display: none; }
  .mobile-topbar { display: none; }
  .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1001; }
  .sidebar-backdrop.open { display: block; }
  .main { padding: 20px 24px; min-width: 0; max-width: 100%; overflow-x: hidden; }
  .view-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px; }
  .view-header h1 { font-size: 20px; font-weight: 700; }
  .filter-grid { display: grid; grid-template-columns: 2fr repeat(4, 1fr); gap: 8px; }
  .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px; }
  @media (max-width: 767px) {
    .app { grid-template-columns: 1fr; }
    .sidebar { position: fixed; left: -260px; top: 0; z-index: 1002; transition: left 0.25s ease; box-shadow: none; width: 240px; }
    .sidebar.open { left: 0; box-shadow: 4px 0 24px rgba(0,0,0,0.15); }
    .mobile-topbar { display: flex; align-items: center; position: fixed; top: 0; left: 0; right: 0; height: 50px; background: #fff; border-bottom: 1px solid #dadde1; z-index: 1000; padding: 0 14px; gap: 12px; }
    .topbar-toggle { width: 36px; height: 36px; background: none; border: none; cursor: pointer; font-size: 18px; color: #1c1e21; display: flex; align-items: center; justify-content: center; border-radius: 8px; flex-shrink: 0; }
    .topbar-toggle:hover { background: #f0f2f5; }
    .topbar-title { font-size: 17px; font-weight: 700; color: #1c1e21; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .topbar-actions { margin-left: auto; display: flex; gap: 6px; align-items: center; flex-shrink: 0; }
    .topbar-actions .feed-iconbtn { width: 34px; height: 34px; font-size: 15px; }
    .topbar-actions .feed-iconbtn .badge { border-color: #fff; }
    .topbar-actions .btn { padding: 6px 12px; font-size: 12px; }
    .main { padding: 58px 14px 20px; }
    .filter-grid { grid-template-columns: 1fr 1fr; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .view-header { padding-left: 4px; }
  }
  .sidebar .nav { flex: 1; }
  .sidebar .logout-btn:hover { background: #f0f2f5; color: #e11d48 !important; }
  .logo { padding: 4px 8px 16px; }
  .logo img { width: 100%; max-width: 200px; height: auto; display: block; }
  .logo small { display: block; font-size: 11px; font-weight: 400; color: #65676b; padding: 6px 4px 0; }
  .nav a { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 8px; margin-bottom: 2px; cursor: pointer; }
  .nav a:hover { background: #f0f2f5; }
  .nav a.active { background: var(--accent-soft); color: var(--accent); font-weight: 600; }
  .nav .ico { width: 20px; display: inline-block; text-align: center; color: #65676b; }
  .nav a.active .ico { color: var(--accent); }
  .nav-section { font-size: 11px; text-transform: uppercase; color: #65676b; padding: 16px 12px 6px; }
  .btn { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; font-size: 13px; }
  .btn-primary { background: var(--accent); color: #fff; }
  .btn-primary:hover { background: var(--accent-hover); }
  .btn-secondary { background: #e4e6eb; color: #1c1e21; }
  .btn-danger { background: #fee2e2; color: #b91c1c; }
  .btn:disabled { opacity: 0.5; cursor: not-allowed; }
  .card { background: #fff; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 14px; padding: 14px 16px; }
  .tag { background: var(--accent-soft); color: var(--accent); font-size: 12px; padding: 3px 10px; border-radius: 12px; }
  .tag.trigger { background: transparent; color: #b91c1c; border: 1px solid #b91c1c; }
  .tag.sub { background: transparent; color: #166534; border: 1px solid #166534; }
  .tags { display: flex; gap: 6px; flex-wrap: wrap; }
  .tags.compact { gap: 4px; }
  .tags.compact .tag { font-size: 10px; padding: 1px 7px; border-radius: 10px; }
  .alert { padding: 10px 14px; border-radius: 8px; margin-bottom: 14px; font-size: 13px; }
  .alert-success { background: #dcfce7; color: #166534; }
  .alert-error { background: #fee2e2; color: #b91c1c; }
  input[type=text], input[type=number], select, textarea { font-family: inherit; font-size: 14px; padding: 8px 12px; border: 1px solid #dadde1; border-radius: 6px; background: #fff; }
  textarea { width: 100%; resize: vertical; min-height: 100px; }
  .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.7s linear infinite; vertical-align: middle; margin-right: 6px; }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>
<header class="mobile-topbar">
  <button class="topbar-toggle" id="sidebarToggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
  <span class="topbar-title" id="topbarTitle">{{ $title ?? $brandName }}</span>
  <div class="topbar-actions" id="topbarActions"></div>
</header>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<div class="app">
  <aside class="sidebar" id="sidebar">
    <div class="logo">
      <a href="{{ url('/simulation') }}">
        @if ($brandCourse?->logo_path)
          <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}">
        @else
          <div style="font-size: 22px; font-weight: 800; color: var(--accent); padding: 6px 4px; line-height: 1.1;">{{ $brandName }}</div>
        @endif
      </a>
    </div>
    <nav class="nav">
      @auth @if (auth()->user()->currentCourse())
      @php $unread = auth()->user()->unreadFeedCount(); @endphp
      <a href="{{ url('/simulation') }}" data-nav="feed" class="{{ request()->is('simulation') ? 'active' : '' }}"><span class="ico"><i class="fa-regular fa-newspaper"></i></span> Feed
        <span id="feedUnreadBadge" style="margin-left: auto; background: #e11d48; color: #fff; font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 10px; min-width: 20px; text-align: center; display: {{ $unread > 0 ? 'inline-block' : 'none' }};">{{ $unread > 99 ? '99+' : $unread }}</span>
      </a>
      <a href="{{ url('/simulation/posts') }}" data-nav="mine" class="{{ request()->is('simulation/posts*') ? 'active' : '' }}"><span class="ico"><i class="fa-regular fa-pen-to-square"></i></span> Opslag</a>
      <a href="{{ url('/simulation/profiler') }}" data-nav="profiler" class="{{ request()->is('simulation/profiler*') ? 'active' : '' }}"><span class="ico"><i class="fa-solid fa-users"></i></span> Profiler</a>
      <a href="{{ url('/simulation/analyse') }}" data-nav="analyse" class="{{ request()->is('simulation/analyse*') ? 'active' : '' }}"><span class="ico"><i class="fa-solid fa-chart-column"></i></span> Analyse</a>
      <a href="{{ url('/simulation/mig') }}" data-nav="mig" class="{{ request()->is('simulation/mig*') ? 'active' : '' }}"><span class="ico"><i class="fa-solid fa-user"></i></span> Mig</a>
      @endif @endauth
      @auth @if (auth()->user()->is_admin)
      <div class="nav-section">Admin</div>
      <a href="{{ url('/simulation/admin/populations') }}" data-nav="admin-populations" class="{{ request()->is('simulation/admin/populations*') ? 'active' : '' }}"><span class="ico"><i class="fa-solid fa-dna"></i></span> Populationer</a>
      @php $systemActive = request()->is('simulation/admin/algorithm*') || request()->is('simulation/admin/prompts*') || request()->is('simulation/admin/api-check*') || request()->is('simulation/admin/usage*') || request()->is('simulation/admin/personlighedskomponenter*'); @endphp
      <a href="{{ url('/simulation/admin/algorithm') }}" data-nav="admin-system" class="{{ $systemActive ? 'active' : '' }}"><span class="ico"><i class="fa-solid fa-sliders"></i></span> System</a>
      <div style="margin-top: 18px; border-top: 1px solid #f0f2f5; padding-top: 8px;">
        <a href="{{ url('/simulation/admin/courses') }}" data-nav="admin-courses" class="{{ request()->is('simulation/admin/courses*') ? 'active' : '' }}" style="color:#65676b;"><span class="ico"><i class="fa-solid fa-chalkboard-user"></i></span> Simulationer</a>
      </div>
      @endif @endauth
    </nav>
    @auth
      <div style="margin-top: 8px; border-top: 1px solid #f0f2f5; padding-top: 8px;">
        <a href="{{ url('/simulation/konto') }}" data-nav="konto" class="{{ request()->is('simulation/konto*') ? 'active' : '' }}" style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 8px; color: #65676b; font-weight: 600; font-size: 14px;">
          <span class="ico"><i class="fa-solid fa-gear"></i></span> Konto
        </a>
      </div>
      <form method="POST" action="{{ url('/simulation/logout') }}" style="margin-top: 0;">
        @csrf
        <button type="submit" class="logout-btn" style="width: 100%; padding: 10px 12px; background: none; border: none; color: #65676b; cursor: pointer; font-weight: 600; font-size: 14px; text-align: left; border-radius: 8px; display: flex; align-items: center; gap: 12px;">
          <span class="ico"><i class="fa-solid fa-right-from-bracket"></i></span> Log ud
        </button>
      </form>
    @endauth
  </aside>
  <main class="main">
    @yield('content')
  </main>
</div>
@include('partials.persona-chat-widget')
@auth
<script>
(function () {
  const badge = document.getElementById('feedUnreadBadge');
  if (!badge) return;
  async function tick() {
    try {
      const res = await fetch('{{ url("/simulation/unread-count") }}');
      const data = await res.json();
      const n = data.unread || 0;
      if (n > 0) {
        badge.textContent = n > 99 ? '99+' : n;
        badge.style.display = 'inline-block';
      } else {
        badge.style.display = 'none';
      }
    } catch {}
  }
  setInterval(tick, 15000); // header unread badge — modest refresh rate
})();
</script>
@endauth
<script>
(function () {
  var toggle = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('sidebar');
  var backdrop = document.getElementById('sidebarBackdrop');
  if (!toggle) return;
  function open() { sidebar.classList.add('open'); backdrop.classList.add('open'); }
  function close() { sidebar.classList.remove('open'); backdrop.classList.remove('open'); }
  toggle.addEventListener('click', function () { sidebar.classList.contains('open') ? close() : open(); });
  backdrop.addEventListener('click', close);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  sidebar.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', close); });
  var titleEl = document.getElementById('topbarTitle');
  var viewHeader = document.querySelector('.view-header');
  var h1 = viewHeader ? viewHeader.querySelector('h1') : null;
  var topbarActions = document.getElementById('topbarActions');

  if (h1) {
    var txt = '';
    for (var i = 0; i < h1.childNodes.length; i++) {
      if (h1.childNodes[i].nodeType === 3) txt += h1.childNodes[i].textContent;
    }
    txt = txt.replace(/[←→]/g, '').trim();
    if (txt) titleEl.textContent = txt;
  }
  if (!titleEl.textContent || titleEl.textContent === '{{ $title ?? $brandName }}') {
    var activeNav = document.querySelector('.nav a.active');
    if (activeNav) {
      var navTxt = '';
      activeNav.childNodes.forEach(function (n) { if (n.nodeType === 3) navTxt += n.textContent; });
      navTxt = navTxt.trim();
      if (navTxt) titleEl.textContent = navTxt;
    }
  }

  var movers = [];
  if (topbarActions) {
    var fa = document.querySelector('.feed-actions');
    if (fa) movers.push({el: fa, parent: fa.parentElement});
    if (viewHeader) {
      viewHeader.querySelectorAll(':scope > a.btn, :scope > .btn, :scope > button.btn').forEach(function (b) {
        movers.push({el: b, parent: viewHeader});
      });
    }
  }

  var h1IsBackLink = h1 && h1.querySelector('a');
  var mql = window.matchMedia('(max-width: 767px)');

  function onMobileChange(e) {
    movers.forEach(function (m) {
      if (e.matches) topbarActions.appendChild(m.el);
      else m.parent.appendChild(m.el);
    });
    if (!viewHeader) return;
    if (e.matches) {
      if (h1 && !h1IsBackLink) h1.style.display = 'none';
      var hasVisible = false;
      for (var c = 0; c < viewHeader.children.length; c++) {
        if (viewHeader.children[c].offsetHeight > 0) { hasVisible = true; break; }
      }
      viewHeader.style.display = hasVisible ? '' : 'none';
    } else {
      if (h1) h1.style.display = '';
      viewHeader.style.display = '';
    }
  }
  onMobileChange(mql);
  mql.addEventListener('change', onMobileChange);
})();
</script>

<style>
.img-desc-tip { position: fixed; background: rgba(0,0,0,0.88); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px; max-width: 320px; line-height: 1.4; z-index: 10000; pointer-events: none; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
</style>
<script>
(function () {
  var tip = null, timer = null, lastX = 0, lastY = 0;
  function place(x, y) {
    if (!tip) return;
    var pad = 12, w = tip.offsetWidth, h = tip.offsetHeight;
    var left = Math.min(x + pad, window.innerWidth - w - pad);
    var top  = Math.min(y + pad, window.innerHeight - h - pad);
    tip.style.left = Math.max(pad, left) + 'px';
    tip.style.top  = Math.max(pad, top)  + 'px';
  }
  function hide() {
    if (timer) { clearTimeout(timer); timer = null; }
    if (tip) { tip.remove(); tip = null; }
  }
  document.addEventListener('mouseover', function (e) {
    var el = e.target.closest && e.target.closest('[data-img-desc]');
    if (!el) return;
    var text = el.getAttribute('data-img-desc');
    if (!text) return;
    hide();
    lastX = e.clientX; lastY = e.clientY;
    timer = setTimeout(function () {
      tip = document.createElement('div');
      tip.className = 'img-desc-tip';
      tip.textContent = text;
      document.body.appendChild(tip);
      place(lastX, lastY);
    }, 3000);
  });
  document.addEventListener('mousemove', function (e) {
    if (!e.target.closest || !e.target.closest('[data-img-desc]')) return;
    lastX = e.clientX; lastY = e.clientY;
    if (tip) place(lastX, lastY);
  });
  document.addEventListener('mouseout', function (e) {
    var from = e.target.closest && e.target.closest('[data-img-desc]');
    if (!from) return;
    var to = e.relatedTarget && e.relatedTarget.closest && e.relatedTarget.closest('[data-img-desc]');
    if (to === from) return;
    hide();
  });
  window.addEventListener('scroll', hide, true);
})();
</script>
</body>
</html>
