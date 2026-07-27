@extends('site.layout')

@section('title', 'Om Simbuktu')
@section('description', 'Simbuktu er både teknologihus og konsulenthus. Vi bygger simulationsmotorer til læring — og de scenarier, der skal køre på dem.')

@section('body')

<section class="pagehead">
    <div class="shell pagehead__grid">
        <div>
            <p class="tag rise">Om os</p>
            <h1 class="rise" style="--delay: 90ms;">Vi bygger de situationer, man ellers <em class="mark" style="font-style: italic;">kun læser om</em></h1>
        </div>
        <p class="lede rise" style="--delay: 180ms;">
            Simbuktu er et lille hus med to ben: vi udvikler platformene, og vi bygger de
            scenarier, der kører på dem — sammen med de organisationer, der skal bruge dem.
        </p>
    </div>
</section>

{{-- ── Overbevisningen ─────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="split split--wide-left">
            <p class="pull" data-reveal>
                Erfaring er den bedste lærer. <em>Den er bare dyr.</em>
            </p>
            <div class="prose" data-reveal style="--delay: 100ms;">
                <p>
                    Alle, der har stået i en rigtig krise, har lært noget af den. Problemet er
                    prisen: en rigtig krise koster tillid, søvn og nogle gange folks job — og man
                    får kun lov at lære af den én gang, bagefter.
                </p>
                <p>
                    En simulation giver den samme erfaring uden regningen. Man må gerne træffe
                    den forkerte beslutning. Man må gerne se den vokse. Og man må gerne prøve
                    igen på tirsdag.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ── To ben ─────────────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="section__head">
            <div>
                <p class="tag" data-reveal>Hvad vi er</p>
                <h2 data-reveal style="--delay: 60ms;">Halvt teknologihus, halvt konsulenthus</h2>
            </div>
            <p class="prose" data-reveal style="--delay: 120ms;">
                Det ene fungerer ikke uden det andet. En god platform med dårlige scenarier er
                stadig kedelig e-læring — og det bedste scenarie i verden hjælper ikke, hvis det
                ligger i et dokument.
            </p>
        </div>

        <div class="grid grid--2">
            <article class="card" data-reveal>
                <span class="card__no">Ben 01</span>
                <h3>Vi bygger motorerne</h3>
                <p>
                    Hver type simulation har sin egen motor. Situationroom er den første, vi har
                    sat i drift — bygget til mediekrise. De næste bygger vi ovenpå de samme
                    komponenter: simulerede aktører, hændelsesforløb, konsekvenser og debriefing.
                </p>
            </article>
            <article class="card" data-reveal style="--delay: 100ms;">
                <span class="card__no">Ben 02</span>
                <h3>Vi bygger scenarierne</h3>
                <p>
                    Motoren er tom, indtil den fyldes med noget, der ligner jeres virkelighed. Det
                    gør vi sammen med jer i en workshop — eller I gør det selv bagefter i
                    scenariebyggeren. Partnere kan bygge deres egne og sælge dem videre.
                </p>
            </article>
        </div>
    </div>
</section>

{{-- ── Principper ─────────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="section__head">
            <div>
                <p class="tag" data-reveal>Sådan arbejder vi</p>
                <h2 data-reveal style="--delay: 60ms;">Fire ting, vi holder fast i</h2>
            </div>
        </div>

        <div class="grid grid--2">
            <article class="card" data-reveal>
                <span class="card__no">01</span>
                <h3>Det skal ligne jer</h3>
                <p>
                    Generiske cases giver generisk læring. Vi bruger jeres fagområde, jeres kanaler
                    og de sager, I faktisk ligger vågne over.
                </p>
            </article>
            <article class="card" data-reveal style="--delay: 80ms;">
                <span class="card__no">02</span>
                <h3>Konsekvens frem for quiz</h3>
                <p>
                    Ingen multiple choice, ingen point for at gætte det rigtige svar. Man skriver
                    og handler selv, og situationen svarer igen.
                </p>
            </article>
            <article class="card" data-reveal style="--delay: 160ms;">
                <span class="card__no">03</span>
                <h3>Debriefingen er halvdelen</h3>
                <p>
                    Oplevelsen alene er underholdning. Det er opsamlingen bagefter, der gør den til
                    noget, organisationen kan bruge.
                </p>
            </article>
            <article class="card" data-reveal style="--delay: 240ms;">
                <span class="card__no">04</span>
                <h3>Vi siger også nej</h3>
                <p>
                    Ikke alt skal simuleres. Hvis en halv dags samtale løser problemet bedre end en
                    platform, siger vi det.
                </p>
            </article>
        </div>
    </div>
</section>

{{-- ── Personen ───────────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="split split--wide-right split--top">
            <figure class="portrait" data-reveal style="margin: 0;">
                <img src="/site/anders.jpg" width="720" height="720"
                     alt="Anders, stifter af Simbuktu" loading="lazy">
                <figcaption>Anders — Simbuktu</figcaption>
            </figure>

            <div>
                <p class="tag" data-reveal>Bag Simbuktu</p>
                <h2 class="mt-1" data-reveal style="--delay: 60ms;">Det er mig, I kommer til at tale med</h2>
                <div class="prose mt-2" data-reveal style="--delay: 120ms;">
                    <p>
                        Simbuktu er et lille hus, og det er en fordel for jer: der er ingen
                        salgsafdeling mellem jer og den, der bygger tingene. Den samtale, I tager
                        i starten, er med den samme person, der sidder med scenariet bagefter.
                    </p>
                    <p>
                        Ring endelig, hvis I hellere vil tale end skrive. Det er tit hurtigere at
                        finde ud af på fem minutter, om der er noget her for jer.
                    </p>
                </div>

                <dl class="spec mt-3" data-reveal style="--delay: 160ms;">
                    <dt>Telefon</dt>
                    <dd><a class="link" href="tel:+4561678913">+45 61 67 89 13</a></dd>

                    <dt>Platform</dt>
                    <dd><a class="link" href="https://situationroom.dk" rel="noopener">situationroom.dk</a></dd>

                    <dt>Skriv</dt>
                    <dd><a class="link" href="/kontakt">Send en besked her på siden</a></dd>
                </dl>
            </div>
        </div>
    </div>
</section>

@include('site.partials.closer', [
    'kicker'  => 'Kontakt',
    'heading' => 'Skal vi tage <em class="mark">en snak</em> om det?',
    'body'    => 'Første samtale koster ingenting og forpligter ingenting. I værste fald bliver I klogere på, hvad I egentlig vil træne.',
])

@endsection
