<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Simbuktu — læring gennem simulationer')</title>
    <meta name="description" content="@yield('description', 'Simbuktu bygger simulationer, hvor deltagerne træffer beslutninger under pres og oplever konsekvenserne. Første motor i drift: Situationroom.')">

    <meta property="og:site_name" content="Simbuktu">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="da_DK">
    <meta property="og:title" content="@yield('title', 'Simbuktu — læring gennem simulationer')">
    <meta property="og:description" content="@yield('description', 'Simbuktu bygger simulationer, hvor deltagerne træffer beslutninger under pres og oplever konsekvenserne.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="/favicon.png" sizes="any">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/site/simbuktu.css?v=1">

    {{-- Opt in to the reveal animations only when scripting can undo them.
         Inline and before the stylesheet is applied, so nothing flashes. --}}
    <script>document.documentElement.classList.add('js-reveal');</script>
</head>
<body>
<a class="skip" href="#indhold">Spring til indhold</a>

@php
    $path = trim(request()->path(), '/');
    $nav = [
        'saadan-virker-det' => 'Sådan virker det',
        'loesninger'        => 'Løsninger',
        'situationroom'     => 'Situationroom',
        'om'                => 'Om Simbuktu',
    ];
@endphp

<header class="masthead">
    <div class="shell masthead__inner">
        <a class="brand" href="/" aria-label="Simbuktu — forside">
            @include('site.partials.mark')
            <span class="brand__word">Simbuktu</span>
        </a>

        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="hovedmenu">Menu</button>

        <nav class="nav" id="hovedmenu" data-nav data-open="false" aria-label="Hovedmenu">
            @foreach ($nav as $slug => $label)
                <a href="/{{ $slug }}" @if ($path === $slug) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
            <a class="btn btn--primary" href="/kontakt">
                Book en samtale
                <span class="btn__arrow" aria-hidden="true">→</span>
            </a>
        </nav>
    </div>
</header>

<main id="indhold">
    @yield('body')
</main>

<footer class="footer">
    <div class="shell">
        <div class="footer__grid">
            <div>
                <a class="brand" href="/" aria-label="Simbuktu — forside">
                    @include('site.partials.mark')
                    <span class="brand__word">Simbuktu</span>
                </a>
                <p class="muted mt-2" style="max-width: 30ch; font-size: 0.93rem;">
                    Vi bygger simulationer, hvor man lærer ved at handle — og ved at se hvad handlingen fører til.
                </p>
            </div>

            <div>
                <h4>Sider</h4>
                <ul>
                    <li><a href="/saadan-virker-det">Sådan virker det</a></li>
                    <li><a href="/loesninger">Løsninger</a></li>
                    <li><a href="/situationroom">Situationroom</a></li>
                    <li><a href="/om">Om Simbuktu</a></li>
                    <li><a href="/kontakt">Kontakt</a></li>
                </ul>
            </div>

            <div>
                <h4>Kontakt</h4>
                <ul>
                    <li><a href="tel:+4561678913">+45 61 67 89 13</a></li>
                    <li><a href="https://situationroom.dk" rel="noopener">situationroom.dk</a></li>
                </ul>
            </div>
        </div>

        <div class="footer__base">
            <span>© {{ date('Y') }} Simbuktu</span>
            <span>Læring gennem simulationer</span>
        </div>
    </div>
</footer>

<script src="/site/simbuktu.js?v=1" defer></script>
</body>
</html>
