@extends('site.layout')

@section('title', 'Situationroom — Simbuktus første motor')
@section('description', 'Situationroom er Simbuktus platform til mediekrise og krisekommunikation: spil krisen igennem i realtid, med simulerede journalister, borgere og influencere.')

@section('body')

<section class="pagehead">
    <div class="shell pagehead__grid">
        <div>
            <p class="tag rise">Motor 01 — i drift</p>
            <h1 class="rise" style="--delay: 90ms;">Situationroom</h1>
            <span class="status status--live rise" style="--delay: 140ms;">Tilgængelig i dag</span>
        </div>
        <div>
            <p class="lede rise" style="--delay: 180ms;">
                En platform, hvor man spiller sig igennem en mediekrise i realtid. Deltagerne
                træffer beslutninger, og pressen, borgerne og de sociale medier reagerer på dem —
                mens det står på.
            </p>
            <div class="btn-row mt-3 rise" style="--delay: 240ms;">
                <a class="btn btn--primary" href="/kontakt">
                    Book en demo
                    <span class="btn__arrow" aria-hidden="true">→</span>
                </a>
                <a class="btn btn--ghost" href="https://situationroom.dk" rel="noopener">Besøg situationroom.dk ↗</a>
            </div>
        </div>
    </div>
</section>

{{-- ── Hvad der sker ───────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="split split--top">
            <div>
                <p class="tag" data-reveal>Oplevelsen</p>
                <h2 class="mt-1" data-reveal style="--delay: 60ms;">Krisen skriver sig selv — ud fra det, I gør</h2>
                <div class="prose mt-2" data-reveal style="--delay: 120ms;">
                    <p>
                        Der er ingen forudbestemte forgreninger at vælge imellem. Deltagerne skriver
                        deres egne svar, og simulationen bygger videre på dem: artikler bliver skrevet,
                        indslag bliver produceret, opslag bliver delt.
                    </p>
                    <p>
                        Står der noget dumt i et svar, står det i avisen bagefter. Med deltagerens
                        eget navn på.
                    </p>
                </div>

                <ul class="ticklist mt-3" data-reveal style="--delay: 160ms;">
                    <li>Nyhedsartikler og indslag genereres undervejs ud fra deltagernes valg</li>
                    <li>AI-drevne karakterer med egne personligheder, motiver og tålmodighed</li>
                    <li>Deltagerne skriver direkte med pressen, kolleger og influencere</li>
                    <li>Sagen kan tippe — og kan reddes — undervejs</li>
                </ul>
            </div>

            <div data-reveal style="--delay: 140ms;">
                <div class="clipping">
                    <div class="clipping__masthead">
                        <span>Dagbladet</span>
                        <span class="clipping__date">Kl. 21.14 — Opdateret</span>
                    </div>
                    <h3>Kommunen afviser at svare på spørgsmål om sagen</h3>
                    <p>
                        Efter flere dages tavshed valgte kommunen i aftes at henvise til en skriftlig
                        udtalelse. Den besvarer ikke de spørgsmål, flere pårørende har stillet.
                    </p>
                    <p>
                        "Vi har ikke fået noget svar overhovedet," siger en af dem til Dagbladet.
                    </p>
                    <div class="clipping__byline">Genereret i simulationen · reagerer på deltagerens svar</div>
                </div>
                <span class="mock-note">Eksempel — sådan kan et udfald se ud</span>
            </div>
        </div>
    </div>
</section>

{{-- ── Samtalen ────────────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="split split--wide-right split--top">
            <div data-reveal>
                <div class="thread">
                    <div class="thread__head">
                        <div class="thread__avatar" aria-hidden="true"></div>
                        <div class="thread__who">
                            Maja Ravn
                            <span>Journalist · deadline om 40 min</span>
                        </div>
                    </div>

                    <div class="msg">
                        Hej. Jeg har tre kilder, der siger, at I kendte til problemet allerede i marts.
                        Kan I bekræfte det?
                        <span class="msg__time">21.02</span>
                    </div>
                    <div class="msg msg--you">
                        Vi undersøger sagen og vender tilbage, når vi ved mere.
                        <span class="msg__time">21.09</span>
                    </div>
                    <div class="msg">
                        Så I afviser ikke, at I vidste det i marts? Jeg skriver historien nu — vil I
                        have en kommentar med, eller skal jeg skrive, at I ikke ønsker at svare?
                        <span class="msg__time">21.10</span>
                    </div>
                </div>
                <span class="mock-note">Eksempel — deltageren skriver selv svaret</span>
            </div>

            <div>
                <p class="tag" data-reveal>Modspillerne</p>
                <h2 class="mt-1" data-reveal style="--delay: 60ms;">De giver sig ikke, fordi I gerne vil videre</h2>
                <div class="prose mt-2" data-reveal style="--delay: 120ms;">
                    <p>
                        Karaktererne i simulationen har hver deres dagsorden og deres egen
                        tålmodighed. Journalisten har en deadline. Borgeren er vred på noget,
                        der er sket for længe siden. Influenceren er ligeglad med jeres
                        forbehold.
                    </p>
                    <p>
                        De husker, hvad deltagerne har sagt tidligere, og de bruger det. Det er
                        derfor, det føles ubehageligt på den rigtige måde — og derfor, det
                        sidder fast bagefter.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Byg selv ────────────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="section__head">
            <div>
                <p class="tag" data-reveal>Scenariebyggeren</p>
                <h2 data-reveal style="--delay: 60ms;">I kan bygge jeres egne sager</h2>
            </div>
            <p class="prose" data-reveal style="--delay: 120ms;">
                Platformen er ikke en pakke med færdige kurser. Det er et værktøj til at bygge
                de situationer, der er relevante for netop jer.
            </p>
        </div>

        <div class="grid grid--3">
            <article class="card" data-reveal>
                <span class="card__no">For organisationer</span>
                <h3>Jeres egne scenarier</h3>
                <p>
                    Byg de sager, I frygter, med jeres egne aktører og jeres eget fagsprog. Kør dem
                    igen og igen med nye hold, uden at vi behøver være med.
                </p>
            </article>
            <article class="card" data-reveal style="--delay: 90ms;">
                <span class="card__no">For partnere</span>
                <h3>Udviklerkonti</h3>
                <p>
                    Konsulenthuse og undervisere kan få en udviklerkonto, bygge specialiserede
                    scenarier til deres egne kunder og sælge dem videre som tilkøb til deres
                    ydelser.
                </p>
            </article>
            <article class="card" data-reveal style="--delay: 180ms;">
                <span class="card__no">Med os</span>
                <h3>Bygget i en workshop</h3>
                <p>
                    Vi holder en workshop med jeres kommunikationsfolk og bygger scenarierne
                    undervejs. I går hjem med både træningen og materialet.
                </p>
            </article>
        </div>
    </div>
</section>

{{-- ── Spørgsmål ───────────────────────────────────────────────────────── --}}
<section class="section">
    <div class="shell">
        <div class="split split--wide-left split--top">
            <div>
                <p class="tag" data-reveal>Spørgsmål</p>
                <h2 class="mt-1" data-reveal style="--delay: 60ms;">Det plejer folk at spørge om</h2>
            </div>

            <div data-reveal style="--delay: 100ms;">
                <details class="qa">
                    <summary>Skal vi installere noget?</summary>
                    <div class="qa__body">
                        Nej. Situationroom kører i browseren. Deltagerne får et link og logger ind.
                    </div>
                </details>
                <details class="qa">
                    <summary>Kan flere spille sammen?</summary>
                    <div class="qa__body">
                        Ja. Det kan køre som singleplayer, hvor alle andre aktører simuleres, eller
                        som multiplayer, hvor flere sidder i samme sag og skal koordinere undervejs.
                    </div>
                </details>
                <details class="qa">
                    <summary>Skal I være med, når vi kører det?</summary>
                    <div class="qa__body">
                        Ikke nødvendigvis. Mange starter med et forløb, hvor vi faciliterer og står
                        for debriefingen, og kører det derefter selv med nye hold.
                    </div>
                </details>
                <details class="qa">
                    <summary>Kan vi bruge vores egne sager?</summary>
                    <div class="qa__body">
                        Ja — og det er som regel dér, det bliver ubehageligt nok til at virke.
                        Scenarierne bygges ud fra jeres virkelighed, enten af jer i scenariebyggeren
                        eller sammen med os i en workshop.
                    </div>
                </details>
                <details class="qa">
                    <summary>Hvad koster det?</summary>
                    <div class="qa__body">
                        Det afhænger af, om I skal have bygget scenarier, om I vil have os med til at
                        facilitere, og hvor mange der skal spille. Ring, så giver vi et konkret bud.
                    </div>
                </details>
            </div>
        </div>
    </div>
</section>

@include('site.partials.closer', [
    'kicker'  => 'Situationroom',
    'heading' => 'Vil I prøve <em class="mark">en krise</em>, før den kommer?',
    'body'    => 'Book en demo, så viser vi platformen og taler om, hvilken sag der ville være den rigtige at bygge for jer.',
])

@endsection
