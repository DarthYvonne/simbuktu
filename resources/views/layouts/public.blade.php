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

        /* Parent menu item with children — dropdown on hover (desktop) and on
           tap (mobile). Caret hints at the dropdown. */
        nav ul li.has-children { position: relative; }
        nav ul li.has-children > a::after {
            content: ''; display: inline-block;
            width: 6px; height: 6px;
            border-right: 1.5px solid currentColor;
            border-bottom: 1.5px solid currentColor;
            transform: rotate(45deg) translateY(-2px);
            margin-left: 7px;
            opacity: 0.55;
            transition: transform 0.15s;
        }
        nav ul li.has-children:hover > a::after,
        nav ul li.has-children.open > a::after {
            transform: rotate(45deg) translateY(0);
            opacity: 0.85;
        }
        .dropdown {
            position: absolute;
            top: 100%; left: 50%;
            transform: translateX(-50%) translateY(4px);
            min-width: 200px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 8px 24px -8px rgba(15,23,42,0.18);
            padding: 6px;
            display: none;
            z-index: 50;
            list-style: none;
            flex-direction: column;
            gap: 0;
        }
        nav ul li.has-children:hover > .dropdown,
        nav ul li.has-children.open > .dropdown {
            display: flex;
        }
        .dropdown li { width: 100%; }
        .dropdown li a {
            display: block;
            padding: 8px 14px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0.3px;
            text-transform: none;
            color: #2c3e50;
            white-space: nowrap;
        }
        .dropdown li a:hover { background: #f4f6f8; color: #3498db; }
        .dropdown li a.active { background: #3498db; color: #fff; }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 60px 5%;
        }

        .container h1 { font-size: 2.5rem; font-weight: 700; margin-bottom: 24px; color: #2c3e50; }
        .container h2 { font-size: 1.75rem; font-weight: 700; margin: 32px 0 16px; }
        .container p  { font-size: 1.1rem; color: #555; margin-bottom: 16px; line-height: 1.7; }
        .container ul, .container ol { margin: 0 0 16px 24px; }

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
            /* On mobile, the dropdown flattens into the column — no overlay. */
            nav ul li.has-children > a::after { display: none; }
            .dropdown {
                position: static;
                transform: none;
                display: flex !important;
                border: 0; box-shadow: none; padding: 0;
                min-width: 0; background: #fafbfc;
            }
            .dropdown li a {
                padding: 12px 5%;
                font-size: 14px;
                border-bottom: 1px solid #f0f0f0;
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
                        $hasChildren = $item->children->isNotEmpty();
                        $sectionActive = request()->is($itemPath);
                        if (!$sectionActive && $hasChildren) {
                            foreach ($item->children as $sub) {
                                if (request()->is(ltrim($sub->url(), '/'))) { $sectionActive = true; break; }
                            }
                        }
                    @endphp
                    <li class="{{ $hasChildren ? 'has-children' : '' }}">
                        <a href="{{ $item->url() }}" class="{{ $sectionActive ? 'active' : '' }}">{{ $item->title }}</a>
                        @if($hasChildren)
                            <ul class="dropdown">
                                @foreach($item->children as $sub)
                                    @php $subPath = ltrim($sub->url(), '/'); @endphp
                                    <li><a href="{{ $sub->url() }}" class="{{ request()->is($subPath) ? 'active' : '' }}">{{ $sub->title }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <script>
    // Touch / no-hover: tap the parent to open the dropdown without navigating
    // (only when the dropdown isn't already open).
    (function () {
        document.querySelectorAll('nav li.has-children > a').forEach(function (a) {
            a.addEventListener('click', function (e) {
                var li = a.parentElement;
                if (!matchMedia('(hover: none)').matches) return; // desktop: normal navigation
                if (!li.classList.contains('open')) {
                    e.preventDefault();
                    document.querySelectorAll('nav li.has-children.open').forEach(function (o) { if (o !== li) o.classList.remove('open'); });
                    li.classList.add('open');
                }
            });
        });
        document.addEventListener('click', function (e) {
            if (!e.target.closest('nav li.has-children')) {
                document.querySelectorAll('nav li.has-children.open').forEach(function (o) { o.classList.remove('open'); });
            }
        });
    })();
    </script>

    @yield('content')

    <footer>
        &copy; {{ date('Y') }} Simbuktu
        @auth
            @if(auth()->user()->is_admin && !empty($editUrl))
                · <a href="{{ $editUrl }}" style="color:#3498db;text-decoration:none;">Rediger denne side</a>
            @endif
        @endauth
    </footer>

</body>
</html>
