@extends('site.layout')

@section('title', 'Løsninger — Simbuktu')
@section('description', 'Krisekommunikation, beredskab, cybersikkerhed, undervisning og fremtidsscenarier — samme fundament, forskellige motorer.')

@section('body')

@php
    $loesninger = [
        [
            'id'      => 'krisekommunikation',
            'no'      => '01',
            'title'   => 'Krisekommunikation og medietræning',
            'status'  => 'I drift som Situationroom',
            'live'    => true,
            'lede'    => 'Sagen ruller, pressen ringer, og de sociale medier har allerede besluttet sig. Deltagerne står midt i det og skal svare — nu.',
            'body'    => [
                'Deltagerne modtager henvendelser fra journalister, borgere og influencere, og det de svarer, bliver til artikler, indslag og opslag i simulationen. Skriver man for defensivt, får man en ny historie oveni. Venter man for længe, fortæller nogen andre historien i stedet.',
                'Det er den motor, vi har kørt længst med, og den findes som en færdig platform, I kan bruge i dag.',
            ],
            'hvem'    => ['Kommunikationsafdelinger', 'Direktioner og ledergrupper', 'Pressevagter og talspersoner', 'Konsulenthuse, der laver medietræning'],
            'traener' => ['At svare præcist under tidspres', 'At vurdere hvornår man går ud, og hvornår man venter', 'At koordinere internt mens det brænder', 'At kende sine egne reflekser, før det gælder'],
            'link'    => ['/situationroom', 'Se Situationroom'],
        ],
        [
            'id'      => 'beredskab',
            'no'      => '02',
            'title'   => 'Beredskab',
            'status'  => 'Bygges på bestilling',
            'live'    => false,
            'lede'    => 'En hændelse udvikler sig, oplysningerne er modstridende, og der er mange aktører, som ikke er enige om noget.',
            'body'    => [
                'En kommune eller myndighed træner en situation med uklarheder: Hvad ved vi egentlig? Hvem har handlepligten? Hvad melder vi ud, når vi ikke ved nok endnu? Undervejs dukker nye oplysninger op — nogle af dem forkerte.',
                'Kan køres som multiplayer, hvor flere forvaltninger eller enheder sidder i samme hændelse på hver sin skærm, eller som singleplayer, hvor de øvrige aktører simuleres.',
            ],
            'hvem'    => ['Kommuner og regioner', 'Styrelser og myndigheder', 'Beredskabsstabe', 'Forsyning og kritisk infrastruktur'],
            'traener' => ['At handle på ufuldstændige oplysninger', 'Koordinering på tværs af enheder', 'Rollefordeling når presset stiger', 'Kommunikation til borgere midt i en hændelse'],
            'link'    => null,
        ],
        [
            'id'      => 'cybersikkerhed',
            'no'      => '03',
            'title'   => 'Cybersikkerhed',
            'status'  => 'Bygges på bestilling',
            'live'    => false,
            'lede'    => 'Phishing, smishing og social engineering — simuleret i medarbejdernes egne kanaler, ikke i et kursusmodul.',
            'body'    => [
                'Angrebet lander dér, hvor det virkelige angreb ville lande: i indbakken og på telefonen. Medarbejderen oplever selv, hvor overbevisende en velskrevet besked kan være, og hvor lidt der skal til, før man klikker.',
                'Bagefter ser man forløbet udefra: hvad var tegnene, hvornår kunne det være stoppet, og hvem skulle have været orienteret hvornår.',
            ],
            'hvem'    => ['IT- og sikkerhedsafdelinger', 'Organisationer med mange medarbejdere', 'Virksomheder underlagt NIS2 og lignende krav'],
            'traener' => ['At genkende et angreb i sin egen indbakke', 'At reagere rigtigt, når man har klikket', 'Interne meldeveje', 'Realistisk risikoforståelse frem for pligtmoduler'],
            'link'    => null,
        ],
        [
            'id'      => 'undervisning',
            'no'      => '04',
            'title'   => 'Undervisning i skoler og gymnasier',
            'status'  => 'Under udvikling med partnere',
            'live'    => false,
            'lede'    => 'Eleverne træder ind i en debat i stedet for at læse om den — og opdager, hvordan den trækker i dem.',
            'body'    => [
                'Demokratiske dilemmaer, videnskabelige kontroverser, manosfæren. Emner, der er svære at undervise i udefra, fordi de handler om, hvad der sker med én selv, når man er inde i dem.',
                'I en simulation kan eleverne prøve det indefra, i et rum hvor det er ufarligt at tage fejl — og bagefter tale om, hvad der egentlig fik dem til at mene noget. Her bygger vi færdige scenarier sammen med faglige partnere, så de kan bruges direkte i undervisningen.',
            ],
            'hvem'    => ['Grundskoler', 'Gymnasier og ungdomsuddannelser', 'Læremiddelproducenter', 'Faglige foreninger'],
            'traener' => ['Kildekritik i praksis', 'Demokratisk samtale under uenighed', 'Genkendelse af retoriske greb', 'Refleksion over egne reaktioner'],
            'link'    => null,
        ],
        [
            'id'      => 'fremtidsscenarier',
            'no'      => '05',
            'title'   => 'Fremtidsscenarier',
            'status'  => 'Bygges på bestilling',
            'live'    => false,
            'lede'    => 'To akser, fire fremtider. Ikke som en rapport, men som fire verdener man kan gå rundt i.',
            'body'    => [
                'Konsulenten sætter parametrene: de to usikkerheder, der betyder mest for organisationen. I krydsfeltet folder fire fremtider sig ud, og deltagerne kan bevæge sig ind i hver af dem og se, hvad der ville være rigtigt at gøre dér.',
                'Det gør scenarieøvelsen konkret. I stedet for fire beskrivelser i et dokument får man fire situationer, man har stået i.',
            ],
            'hvem'    => ['Strategi- og udviklingsafdelinger', 'Konsulenthuse', 'Direktioner og bestyrelser', 'Kommuner med planlægningsopgaver'],
            'traener' => ['Strategisk dømmekraft under usikkerhed', 'At tænke i flere fremtider samtidig', 'At afprøve beslutninger, før de træffes', 'Fælles billede af hvad der kan komme'],
            'link'    => null,
        ],
        [
            'id'      => 'skraeddersyet',
            'no'      => '06',
            'title'   => 'Skræddersyede scenarier',
            'status'  => 'Bygges på bestilling',
            'live'    => false,
            'lede'    => 'Har I noget helt andet, der skal trænes? Motorerne er byggeklodser.',
            'body'    => [
                'Forhandlinger, svære samtaler, whistleblowersager, tilsynsbesøg, politisk pres. Fælles for dem er, at de involverer mennesker med hver deres dagsorden — og det er præcis dét, vi bygger.',
                'Vi starter med en samtale om, hvad der faktisk går galt hos jer i dag. Derfra vurderer vi, om det kan simuleres — og siger til, hvis vi ikke tror på det.',
            ],
            'hvem'    => ['Alle med en situation, der ikke kan læses sig til'],
            'traener' => ['Det, I har brug for at kunne, når det gælder'],
            'link'    => ['/kontakt', 'Fortæl os om jeres situation'],
        ],
    ];
