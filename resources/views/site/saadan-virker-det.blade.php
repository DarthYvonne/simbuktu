@extends('site.layout')

@section('title', 'Sådan virker det — Simbuktu')
@section('description', 'Fra scenarie til debriefing: sådan er en Simbuktu-simulation bygget op, og sådan forløber den for deltagerne.')

@section('body')

<section class="pagehead">
    <div class="shell pagehead__grid">
        <div>
            <p class="tag rise">Metoden</p>
            <h1 class="rise" style="--delay: 90ms;">Fra scenarie til <em class="mark" style="font-style: italic;">debriefing</em></h1>
        </div>
        <p class="lede rise" style="--delay: 180ms;">
            En simulation er ikke et spil, man vinder. Det er en situation, man står i — og som
            reagerer på alt, hvad man gør. Her er de fem faser, ethvert forløb er bygget over.
        </p>
    </div>
</section>

<div class="shell">

    {{-- 01 ────────────────────────────────────────────────────────────── --}}
    <article class="step">
        <div>
            <p class="tag" data-reveal>Fase 01</p>
            <h2 data-reveal style="--delay: 60ms;">Vi bygger scenariet</h2>
            <div class="prose" data-reveal style="--delay: 120ms;">
                <p>
                    Vi starter med den sag, I helst ikke vil have. Sammen fastlægger vi, hvad der er
                    sket, hvornår deltagerne træder ind i det, hvad der allerede er sluppet ud, og
                    hvem der har en interesse i sagen.
                </p>
                <p>
                    Det afgørende er genkendeligheden. Når scenariet ligner jeres hverdag helt ned i
                    detaljen — jeres fagområde, jeres kanaler, jeres type af modspillere — kan ingen
                    trække sig med et <strong>"det ville aldrig ske hos os"</strong>.
                </p>
            </div>
        </div>

        <div class="step__art" data-reveal style="--delay: 100ms;">
            <svg class="diagram" viewBox="0 0 460 300" role="img"
                 aria-label="Diagram: scenariets parametre indstilles på fire skalaer.">
                @php
                    $axes = [
                        ['label' => 'Sagen',         'y' => 46,  'x' => 300],
                        ['label' => 'Tidspunkt',     'y' => 122, 'x' => 175],
                        ['label' => 'Aktører',       'y' => 198, 'x' => 380],
                        ['label' => 'Sværhedsgrad',  'y' => 274, 'x' => 245],
                    ];
                @endphp
                @foreach ($axes as $i => $axis)
                    <text class="glyph" style="--i: {{ $i }};" x="0" y="{{ $axis['y'] - 14 }}">{{ $axis['label'] }}</text>
                    <path class="trace trace--faint" style="--i: {{ $i }};" d="M0 {{ $axis['y'] }} H 440"/>
                    <path class="trace trace--hot" style="--i: {{ $i }};" d="M0 {{ $axis['y'] }} H {{ $axis['x'] }}"/>
                    <circle class="node node--hot" style="--i: {{ $i }};" cx="{{ $axis['x'] }}" cy="{{ $axis['y'] }}" r="6"/>
                @endforeach
            </svg>
        </div>
    </article>

    {{-- 02 ────────────────────────────────────────────────────────────── --}}
    <article class="step">
        <div>
            <p class="tag" data-reveal>Fase 02</p>
            <h2 data-reveal style="--delay: 60ms;">Vi befolker det</h2>
            <div class="prose" data-reveal style="--delay: 120ms;">
                <p>
                    En simulation er kun så god som de mennesker, der er i den. Vi bygger aktørerne:
                    journalisten med en vinkel, den vrede borger, kollegaen der gerne vil hjælpe men
                    kommer til at gøre det værre, influenceren der lugter en historie.
                </p>
                <p>
                    Hver aktør har sit eget temperament, sine egne motiver og sin egen hukommelse.
                    De følger ikke et manuskript — de reagerer på det, deltagerne faktisk skriver
                    og gør. <strong>Derfor kan man ikke spille det samme scenarie to gange ens.</strong>
                </p>
            </div>
        </div>

        <div class="step__art" data-reveal style="--delay: 100ms;">
            <svg class="diagram" viewBox="0 0 460 320" role="img"
                 aria-label="Diagram: et netværk af simulerede aktører forbundet på kryds og tværs.">
                <path class="trace trace--faint" style="--i: 0;" d="M70 90 L 190 44 L 330 96"/>
                <path class="trace trace--faint" style="--i: 1;" d="M70 90 L 150 180 L 300 210"/>
                <path class="trace" style="--i: 2;" d="M190 44 C 250 100 250 160 300 210"/>
                <path class="trace" style="--i: 3;" d="M150 180 C 190 240 260 250 330 96"/>
                <path class="trace trace--hot" style="--i: 4;" d="M300 210 C 350 250 400 230 410 160"/>
                <path class="trace trace--faint" style="--i: 5;" d="M70 90 C 40 170 60 250 150 180"/>
                <path class="trace trace--faint" style="--i: 5;" d="M330 96 L 410 160"/>

                <circle class="node" style="--i: 0;" cx="70"  cy="90"  r="7"/>
                <circle class="node node--hot" style="--i: 1;" cx="190" cy="44"  r="8"/>
                <circle class="node" style="--i: 2;" cx="330" cy="96"  r="7"/>
                <circle class="node" style="--i: 3;" cx="150" cy="180" r="7"/>
                <circle class="node node--hot" style="--i: 4;" cx="300" cy="210" r="8"/>
                <circle class="node node--open" style="--i: 5;" cx="410" cy="160" r="6"/>

                <text class="glyph glyph--hot" style="--i: 1;" x="190" y="26" text-anchor="middle">Journalist</text>
                <text class="glyph" style="--i: 0;" x="70" y="118" text-anchor="middle">Borger</text>
                <text class="glyph" style="--i: 2;" x="330" y="82" text-anchor="middle">Kollega</text>
                <text class="glyph" style="--i: 3;" x="150" y="206" text-anchor="middle">Politiker</text>
                <text class="glyph glyph--hot" style="--i: 4;" x="300" y="238" text-anchor="middle">Influencer</text>

                <path class="trace trace--faint" style="--i: 6;" d="M20 290 H 440"/>
                <text class="glyph" style="--i: 6;" x="20" y="310">Hver aktør har egne motiver</text>
            </svg>
        </div>
    </article>

    {{-- 03 ────────────────────────────────────────────────────────────── --}}
    <article class="step">
        <div>
            <p class="tag" data-reveal>Fase 03</p>
            <h2 data-reveal style="--delay: 60ms;">Deltagerne spiller</h2>
            <div class="prose" data-reveal style="--delay: 120ms;">
                <p>
                    Så starter uret. Henvendelserne begynder at komme ind, og deltagerne må forholde
                    sig til dem: svare eller tie, gå ud med det hele eller vente på flere oplysninger,
                    tage telefonen eller lade den ringe.
                </p>
                <p>
                    Det kan køre som <strong>singleplayer</strong>, hvor alle andre aktører simuleres,
                    eller som <strong>multiplayer</strong>, hvor flere afdelinger sidder i samme
                    situation og skal koordinere under tidspres — og opdager, hvor svært det er.
                </p>
            </div>
        </div>

        <div class="step__art" data-reveal style="--delay: 100ms;">
            <svg class="diagram" viewBox="0 0 460 300" role="img"
                 aria-label="Diagram: presset i sagen stiger og falder over tid, markeret med hændelser undervejs.">
                <path class="trace trace--faint" style="--i: 0;" d="M20 250 H 440"/>
                <path class="trace trace--faint" style="--i: 0;" d="M20 40 V 250"/>

                <path class="trace trace--hot" style="--i: 1;"
                      d="M20 232 C 80 232 90 170 130 168 S 180 200 210 130 S 260 60 300 96 S 350 190 390 172 S 425 120 435 116"/>

                <circle class="node node--hot" style="--i: 2;" cx="130" cy="168" r="5"/>
                <circle class="node node--hot" style="--i: 3;" cx="210" cy="130" r="5"/>
                <circle class="node node--hot" style="--i: 4;" cx="300" cy="96"  r="7"/>
                <circle class="node" style="--i: 5;" cx="390" cy="172" r="5"/>

                <text class="glyph" style="--i: 2;" x="130" y="154" text-anchor="middle">Opkald</text>
                <text class="glyph" style="--i: 3;" x="210" y="116" text-anchor="middle">Opslag</text>
                <text class="glyph glyph--hot" style="--i: 4;" x="300" y="80" text-anchor="middle">Forside</text>
                <text class="glyph" style="--i: 5;" x="392" y="196" text-anchor="middle">Svar</text>

                <text class="glyph" style="--i: 0;" x="20" y="272">T+0</text>
                <text class="glyph" style="--i: 0;" x="230" y="272" text-anchor="middle">Undervejs</text>
                <text class="glyph" style="--i: 0;" x="440" y="272" text-anchor="end">Slut</text>
                <text class="glyph" style="--i: 0;" x="0" y="32">Pres</text>
            </svg>
        </div>
    </article>

    {{-- 04 ────────────────────────────────────────────────────────────── --}}
    <article class="step">
        <div>
            <p class="tag" data-reveal>Fase 04</p>
            <h2 data-reveal style="--delay: 60ms;">Konsekvenserne indfinder sig</h2>
            <div class="prose" data-reveal style="--delay: 120ms;">
                <p>
                    Alt, hvad deltagerne gør, får følger. Et undvigende svar bliver til en rubrik.
                    Tavshed bliver til en anden historie, fortalt af nogen andre. En indrømmelse
                    på det rigtige tidspunkt kan tage luften ud af det hele.
                </p>
                <p>
                    Det er her læringen sætter sig. Ikke i at få at vide, at man bør svare hurtigt
                    — men i selv at mærke, hvad der skete, fordi man lod være.
                </p>
            </div>
        </div>

        <div class="step__art" data-reveal style="--delay: 100ms;">
            <svg class="diagram" viewBox="0 0 460 300" role="img"
                 aria-label="Diagram: én handling forgrener sig i flere mulige konsekvenser.">
                <path class="trace trace--hot" style="--i: 0;" d="M30 150 H 120"/>
                <path class="trace trace--hot" style="--i: 1;" d="M120 150 C 200 150 220 40 330 40"/>
                <path class="trace" style="--i: 2;" d="M120 150 C 200 150 220 100 330 100"/>
                <path class="trace" style="--i: 3;" d="M120 150 H 330"/>
                <path class="trace" style="--i: 4;" d="M120 150 C 200 150 220 205 330 205"/>
                <path class="trace trace--faint" style="--i: 5;" d="M120 150 C 200 150 220 262 330 262"/>

                <circle class="node node--open" style="--i: 0;" cx="30"  cy="150" r="5"/>
                <circle class="node node--hot"  style="--i: 1;" cx="120" cy="150" r="8"/>
                <circle class="node node--hot"  style="--i: 2;" cx="330" cy="40"  r="6"/>
                <circle class="node" style="--i: 3;" cx="330" cy="100" r="6"/>
                <circle class="node" style="--i: 4;" cx="330" cy="150" r="6"/>
                <circle class="node" style="--i: 5;" cx="330" cy="205" r="6"/>
                <circle class="node trace--faint" style="--i: 6;" cx="330" cy="262" r="4" fill="none" stroke="var(--steel)"/>

                <text class="glyph" style="--i: 0;" x="18" y="134">Valg</text>
                <text class="glyph glyph--hot" style="--i: 2;" x="346" y="44">Optrapning</text>
                <text class="glyph" style="--i: 3;" x="346" y="104">Ny vinkel</text>
                <text class="glyph" style="--i: 4;" x="346" y="154">Status quo</text>
                <text class="glyph" style="--i: 5;" x="346" y="209">Afdramatisering</text>
                <text class="glyph" style="--i: 6;" x="346" y="266">Glemt</text>
            </svg>
        </div>
    </article>

    {{-- 05 ────────────────────────────────────────────────────────────── --}}
    <article class="step">
        <div>
            <p class="tag" data-reveal>Fase 05</p>
            <h2 data-reveal style="--delay: 60ms;">Vi samler op</h2>
            <div class="prose" data-reveal style="--delay: 120ms;">
                <p>
                    Til sidst spoler vi tilbage gennem forløbet. Hvor tippede det? Hvilket svar
                    ændrede tonen? Hvad blev der besluttet i en fart, som ingen ville have besluttet
                    med ti minutter mere?
                </p>
                <p>
                    Debriefingen er der, hvor oplevelsen bliver til noget, man kan bruge på mandag:
                    konkrete greb, aftaler om hvem der gør hvad — og en fælles erfaring, hele
                    afdelingen kan referere til bagefter.
                </p>
            </div>
        </div>

        <div class="step__art" data-reveal style="--delay: 100ms;">
            <svg class="diagram" viewBox="0 0 460 260" role="img"
                 aria-label="Diagram: forløbet spoles tilbage, og de afgørende øjeblikke markeres.">
                <path class="trace trace--faint" style="--i: 0;" d="M20 120 H 440"/>
                <path class="trace trace--hot" style="--i: 1;" d="M20 120 H 265"/>

                @php $ticks = [70, 120, 175, 265, 330, 395]; @endphp
                @foreach ($ticks as $i => $tick)
                    <path class="trace {{ $tick === 265 ? 'trace--hot' : 'trace--faint' }}" style="--i: {{ $i + 2 }};"
                          d="M{{ $tick }} {{ $tick === 265 ? 88 : 104 }} V {{ $tick === 265 ? 152 : 136 }}"/>
                @endforeach

                <circle class="node node--hot" style="--i: 5;" cx="265" cy="120" r="9"/>
                <text class="glyph glyph--hot" style="--i: 5;" x="265" y="76" text-anchor="middle">Her tippede det</text>

                <text class="glyph" style="--i: 0;" x="20" y="180">Afspil forfra</text>
                <text class="glyph" style="--i: 0;" x="440" y="180" text-anchor="end">Slut</text>
                <path class="trace trace--faint" style="--i: 6;" d="M20 210 H 440"/>
                <text class="glyph" style="--i: 6;" x="20" y="234">Hvad tager vi med på mandag?</text>
            </svg>
        </div>
    </article>
