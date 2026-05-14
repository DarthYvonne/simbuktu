<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CMS') · Simbuktu</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #f7f8fa;
            --surface:  #ffffff;
            --ink:      #1a2733;
            --ink-soft: #54637a;
            --ink-mute: #8a96a7;
            --line:     #e6eaf0;
            --line-soft:#f0f2f6;
            --accent:   #3498db;
            --accent-h: #2980b9;
            --accent-soft: #eaf4fb;
            --success:  #16a34a;
            --success-soft:#dcfce7;
            --danger:   #dc2626;
            --danger-soft:#fee2e2;
            --shadow-sm: 0 1px 2px rgba(15,23,42,0.04);
            --shadow:    0 4px 14px -8px rgba(15,23,42,0.12), 0 1px 2px rgba(15,23,42,0.04);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        body { background: var(--bg); color: var(--ink); font-size: 14px; line-height: 1.5; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }

        /* ── Top bar ───────────────────────────────── */
        .cms-header {
            background: var(--surface);
            border-bottom: 1px solid var(--line);
            padding: 14px 28px;
            display: flex; justify-content: space-between; align-items: center;
            gap: 16px;
        }
        .cms-header .brand {
            display: flex; align-items: center; gap: 10px;
            font-weight: 700; font-size: 15px; letter-spacing: -0.01em;
            color: var(--ink);
        }
        .cms-header .brand .dot {
            display: inline-block; width: 9px; height: 9px; border-radius: 999px;
            background: var(--accent);
        }
        .cms-header nav { display: flex; gap: 4px; }
        .cms-header nav a {
            padding: 8px 14px; border-radius: 8px;
            font-size: 13px; font-weight: 500; color: var(--ink-soft);
            transition: background 0.12s, color 0.12s;
        }
        .cms-header nav a:hover { background: var(--line-soft); color: var(--ink); }
        .cms-header nav a.active { background: var(--accent-soft); color: var(--accent-h); }

        .cms-main { max-width: 920px; margin: 36px auto; padding: 0 24px; }

        /* ── Cards / surfaces ──────────────────────── */
        .card {
            background: var(--surface);
            border-radius: 14px;
            box-shadow: var(--shadow);
            padding: 28px;
            margin-bottom: 18px;
        }
        .card .card-head {
            display: flex; align-items: flex-end; justify-content: space-between;
            gap: 16px; flex-wrap: wrap; margin-bottom: 22px;
        }
        .card .card-head h1 {
            font-size: 22px; font-weight: 700; letter-spacing: -0.02em;
            color: var(--ink);
        }
        .card .card-head .sub {
            display: block; color: var(--ink-mute); font-size: 13px; font-weight: 400;
            margin-top: 3px;
        }

        /* ── Buttons ───────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--accent); color: #fff;
            padding: 9px 16px; border: 0; border-radius: 8px;
            text-decoration: none; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: background 0.12s, transform 0.04s;
            font-family: inherit;
        }
        .btn:hover { background: var(--accent-h); }
        .btn:active { transform: translateY(1px); }
        .btn--ghost { background: transparent; color: var(--ink-soft); }
        .btn--ghost:hover { background: var(--line-soft); color: var(--ink); }
        .btn--secondary { background: var(--line-soft); color: var(--ink); }
        .btn--secondary:hover { background: var(--line); }
        .btn--danger { background: var(--danger-soft); color: var(--danger); }
        .btn--danger:hover { background: #fecaca; }
        .btn--sm { padding: 6px 11px; font-size: 12px; }

        .iconbtn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 7px;
            background: transparent; color: var(--ink-mute);
            border: 0; cursor: pointer; transition: background 0.12s, color 0.12s;
        }
        .iconbtn:hover { background: var(--line-soft); color: var(--ink); }
        .iconbtn svg { width: 15px; height: 15px; }

        /* ── Forms ─────────────────────────────────── */
        label { display: block; font-size: 12px; font-weight: 600; color: var(--ink-soft); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.4px; }
        input[type=text], input[type=number], textarea, select {
            width: 100%; padding: 10px 12px;
            border: 1px solid var(--line); border-radius: 8px;
            font-size: 14px; font-family: inherit; background: var(--surface);
            color: var(--ink);
            transition: border-color 0.12s, box-shadow 0.12s;
        }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
        textarea { min-height: 240px; font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 13px; }
        .form-row { margin-bottom: 18px; }
        .form-row--inline { display: flex; gap: 14px; flex-wrap: wrap; }
        .form-row--inline > div { flex: 1; min-width: 160px; }
        .checkbox { display: flex; align-items: center; gap: 9px; text-transform: none; letter-spacing: 0; font-size: 14px; font-weight: 500; color: var(--ink); }
        .checkbox input { width: auto; }

        /* ── Status banner ─────────────────────────── */
        .status {
            background: var(--success-soft); color: #166534;
            padding: 12px 16px; border-radius: 10px;
            margin-bottom: 18px; font-size: 13px; font-weight: 500;
            display: flex; align-items: flex-start; gap: 9px;
            flex-direction: column;
        }
        .status[style*="danger"]::before { content: '⚠'; font-weight: 700; }
        .status:not([style*="danger"])::before { content: '✓'; font-weight: 700; }

        code { background: var(--line-soft); padding: 2px 6px; border-radius: 4px; font-size: 12px; color: var(--ink-soft); font-family: 'JetBrains Mono', ui-monospace, monospace; }

        @media (max-width: 600px) {
            .cms-header { padding: 12px 16px; flex-wrap: wrap; }
            .cms-header nav { width: 100%; justify-content: flex-start; }
            .cms-main { padding: 0 14px; margin: 18px auto; }
            .card { padding: 20px 18px; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <header class="cms-header">
        <a href="/cms" class="brand"><span class="dot"></span> Simbuktu CMS</a>
        <nav>
            <a href="/cms" class="{{ request()->is('cms') ? 'active' : '' }}">Sider</a>
            <a href="/cms/settings" class="{{ request()->is('cms/settings') ? 'active' : '' }}">Forside</a>
            <a href="/" style="color: var(--ink-mute);">Vis website ↗</a>
        </nav>
    </header>
    <main class="cms-main">
        @if(session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="status" style="background:var(--danger-soft);color:var(--danger);">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
