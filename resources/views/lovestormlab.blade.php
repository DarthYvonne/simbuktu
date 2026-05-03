<!DOCTYPE html>
<html lang="da">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LovestormLAB — bliv elsket</title>
    <meta name="description" content="Sådan har du aldrig fået et kommunikationskursus før. Forstå positiv viralitet ved at prøve at skabe den.">
    <link rel="icon" href="/img/favicon.png" type="image/png">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Lovestormlab">
    <meta property="og:url" content="https://shitstormlab.dk/lovestormlab">
    <meta property="og:title" content="LovestormLAB — bliv elsket">
    <meta property="og:description" content="Sådan har du aldrig fået et kommunikationskursus før. Forstå positiv viralitet ved at prøve at skabe den.">
    <meta property="og:image" content="https://shitstormlab.dk/img/preview.png">
    <meta property="og:locale" content="da_DK">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="LovestormLAB — bliv elsket">
    <meta name="twitter:description" content="Sådan har du aldrig fået et kommunikationskursus før. Forstå positiv viralitet ved at prøve at skabe den.">
    <meta name="twitter:image" content="https://shitstormlab.dk/img/preview.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            background: #0a0a0a;
            color: #f5f5f5;
            font-family: "Inter", -apple-system, "Segoe UI", sans-serif;
            line-height: 1.5;
            font-weight: 400;
        }

        .wrap {
            max-width: 1080px;
            margin: 0 auto;
            padding: 48px 24px 80px;
        }

        .login-float {
            float: right;
            margin: 0 0 0 12px;
            transition: transform .15s ease, opacity .15s ease;
            opacity: .85;
        }

        .login-float:hover {
            opacity: 1;
            transform: scale(1.05);
        }

        .login-float img {
            display: block;
        }

        /* Masthead */
        .masthead {
            text-align: center;
            padding: 18px 0;
            margin-bottom: 36px;
        }

        .masthead .kicker {
            text-transform: uppercase;
            letter-spacing: .3em;
            font-size: 11px;
            color: #bdbdbd;
            font-weight: 700;
        }

        .masthead h1 {
            font-family: "Archivo Black", "Inter", sans-serif;
            font-weight: 900;
            font-size: clamp(2.4rem, 7vw, 5.5rem);
            letter-spacing: -0.02em;
            line-height: 1;
            margin: 8px 0;
        }

        .masthead .subtitle {
            font-family: "Archivo Black", "Inter", sans-serif;
            font-size: clamp(1rem, 1.8vw, 1.35rem);
            letter-spacing: -0.01em;
            line-height: 1.2;
            margin: 10px auto 2px;
            max-width: 760px;
            color: #f5f5f5;
        }

        .masthead .dateline {
            font-size: 12px;
            color: #bdbdbd;
            display: flex;
            justify-content: space-between;
            max-width: 640px;
            margin: 10px auto 0;
            padding: 0 8px;
            text-transform: uppercase;
            letter-spacing: .15em;
        }

        .love {
            color: #ff3b8a;
        }

        /* Top image */
        .hero-img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0 0 40px;
            filter: grayscale(.2) contrast(1.05);
            border: 1px solid #1a1a1a;
        }

        /* Lede — plain text on black page */
        .lede {
            margin: 28px 0 40px;
        }

        .lede h2 {
            font-family: "Archivo Black", "Inter", sans-serif;
            font-weight: 900;
            font-size: clamp(1.6rem, 3.2vw, 2.4rem);
            line-height: 1.05;
            margin-bottom: 14px;
            letter-spacing: -0.01em;
            color: #f5f5f5;
        }

        .cols2-dark {
            color: #d6d6d6;
        }

        .cols2-dark p {
            font-size: 17px;
        }

        .cols2-dark.cols2 {
            column-rule: 1px solid rgba(255, 255, 255, .15);
        }

        /* Sensational clip variant — louder shadow, uppercase headline */
        .clip-sensational {
            box-shadow: 0 4px 0 #000, 0 0 0 2px #000, 8px 10px 0 rgba(255, 59, 138, .9);
            transform: rotate(-0.4deg);
        }

        .clip-sensational h2.upper {
            text-transform: uppercase;
            letter-spacing: 0;
        }

        /* Newspaper cards */
        .clip {
            background: #FFD6E7;
            color: #111;
            padding: 28px 30px;
            margin-bottom: 28px;
            box-shadow: 0 2px 0 #000, 0 0 0 1px #000;
            position: relative;
        }

        .clip .label {
            font-size: 10px;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: #8a1a4a;
            font-weight: 600;
        }

        .clip h2 {
            font-family: "Archivo Black", "Inter", sans-serif;
            font-weight: 900;
            font-size: clamp(1.6rem, 3.2vw, 2.4rem);
            line-height: 1.05;
            margin: 6px 0 12px;
            letter-spacing: -0.01em;
        }

        .clip h2 .love {
            color: #b0004f;
        }

        .clip p {
            font-size: 17px;
        }

        .clip p+p {
            margin-top: 10px;
        }

        .clip p {
            font-weight: 400;
        }

        /* Two-column newsprint */
        .cols2 {
            columns: 2;
            column-gap: 32px;
            column-rule: 1px solid rgba(0, 0, 0, .15);
        }

        .cols2 p:first-of-type::first-letter {
            float: left;
        }

        @media (max-width: 640px) {
            .cols2 {
                columns: 1;
            }
        }

        /* Alternating rows */
        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            align-items: center;
            margin: 56px 0;
        }

        .row.reverse .row-text {
            order: 2;
        }

        .row.reverse .row-media {
            order: 1;
        }

        .row-media {
            background: #111;
            border: 1px solid #222;
            padding: 10px;
        }

        .row-media img {
            width: 100%;
            display: block;
        }

        .row-media .cap {
            font-size: 11px;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: .2em;
            margin-top: 8px;
            text-align: center;
        }

        .row-text h3 {
            font-family: "Archivo Black", "Inter", sans-serif;
            font-weight: 900;
            font-size: clamp(1.4rem, 2.6vw, 2rem);
            line-height: 1.1;
            margin-bottom: 10px;
        }

        .row-text .tag {
            display: inline-block;
            font-size: 10px;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: #ff3b8a;
            border: 1px solid #ff3b8a;
            padding: 3px 8px;
            margin-bottom: 10px;
        }

        .row-text p {
            color: #d6d6d6;
            font-size: 17px;
        }

        .row-text p+p {
            margin-top: 10px;
        }

        @media (max-width: 760px) {
            .row {
                grid-template-columns: 1fr;
            }

            .row.reverse .row-text {
                order: 1;
            }

            .row.reverse .row-media {
                order: 2;
            }
        }

        /* Section divider */
        .rule {
            text-align: center;
            color: #666;
            letter-spacing: .6em;
            margin: 40px 0;
            font-size: 12px;
        }

        /* Subscription / booking form */
        .sub {
            background: #f5f1e8;
            color: #111;
            padding: 36px 34px;
            margin-top: 60px;
            box-shadow: 0 2px 0 #000, 0 0 0 1px #000;
        }

        .sub-head {
            text-align: center;
            border-bottom: 3px double #111;
            padding-bottom: 16px;
            margin-bottom: 22px;
        }

        .sub-head .kicker {
            font-size: 11px;
            letter-spacing: .35em;
            text-transform: uppercase;
            color: #8a1a4a;
        }

        .sub-head h2 {
            font-family: "Archivo Black", "Inter", sans-serif;
            font-weight: 900;
            font-size: clamp(1.8rem, 3.6vw, 2.6rem);
            margin-top: 4px;
            letter-spacing: -0.01em;
        }

        .sub-head p {
            margin-top: 8px;
            font-size: 15px;
            color: #333;
            font-style: italic;
        }

        .sub form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 18px;
        }

        .sub .full {
            grid-column: 1 / -1;
        }

        .sub label {
            display: block;
            font-size: 10px;
            letter-spacing: .25em;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .sub input,
        .sub select,
        .sub textarea {
            width: 100%;
            font-family: inherit;
            font-size: 16px;
            padding: 10px 12px;
            background: transparent;
            border: none;
            border-bottom: 1px solid #111;
            border-radius: 0;
            color: #111;
        }

        .sub textarea {
            min-height: 110px;
            border: 1px solid #111;
            padding: 10px 12px;
            resize: vertical;
        }

        .sub input:focus,
        .sub select:focus,
        .sub textarea:focus {
            outline: none;
            background: #fff;
        }

        .sub .choices {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            font-size: 15px;
        }

        .sub .choices label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            letter-spacing: 0;
            text-transform: none;
            font-size: 15px;
            color: #111;
            font-weight: 400;
        }

        .sub button {
            grid-column: 1 / -1;
            margin-top: 10px;
            background: #111;
            color: #f5f1e8;
            border: none;
            padding: 16px;
            font-family: "Archivo Black", "Inter", sans-serif;
            font-weight: 900;
            font-size: 20px;
            letter-spacing: .15em;
            text-transform: uppercase;
            cursor: pointer;
        }

        .sub button:hover {
            background: #b0004f;
        }

        .sub .finep {
            grid-column: 1 / -1;
            font-size: 12px;
            color: #555;
            font-style: italic;
            text-align: center;
            margin-top: 4px;
        }

        @media (max-width: 600px) {
            .sub form {
                grid-template-columns: 1fr;
            }
        }

        /* About the instructor */
        .bio {
            display: grid;
            grid-template-columns: 220px 1fr;
            gap: 28px;
            align-items: start;
            background: #f5f1e8;
            color: #111;
            padding: 28px 30px;
            margin-top: 60px;
            box-shadow: 0 2px 0 #000, 0 0 0 1px #000;
            position: relative;
        }

        .bio::before {
            content: "";
            position: absolute;
            inset: 6px;
            border: 1px solid rgba(0, 0, 0, .12);
            pointer-events: none;
        }

        .bio img {
            width: 100%;
            height: auto;
            display: block;
            filter: grayscale(1) contrast(1.1);
            border: 1px solid #111;
        }

        .bio .label {
            font-size: 10px;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: #8a1a4a;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .bio h2 {
            font-family: "Archivo Black", "Inter", sans-serif;
            font-weight: 900;
            font-size: clamp(1.4rem, 2.6vw, 2rem);
            line-height: 1.05;
            margin-bottom: 12px;
            letter-spacing: -0.01em;
        }

        .bio p {
            font-size: 16px;
        }

        .bio p+p {
            margin-top: 10px;
        }

        @media (max-width: 640px) {
            .bio {
                grid-template-columns: 1fr;
            }

            .bio img {
                max-width: 220px;
            }
        }

        .platform {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin: 32px 0 8px;
            color: #bdbdbd;
            font-size: 12px;
            letter-spacing: .25em;
            text-transform: uppercase;
        }

        .platform img {
            height: 34px;
        }

        /* Testimonials */
        .quotes {
            margin: 60px 0 20px;
        }

        .quotes-head {
            text-align: center;
            margin-bottom: 28px;
        }

        .quotes-head .kicker {
            font-size: 11px;
            letter-spacing: .35em;
            text-transform: uppercase;
            color: #bdbdbd;
            font-weight: 700;
        }

        .quotes-head h2 {
            font-family: "Archivo Black", "Inter", sans-serif;
            font-weight: 900;
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            letter-spacing: -0.01em;
            margin-top: 6px;
        }

        .quotes-head .sub {
            font-size: 13px;
            color: #bdbdbd;
            margin-top: 6px;
            font-style: italic;
        }

        .quotes-grid {
            column-count: 2;
            column-gap: 24px;
        }

        @media (max-width: 640px) {
            .quotes-grid {
                column-count: 1;
            }
        }

        .quote {
            break-inside: avoid;
            background: #f5f1e8;
            color: #111;
            padding: 22px 24px 20px;
            margin-bottom: 22px;
            box-shadow: 0 2px 0 #000, 0 0 0 1px #000;
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .quote::before {
            content: "“";
            font-family: "Archivo Black", "Inter", sans-serif;
            font-size: 64px;
            line-height: .7;
            color: #b0004f;
            position: absolute;
            top: 14px;
            left: 16px;
            opacity: .22;
        }

        .quote p {
            font-size: 16px;
            line-height: 1.5;
            position: relative;
        }

        .quote .who {
            display: block;
            margin-top: 12px;
            font-size: 10px;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: #8a1a4a;
            font-weight: 600;
        }

        footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 60px;
            letter-spacing: .2em;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <a href="/slophub/login" class="login-float" aria-label="Log ind">
            <img src="/img/login.png" alt="Log ind">
        </a>

        <header class="masthead">
            <div class="kicker">Så godt, at folk bliver forelskede</div>
            <h1>Lovestorm<span class="love">lab</span></h1>
            <div class="subtitle">Et kursus <span class="love">til kommunikationsfolk</span> om (positiv) viralitet</div>
        </header>

        <img src="/img/topimage-love.jpg" alt="" class="hero-img">

        <section class="lede">
            <h2>Sådan har I aldrig prøvet et <span style="color:#ff3b8a">kommunikationskursus</span> før</h2>
            <div class="cols2 cols2-dark">
                <p>Lovestorm Lab er et kommunikationskursus hvor I kommer helt ind under huden på mennesker som deler, kommenterer og forelsker sig i viralt content. I skal nemlig lave det indhold selv.</p>
                <p>På Lovestormlab bliver deltagerne indført i den positive viralitets psykologi og lærer teori og psykologiske mekanismer bag det indhold, vi elsker. Derefter skal de selv prøve at skabe en lovestorm.</p>
            </div>
        </section>

        <hr style="border:none; border-top:1px solid #f5f5f5; margin:48px 0 28px;">

        <img src="/img/slophub-black-logo.png" alt="SlopHub">

        <article class="clip clip-sensational" style="margin-top:25px;">
            <h2 class="upper">10.000 digitale brugere venter på at forelske sig i jeres opslag</h2>
            <p><b style="font-weight:900">SlopHUB</b> ligner endnu en social medieplatform. Men den er befolket med 10.000 psykologisk detaljerede, <i>simulerede brugere</i> som kan reagere på jeres opslag.
                Brugerne har personligheder, historie, kommunikationsstil og mærkesager som kan røre dem. Og de kan forelske sig i indhold — hvis det altså rammer dem rigtigt.</p>
        </article>

        <section class="row">
            <div class="row-text">
                <h3>Når dit opslag er uploadet - starter simulationen</h3>
                <p>Efter at have lært om psykologien bag positiv viralitet, skal deltagerne selv prøve kræfter med at skabe en lovestorm. De planlægger indhold og finder billeder. Og logger så ind på SlopHUB, hvor de uploader deres indhold - og håber på det bedste.</p>
                <p>Her er et opslag om fællesskab, en personlig triumf, en selfie med et stort smil — de simulerede brugere reagerer præcis som ude i virkeligheden. Hvad kunne have fået folk endnu mere forelskede her, tror du?</p>
            </div>
            <div class="row-media">
                <img src="/img/slophub-feed.png" alt="SlopHub feed">
            </div>
        </section>

        <section class="row reverse">
            <div class="row-text">
                <h3>10.000 profiler — hver med en agenda og personlighed</h3>
                <p>Maja på 26 er feministisk og deler indhold, der hylder stærke kvinder og sårbarhed. Hun skriver i en akademisk stil og ofte lange og inderlige kommentarer. Tina på 41 holder af motorcykelmiljø og stemmer SF.
                    Alle personer er levende, har venner de kan dele indhold med og kan kommentere på jeres opslag (og I kan blande jer i kommentarfelterne).</p>
            </div>
            <div class="row-media">
                <img src="/img/slophub-profiler.png" alt="SlopHub personagalleri">
            </div>
        </section>

        <section class="row">
            <div class="row-text">
                <h3>Profilsider for alle - du kan tjekke hvem der forelsker sig</h3>
                <p>Hver persona har en baggrund, en indre længsel og et sæt ting de elsker. Samir taler om at være en "rigtig mand", men drømmer om fællesskab og anerkendelse. Forelsker sig i: ægthed, sårbarhed, historier om fædre og sønner.</p>
                <p>Det er her deltagerne ser, at en lovestorm ikke handler om hvad man skriver — men om hvem man rører ved.</p>
            </div>
            <div class="row-media">
                <img src="/img/slophub-persona.png" alt="SlopHub persona-detalje">
            </div>
        </section>

        <section class="row reverse">
            <div class="row-text">
                <h3>Se grafer og netværk for spredning</h3>
                <p>Profilerne reagerer som mennesker gør - og når de deler og forelsker sig i indhold, bliver det vist til deres netværk.
                    Efter øvelsen kan I se præcis hvordan opslaget bevægede sig. Hvem delte først. Hvem var spredere og hvor tog det fart.</p>
            </div>
            <div class="row-media">
                <img src="/img/slophub-analyse.png" alt="SlopHub spredningsgraf">
            </div>
        </section>

        <article class="clip" style="background-color:#FFD6E7">
            <h2>EN SJOV OG LÆRERIG DAG MED KOMMUNIKATIONSAFDELINGEN</h2>
            <p>LovestormLAB køres typisk som en samlet dag for hele kommunikationsafdelingen. Om formiddagen lærer vi om psykologien bag det indhold, vi elsker og deler. Om eftermiddagen prøver I selv at skabe positiv viralitet.
                Det er rørende, sjovt og klart det mest lærerige I kan lave sammen i år.</p>
        </article>

        <section class="bio">
            <img src="/img/anders2025.jpg" alt="Anders Colding-Jørgensen">
            <div>
                <div class="label">Underviseren</div>
                <h2>Undervejs får I viden og feedback af Anders Colding‑Jørgensen</h2>
                <p><b>Anders er cand.psych. og adfærdspsykolog — og en af de første herhjemme, der undersøgte psykologien bag sociale medier. I 2009 lavede han
                        et af de tidlige virale fænomener i DK: en Facebook‑gruppe mod en opdigtet nedrivning af Storkespringvandet, der på en uge samlede over 27.000 medlemmer og endte i Washington Post og New York Times.</b></p>
                <p>Lovestorm LAB er tvillingen til Shitstorm LAB — samme platform, samme psykologi, modsat fortegn. Hvor Shitstormlab handler om at forstå vrede og forargelse, handler Lovestormlab om at forstå begejstring, identifikation og den deling, der kommer af kærlighed.</p>
                <p>Til dagligt er Anders forfatter på Gyldendal, vært på den topratede podcast <em>Vaneinstituttet</em>, og holder foredrag og underviser om adfærd og vaner.
                    Anders har været ekstern lektor i digital konceptudvikling på IT Universitetet og har bl.a. udviklet SlopHUB med 10.000 simulerede brugere, som kursisterne får adgang til.</p>
            </div>
        </section>

        <section class="quotes">
            <div class="quotes-head">
                <h2>Hvad deltagerne sagde efter sidste gang</h2>
                Anders' oplæg om sociale medier fik 92/100 på Social Media Week 2015 og blev det næstbest ratede indlæg på hele ugen.
            </div>
            <div class="quotes-grid">
                <div class="quote">
                    <p>Det bedste SMW‑arrangement jeg indtil nu har deltaget i.</p>
                    <span class="who">— Deltager, SMW 2015</span>
                </div>
                <div class="quote">
                    <p>Blown away. Sjovt, lærerigt, tankevækkende med Anders' psykologiske perspektiv.</p>
                    <span class="who">— Deltager, SMW 2015</span>
                </div>
                <div class="quote">
                    <p>Sjoveste og mest inspirerende indlæg jeg har hørt længe! Mange case‑eksempler, kombineret med teori, serveret med humor så det ikke blev tørt. Gennemført.</p>
                    <span class="who">— Deltager, SMW 2015</span>
                </div>
                <div class="quote">
                    <p>Et nyt og interessant take på viralitet — og gør en bedre rustet, hvis man sidder med ansvar for en FB‑side. Og så er Anders skide sjov og en god storyteller, hvilket gør det nemmere at huske de forskellige ting.</p>
                    <span class="who">— Deltager, SMW 2015</span>
                </div>
                <div class="quote">
                    <p>Mega spændende. Bum. Det holdt, og gav enormt mange idéer i forhold til personal branding.</p>
                    <span class="who">— Deltager, SMW 2015</span>
                </div>
                <div class="quote">
                    <p>Virkelig spændende at høre om de mekanismer et enkelt gennemtænkt opslag kan sætte i gang.</p>
                    <span class="who">— Deltager, SMW 2015</span>
                </div>
                <div class="quote">
                    <p>Interessant oplæg med konkrete eksempler, der gør dig klogere på dit eget arbejde med sociale medier.</p>
                    <span class="who">— Deltager, SMW 2015</span>
                </div>
                <div class="quote">
                    <p>Meget medrivende, sjovt, lærerigt og interessant.</p>
                    <span class="who">— Deltager, SMW 2015</span>
                </div>
            </div>
        </section>

        <section class="sub">
            <div class="sub-head">
                <div class="kicker">Tegn abonnement</div>
                <h2>Book en heldag — eller stil et spørgsmål</h2>
                <p>Udfyld kuponen - og bliv elsket!</p>
            </div>
            <form method="POST" action="/booking">
                @csrf
                <input type="hidden" name="course" value="lovestormlab">
                <div>
                    <label for="name">Navn</label>
                    <input id="name" name="name" type="text" required>
                </div>
                <div>
                    <label for="org">Organisation</label>
                    <input id="org" name="org" type="text">
                </div>
                <div>
                    <label for="email">E‑mail</label>
                    <input id="email" name="email" type="email" required>
                </div>
                <div>
                    <label for="phone">Telefon</label>
                    <input id="phone" name="phone" type="tel">
                </div>
                <div class="full">
                    <label>Henvendelsen gælder</label>
                    <div class="choices">
                        <label><input type="radio" name="type" value="heldag" checked> Booking — heldag med kommunikationsafdelingen</label>
                        <label><input type="radio" name="type" value="kort"> Kortere oplæg / keynote</label>
                        <label><input type="radio" name="type" value="spm"> Jeg har bare et spørgsmål</label>
                    </div>
                </div>
                <div class="full">
                    <label for="msg">Besked</label>
                    <textarea id="msg" name="msg" placeholder="Størrelse på hold, ønsket dato, særlige cases I gerne vil arbejde med…"></textarea>
                </div>
                <button type="submit">Send kuponen</button>
                <div class="finep">Vi bruger kun dine oplysninger til at spamme dig med multilevel marketing og reklamer fra Temu. </div>
            </form>
        </section>

        <footer>Lovestormlab · København · bliv elsket</footer>
    </div>
</body>

</html>
