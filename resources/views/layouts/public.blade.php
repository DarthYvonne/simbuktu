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

        header { border-bottom: 1px solid #e0e0e0; background-color: #ffffff; }

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
            transition: color 0.2s;
        }
        nav ul li a:hover,
        nav ul li a.active { color: #3498db; }

        nav ul li { display: flex; flex-direction: column; align-items: center; gap: 4px; }
        .subnav {
            display: flex; gap: 18px; flex-wrap: wrap; justify-content: center;
            list-style: none;
        }
        .subnav li a {
            font-size: 13px;
            font-weight: 400;
            letter-spacing: 0.5px;
            text-transform: none;
            color: #7a8694;
        }
        .subnav li a:hover,
        .subnav li a.active { color: #3498db; }

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
                    @php $itemPath = ltrim($item->url(), '/') ?: '/'; @endphp
                    <li>
                        <a href="{{ $item->url() }}" class="{{ request()->is($itemPath) ? 'active' : '' }}">{{ $item->title }}</a>
                        @if($item->children->isNotEmpty())
                            <ul class="subnav">
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