@endphp

<section class="pagehead">
    <div class="shell pagehead__grid">
        <div>
            <p class="tag rise">Løsninger</p>
            <h1 class="rise" style="--delay: 90ms;">Samme fundament, <em class="mark" style="font-style: italic;">forskellige</em> motorer</h1>
        </div>
        <p class="lede rise" style="--delay: 180ms;">
            Situationroom er den første motor, vi har sat i drift. De øvrige bygger vi på samme
            fundament — sammen med de kunder og partnere, der har brug for dem. Her er de, og
            her er hvad de kan.
        </p>
    </div>
    <div class="shell mt-3">
        <nav class="chips rise" style="--delay: 260ms;" aria-label="Gå til løsning">
            @foreach ($loesninger as $l)
                <a href="#{{ $l['id'] }}">{{ $l['no'] }} — {{ $l['title'] }}</a>
            @endforeach
        </nav>
    </div>
</section>

@foreach ($loesninger as $l)
    <section class="section" id="{{ $l['id'] }}">
        <div class="shell">
            <div class="section__head">
                <div>
                    <p class="tag" data-reveal>{{ $l['no'] }} — Motor</p>
                    <h2 data-reveal style="--delay: 60ms;">{{ $l['title'] }}</h2>
                    <span class="status {{ $l['live'] ? 'status--live' : '' }}" data-reveal style="--delay: 100ms;">{{ $l['status'] }}</span>
                </div>
                <div data-reveal style="--delay: 120ms;">
                    <p class="lede">{{ $l['lede'] }}</p>
                </div>
            </div>

            <div class="split split--wide-left split--top">
                <div class="prose" data-reveal>
                    @foreach ($l['body'] as $para)
                        <p>{{ $para }}</p>
                    @endforeach
                    @if ($l['link'])
                        <p><a class="link" href="{{ $l['link'][0] }}">{{ $l['link'][1] }} →</a></p>
                    @endif
                </div>

                <div class="grid grid--2" data-reveal style="--delay: 100ms;">
                    <div class="card">
                        <span class="card__no">Til hvem</span>
                        <ul class="ticklist">
                            @foreach ($l['hvem'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card">
                        <span class="card__no">Hvad man træner</span>
                        <ul class="ticklist">
                            @foreach ($l['traener'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endforeach

@include('site.partials.closer', [
    'kicker'  => 'Næste skridt',
    'heading' => 'Ingen af delene <em class="mark">helt</em> rammer jer?',
    'body'    => 'Så er det nok en sjette slags. Ring eller skriv, og lad os høre, hvad I står med — vi siger også til, hvis en simulation ikke er svaret.',
])

@endsection
