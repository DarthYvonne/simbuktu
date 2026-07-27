@extends('site.layout')

@section('title', 'Simbuktu — læring gennem simulationer')
@section('description', 'Simbuktu bygger simulationer, hvor deltagerne træffer beslutninger under pres og oplever konsekvenserne med det samme. Første motor i drift: Situationroom.')

@section('body')

{{-- ── Hero ────────────────────────────────────────────────────────────── --}}
<section class="hero">
    <div class="shell hero__grid">
        <div>
            <p class="tag rise">Simbuktu</p>

            <h1 class="rise" style="--delay: 90ms;">
                Læring gennem
                <span class="breakline"><em class="mark" style="font-family: var(--serif); font-style: italic;">simulationer</em></span>
            </h1>

            <p class="lede rise" style="--delay: 180ms;">
                Det meste e-læring bliver glemt, fordi man læser om noget i stedet for at stå i det.
                Vi bygger simulationer, hvor deltagerne træffer beslutninger under pres — og ser
                konsekvenserne folde sig ud, mens de sker.
            </p>

            <div class="btn-row mt-3 rise" style="--delay: 260ms;">
                <a class="btn btn--primary" href="/kontakt">
                    Book en samtale
                    <span class="btn__arrow" aria-hidden="true">→</span>
                </a>
                <a class="btn btn--ghost" href="/saadan-virker-det">Sådan virker det</a>
            </div>

            <dl class="hero__meta rise" style="--delay: 340ms;">
                <div>
                    <dt>I drift</dt>
                    <dd>Situationroom — mediekrise</dd>
                </div>
                <div>
                    <dt>Format</dt>
                    <dd>Single- og multiplayer</dd>
                </div>
                <div>
                    <dt>Model</dt>
                    <dd>Platform + workshops</dd>
                </div>
            </dl>
        </div>

        {{-- Branching-scenario plot: one decision, flere fremtider. --}}
        <div class="rise" style="--delay: 200ms;">
            <svg class="diagram" viewBox="0 0 560 460" role="img"
                 aria-label="Diagram: et scenarie fører til en beslutning, som forgrener sig i tre mulige udfald.">

                <path class="trace trace--faint" style="--i: 5;" d="M300 230 C 350 230 360 300 415 300"/>
                <path class="trace trace--faint" style="--i: 5;" d="M300 230 C 350 230 360 168 415 168"/>

                <path class="trace trace--hot" style="--i: 0;" d="M46 230 H 150"/>
                <path class="trace trace--hot" style="--i: 1;" d="M150 230 C 240 230 250 96 350 96 H 415"/>
                <path class="trace" style="--i: 2;" d="M150 230 H 415"/>
                <path class="trace" style="--i: 3;" d="M150 230 C 240 230 250 366 350 366 H 415"/>

                <circle class="node node--open" style="--i: 0;" cx="46" cy="230" r="5"/>
                <circle class="node node--hot" style="--i: 1;" cx="150" cy="230" r="7"/>
                <circle class="node node--hot" style="--i: 2;" cx="415" cy="96" r="6"/>
                <circle class="node" style="--i: 3;" cx="415" cy="230" r="6"/>
                <circle class="node" style="--i: 4;" cx="415" cy="366" r="6"/>
                <circle class="node trace--faint" style="--i: 6;" cx="415" cy="168" r="4" fill="none" stroke="var(--steel)"/>
                <circle class="node trace--faint" style="--i: 6;" cx="415" cy="300" r="4" fill="none" stroke="var(--steel)"/>

                <circle class="pulse" cx="415" cy="96" r="5" fill="var(--amber)"/>

                <text class="glyph" style="--i: 0;" x="34" y="212">Scenarie</text>
                <text class="glyph glyph--hot" style="--i: 1;" x="150" y="212" text-anchor="middle">Beslutning</text>
                <text class="glyph glyph--hot" style="--i: 2;" x="430" y="100">Medieomtale</text>
                <text class="glyph" style="--i: 3;" x="430" y="234">Tillidstab</text>
                <text class="glyph" style="--i: 4;" x="430" y="370">Ro på sagen</text>

                <path class="trace trace--faint" style="--i: 7;" d="M46 430 H 500"/>
                <text class="glyph" style="--i: 7;" x="46" y="450">Tid →</text>
            </svg>
        </div>
    </div>
</section>