</div>

{{-- ── Praktisk ────────────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="split split--wide-left split--top">
            <div>
                <p class="tag" data-reveal>Praktisk</p>
                <h2 class="mt-1" data-reveal style="--delay: 60ms;">Hvordan det kører i praksis</h2>
                <p class="prose mt-2" data-reveal style="--delay: 120ms;">
                    Rammerne aftaler vi med jer. Nogle vil have et enkelt forløb til hele
                    afdelingen; andre vil selv kunne bygge nye scenarier bagefter.
                </p>
                <div class="btn-row mt-3" data-reveal style="--delay: 180ms;">
                    <a class="btn btn--primary" href="/kontakt">
                        Book en samtale
                        <span class="btn__arrow" aria-hidden="true">→</span>
                    </a>
                </div>
            </div>

            <dl class="spec" data-reveal style="--delay: 140ms;">
                <dt>Format</dt>
                <dd>Singleplayer eller multiplayer på tværs af afdelinger</dd>

                <dt>Aktører</dt>
                <dd>Simulerede og AI-drevne — de svarer på det, deltagerne skriver</dd>

                <dt>Adgang</dt>
                <dd>Kører i browseren. Ingen installation</dd>

                <dt>Sprog</dt>
                <dd>Dansk</dd>

                <dt>Scenarier</dt>
                <dd>Bygget af os til jer — eller bygget af jer selv i scenariebyggeren</dd>

                <dt>Facilitering</dt>
                <dd>Med os på sidelinjen, eller kørt af jer selv bagefter</dd>
            </dl>
        </div>
    </div>
</section>

@include('site.partials.closer', [
    'kicker'  => 'Kom i gang',
    'heading' => 'Hvad er den sag, I <em class="mark">helst ikke</em> vil stå i?',
    'body'    => 'Det er som regel dér, vi starter. Fortæl os om den, så giver vi et bud på, hvordan den ser ud som simulation.',
])

@endsection
