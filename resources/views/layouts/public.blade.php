<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Simbuktu | Troværdige Simulationer')</title>

    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Stack+Sans+Headline:wght@400;700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Stack Sans Headline', system-ui, sans-serif; }
        body { background-color: #ffffff; color: #2c3e50; line-height: 1.5; }
        a { color: inherit; }
        img { max-width: 100%; height: auto; }

        header { border-bottom: 1px solid #e0e0e0; background-color: #ffffff; position: relative; }

        .top-bar {
            display: flex;
            align-items: center;
            padding: 15px 5%;
            border-bottom: 1px solid #e0e0e0;
            position: relative;
        }

        .logo-area { flex: 1; display: flex; justify-content: center; }
        .logo-area img { height: 100px; width: auto; max-width: 100%; }

        .icon-area { display: flex; gap: 20px; color: #2c3e50; min-width: 22px; }
        .icon-area a { color: inherit; display: inline-flex; }
        .icon-area svg { width: 22px; height: 22px; cursor: pointer; }

        .menu-toggle {
            display: none;
            background: none;
            border: 0;
            cursor: pointer;
            padding: 8px;
            color: #2c3e50;
        }
        .menu-toggle svg { width: 26px; height: 26px; }

        nav { padding: 10px 5%; background-color: #ffffff; }
        nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }
        nav ul li a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 300;
            text-transform: uppercase;
            font-size: 18px;
            letter-spacing: 1px;
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            transition: background-color 0.15s, color 0.15s;
        }
        nav ul li a:hover { color: #3498db; }
        nav ul li a.active { background: #3498db; color: #fff; }

        .container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 60px 5%;
        }

        /* ── Body typography (page content + landing) ─────────────────────
           One vertical rhythm unit ≈ 8px. Headings have generous top margin
           to "breathe" after preceding text, but tight when they immediately
           follow another heading. First element in any block has no top margin. */
        .container { color: #2c3e50; }
        .container h1, .container h2, .container h3, .container h4 {
            color: #1a2733;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.01em;
        }
        .container h1 { font-size: 2.25rem; margin: 0 0 12px; letter-spacing: -0.02em; line-height: 1.15; }
        .container h2 { font-size: 1.5rem;  margin: 40px 0 12px; }
        .container h3 { font-size: 1.2rem;  margin: 28px 0 10px; }
        .container h4 { font-size: 1.05rem; margin: 22px 0 8px; }

        .container p  { font-size: 1.05rem; color: #4a5568; margin: 0 0 14px; line-height: 1.65; }
        .container ul, .container ol { margin: 0 0 16px 22px; color: #4a5568; font-size: 1.05rem; line-height: 1.65; }
        .container li { margin-bottom: 4px; }
        .container li > ul, .container li > ol { margin-bottom: 0; }

        .container a { color: #3498db; text-decoration: underline; text-underline-offset: 2px; }
        .container a:hover { color: #2980b9; }

        .container strong { color: #1a2733; }

        .container blockquote {
            margin: 22px 0;
            padding: 4px 0 4px 18px;
            border-left: 3px solid #cdd5e0;
            color: #54637a;
            font-style: italic;
        }
        .container code {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 0.92em;
            background: #f4f6f8;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .container pre {
            background: #1a2733; color: #e6eaf0;
            padding: 16px 18px; border-radius: 8px;
            overflow-x: auto; margin: 18px 0;
            font-size: 0.92rem; line-height: 1.5;
        }
        .container pre code { background: transparent; padding: 0; color: inherit; }

        .container img { margin: 14px 0; }
        .container hr { border: 0; border-top: 1px solid #e6eaf0; margin: 32px 0; }

        /* Tighten when headings stack directly on top of each other. */
        .container h1 + h2, .container h1 + h3 { margin-top: 18px; }
        .container h2 + h3, .container h2 + h4 { margin-top: 14px; }
        .container h3 + h4 { margin-top: 12px; }

        /* No top margin on first child of any content block. */
        .container > :first-child,
        .container .page-content > :first-child { margin-top: 0; }

        footer {
            border-top: 1px solid #e0e0e0;
            padding: 24px 5%;
            text-align: center;
            color: #888;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: inline-flex;
                position: absolute;
                left: 5%;
                top: 50%;
                transform: translateY(-50%);
                z-index: 2;
            }
            .top-bar { padding: 12px 5%; }
            .top-bar .icon-area--right {
                position: absolute;
                right: 5%;
                top: 50%;
                transform: translateY(-50%);
                z-index: 2;
            }
            .icon-area--left { display: none; }
            .logo-area { flex: 1 1 100%; justify-content: center; }
            .logo-area img { height: 64px; }

            nav { padding: 0; }
            nav ul {
                flex-direction: column;
                gap: 0;
                display: none;
                border-top: 1px solid #e0e0e0;
            }
            nav.open ul { display: flex; }
            nav ul li { width: 100%; text-align: center; }
            nav ul li a {
                display: block;
                padding: 16px 5%;
                border-bottom: 1px solid #f0f0f0;
                font-size: 16px;
            }
        }

        @yield('styles')
    </style>
</head>
<body>

    <header>
        <div class="top-bar">
            <button class="menu-toggle" aria-label="Menu" onclick="document.getElementById('main-nav').classList.toggle('open')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>

            <div class="icon-area icon-area--left"></div>

            <div class="logo-area">
                <a href="/"><img src="{{ asset('img/simbuktu-logo.png') }}" alt="Simbuktu Logo"></a>
            </div>

            <div class="icon-area icon-area--right">
                <a href="/simulation/login" aria-label="Log ind">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </a>
            </div>
        </div>

        <nav id="main-nav">
            <ul>
                @foreach($menu as $item)
                    @php
                        $itemPath = ltrim($item->url(), '/') ?: '/';
                        $sectionActive = request()->is($itemPath);
                        if (!$sectionActive && $item->children->isNotEmpty()) {
                            foreach ($item->children as $sub) {
                                if (request()->is(ltrim($sub->url(), '/'))) { $sectionActive = true; break; }
                            }
                        }
                    @endphp
                    <li><a href="{{ $item->url() }}" class="{{ $sectionActive ? 'active' : '' }}">{{ $item->title }}</a></li>
                @endforeach
            </ul>
        </nav>
    </header>

    @yield('content')

    <footer>
        &copy; {{ date('Y') }} Simbuktu
        @auth
            @if(auth()->user()->is_admin && !empty($editUrl))
                · <a href="{{ $editUrl }}" style="color:#3498db;text-decoration:none;">Rediger denne side</a>
                · <a href="/cms/create{{ isset($page) && $page->parent_id ? '?parent='.$page->parent_id : (isset($page) ? '?parent='.$page->id : '') }}" style="color:#3498db;text-decoration:none;">+ Opret ny side</a>
            @endif
        @endauth
    </footer>

</body>
</html>