{{-- ── Problemet ───────────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="section__head">
            <div>
                <p class="tag" data-reveal>01 — Problemet</p>
                <h2 data-reveal style="--delay: 60ms;">
                    Ingen husker <em class="mark" style="font-style: italic;">et kursus</em>. Alle husker en krise.
                </h2>
            </div>
            <p class="prose" data-reveal style="--delay: 120ms;">
                Organisationer bruger enorme summer på e-læring, som ingen har lyst til at gennemføre
                — og som ikke virker, når det gælder. Der er to grunde til det, og de hænger sammen.
            </p>
        </div>

        <div class="grid grid--2">
            <article class="card" data-reveal>
                <span class="card__no">01</span>
                <h3>Det er kedeligt</h3>
                <p>
                    Slides, PDF'er og videoer beder deltagerne om at sidde stille og modtage.
                    Der er intet på spil, ingen konsekvens, ingen grund til at være opmærksom.
                    Det bliver klikket igennem, ikke gennemført.
                </p>
            </article>
            <article class="card" data-reveal style="--delay: 100ms;">
                <span class="card__no">02</span>
                <h3>Læringen rejser ikke</h3>
                <p>
                    Man kan læse alt om krisekommunikation og stadig gå i stå, når telefonen ringer
                    kl. 22.40 og journalisten allerede har en vinkel. Viden i hovedet flytter sig
                    ikke af sig selv ud i hænderne. Det gør handling under pres.
                </p>
            </article>
        </div>

        <div class="rule" aria-hidden="true"><span></span></div>

        <div class="split split--wide-left">
            <p class="pull" data-reveal>
                Man lærer ikke at svømme <em>af at læse om vand</em>.
            </p>
            <div class="prose" data-reveal style="--delay: 100ms;">
                <p>
                    En simulation vender det om. Deltageren er ikke publikum, men aktør. Der er et
                    scenarie, der er andre mennesker i det, og der er beslutninger, som får noget til
                    at ske. Det, man selv har gjort — og selv har set gå galt — sætter sig.
                </p>
                <p>
                    <a class="link" href="/saadan-virker-det">Se hvordan en simulation er skruet sammen →</a>
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ── Sådan virker det, kort ──────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="section__head">
            <div>
                <p class="tag" data-reveal>02 — Anatomien</p>
                <h2 data-reveal style="--delay: 60ms;">Fire dele, der gør en simulation til læring</h2>
            </div>
            <p class="prose" data-reveal style="--delay: 120ms;">
                Uanset om vi simulerer en mediekrise, et beredskab eller et phishing-angreb,
                er skelettet det samme.
            </p>
        </div>

        <div class="grid grid--2">
            <article class="card" data-reveal>
                <span class="card__no">Del 01</span>
                <h3>Scenariet</h3>
                <p>
                    Udgangspunktet, deltagerne træder ind i: en sag, en situation, et tidspunkt.
                    Bygget så tæt på jeres virkelighed, at ingen kan gemme sig bag "det ville
                    aldrig ske hos os".
                </p>
            </article>
            <article class="card" data-reveal style="--delay: 80ms;">
                <span class="card__no">Del 02</span>
                <h3>De simulerede aktører</h3>
                <p>
                    Journalister, borgere, kolleger, influencere, modparter. De har hver deres
                    dagsorden og reagerer på det, deltageren rent faktisk gør — ikke på et
                    forudbestemt manuskript.
                </p>
            </article>
            <article class="card" data-reveal style="--delay: 160ms;">
                <span class="card__no">Del 03</span>
                <h3>Beslutningerne</h3>
                <p>
                    Skal I svare nu eller vente? Udtale jer eller lade være? Hver beslutning
                    ændrer situationen, og der er ingen vej tilbage til forrige skærmbillede.
                </p>
            </article>
            <article class="card" data-reveal style="--delay: 240ms;">
                <span class="card__no">Del 04</span>
                <h3>Debriefingen</h3>
                <p>
                    Bagefter kan hele forløbet spoles tilbage: hvad skete der, hvornår tippede det,
                    hvad kunne have vendt det. Det er her, oplevelsen bliver til noget, man kan tage
                    med på arbejde.
                </p>
            </article>
        </div>
    </div>
</section>

{{-- ── Situationroom ───────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="panel">
            <div class="split split--wide-right split--top">
                <div>
                    <p class="tag" data-reveal>03 — I drift i dag</p>
                    <h2 class="mt-1" data-reveal style="--delay: 60ms;">Situationroom</h2>
                    <p class="lede mt-2" data-reveal style="--delay: 120ms;">
                        Vores første motor. En platform, hvor man spiller sig igennem en mediekrise
                        i realtid — og bliver hængt ud i pressen, hvis man håndterer den skidt.
                    </p>
                    <div class="btn-row mt-3" data-reveal style="--delay: 180ms;">
                        <a class="btn btn--ghost" href="/situationroom">Læs om Situationroom</a>
                        <a class="btn btn--ghost" href="https://situationroom.dk" rel="noopener">situationroom.dk ↗</a>
                    </div>
                </div>

                <div data-reveal style="--delay: 140ms;">
                    <ul class="ticklist">
                        <li>Nyheder, indslag og opslag bliver skabt undervejs ud fra deltagernes valg.</li>
                        <li>AI-drevne karakterer med egne personligheder, motiver og tålmodighed.</li>
                        <li>Deltagerne skriver med pressen, kolleger og influencere direkte i platformen.</li>
                        <li>Scenariebygger, så organisationer kan lave deres egne simulationer.</li>
                        <li>Udviklerkonti til partnere, der bygger og sælger scenarier videre.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Motorerne ───────────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="section__head">
            <div>
                <p class="tag" data-reveal>04 — Motorerne</p>
                <h2 data-reveal style="--delay: 60ms;">Én tilgang, mange slags simulationer</h2>
            </div>
            <p class="prose" data-reveal style="--delay: 120ms;">
                Situationroom er den første specialiserede motor, vi har sat i drift. De øvrige
                bygger vi på samme fundament — sammen med de kunder og partnere, der har brug for dem.
            </p>
        </div>

        <div>
            @php
                $engines = [
                    [
                        'no'     => '01',
                        'title'  => 'Krisekommunikation og medietræning',
                        'status' => 'I drift',
                        'live'   => true,
                        'body'   => 'Deltagerne står i en sag, der udvikler sig time for time. Pressen ringer, sociale medier koger, og de svar man giver, bliver til morgendagens forside. Kører som Situationroom.',
                    ],
                    [
                        'no'     => '02',
                        'title'  => 'Beredskab',
                        'status' => 'Bygges på bestilling',
                        'live'   => false,
                        'body'   => 'En kommune eller myndighed træner en situation med uklare oplysninger og mange aktører. Kan spilles i flere hold på tværs af organisationen — eller alene, hvor de øvrige aktører simuleres.',
                    ],
                    [
                        'no'     => '03',
                        'title'  => 'Cybersikkerhed',
                        'status' => 'Bygges på bestilling',
                        'live'   => false,
                        'body'   => 'Phishing, smishing og social engineering simuleret i medarbejdernes egne kanaler — mail og sms — så træningen sker der, hvor angrebet faktisk ville lande.',
                    ],
                    [
                        'no'     => '04',
                        'title'  => 'Undervisning i skoler og gymnasier',
                        'status' => 'Under udvikling med partnere',
                        'live'   => false,
                        'body'   => 'Elever prøver kræfter med demokratiske dilemmaer, videnskabelige kontroverser eller manosfæren — indefra, i et miljø hvor det er sikkert at tage fejl. Her bygger vi færdige scenarier sammen med faglige partnere.',
                    ],
                    [
                        'no'     => '05',
                        'title'  => 'Fremtidsscenarier',
                        'status' => 'Bygges på bestilling',
                        'live'   => false,
                        'body'   => 'Konsulenten sætter to akser op, og fire fremtider folder sig ud i krydsfeltet. Et strategiværktøj, hvor man kan bevæge sig rundt i scenarierne i stedet for at nøjes med at beskrive dem.',
                    ],
                    [
                        'no'     => '06',
                        'title'  => 'Skræddersyede scenarier',
                        'status' => 'Bygges på bestilling',
                        'live'   => false,
                        'body'   => 'Har I noget helt fjerde, der skal trænes? Motorerne er byggeklodser. Vi sætter dem sammen til det, jeres organisation faktisk står i.',
                    ],
                ];
            @endphp

            @foreach ($engines as $i => $engine)
                <article class="engine" data-reveal style="--delay: {{ $i * 60 }}ms;">
                    <div class="engine__no">{{ $engine['no'] }}</div>
                    <div>
                        <h3>{{ $engine['title'] }}</h3>
                        <span class="status {{ $engine['live'] ? 'status--live' : '' }}">{{ $engine['status'] }}</span>
                    </div>
                    <div class="engine__body">
                        <p>{{ $engine['body'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        <p class="mt-3" data-reveal>
            <a class="link" href="/loesninger">Se løsningerne i detaljer →</a>
        </p>
    </div>
</section>

{{-- ── Sådan arbejder vi sammen ────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="section__head">
            <div>
                <p class="tag" data-reveal>05 — Samarbejdet</p>
                <h2 data-reveal style="--delay: 60ms;">To måder at komme i gang på</h2>
            </div>
            <p class="prose" data-reveal style="--delay: 120ms;">
                Simbuktu er både et teknologihus og et konsulenthus. De fleste starter med det andet
                og ender med begge dele.
            </p>
        </div>

        <div class="grid grid--2">
            <article class="card" data-reveal>
                <span class="card__no">Model 01 — Platform</span>
                <h3>I bygger selv videre</h3>
                <p>
                    I får adgang til platformen og bygger jeres egne scenarier i den. Partnere og
                    konsulenthuse kan få en udviklerkonto, bygge specialiserede scenarier til deres
                    egne kunder og sælge dem videre som tilkøb. Vi holder motoren kørende, I ejer
                    indholdet.
                </p>
            </article>
            <article class="card" data-reveal style="--delay: 100ms;">
                <span class="card__no">Model 02 — Workshop</span>
                <h3>Vi bygger scenariet sammen</h3>
                <p>
                    Vi holder en workshop med jeres kommunikationsfolk og bygger jeres egne
                    scenarier undervejs — de sager, I frygter mest. Bagefter kan resten af
                    organisationen spille dem igen og igen, uden at vi behøver være med.
                </p>
            </article>
        </div>
    </div>
</section>

@include('site.partials.closer')

@endsection
