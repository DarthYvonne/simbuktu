<!DOCTYPE html>
<html lang="da">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShitstormLAB — vær bange</title>
    <meta name="description" content="Sådan har du aldrig fået et kommunikationskursus før. Forstå shitstorme ved at prøve at skabe dem.">
    <link rel="icon" href="/img/favicon.png" type="image/png">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Shitstormlab">
    <meta property="og:url" content="https://shitstormlab.dk/">
    <meta property="og:title" content="ShitstormLAB — vær bange">
    <meta property="og:description" content="Sådan har du aldrig fået et kommunikationskursus før. Forstå shitstorme ved at prøve at skabe dem.">
    <meta property="og:image" content="https://shitstormlab.dk/img/preview.png">
    <meta property="og:locale" content="da_DK">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="ShitstormLAB — vær bange">
    <meta name="twitter:description" content="Sådan har du aldrig fået et kommunikationskursus før. Forstå shitstorme ved at prøve at skabe dem.">
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

        img {
            max-width: 100%;
            height: auto;
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

        .scary {
            color: #ff3b3b;
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
            box-shadow: 0 4px 0 #000, 0 0 0 2px #000, 8px 10px 0 rgba(255, 59, 59, .9);
            transform: rotate(-0.4deg);
        }

        .clip-sensational h2.upper {
            text-transform: uppercase;
            letter-spacing: 0;
        }

        /* Newspaper white cards */
        .clip {
            background: #FAFF10;
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
            color: #7a1a1a;
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

        .clip h2 .scary {
            color: #b00020;
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
            color: #ff3b3b;
            border: 1px solid #ff3b3b;
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
            color: #7a1a1a;
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
            background: #b00020;
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
            color: #7a1a1a;
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
            color: #b00020;
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
            color: #7a1a1a;
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
            <div class="kicker">Så godt, at det burde være forbudt</div>
            <h1>Shitstorm<span class="scary">lab</span></h1>
            <div class="subtitle">Et kursus <span class="scary">til kommunikationsfolk</span> om (negativ) viralitet </div>
        </header>

        <img src="/img/topimage.jpg" alt="" class="hero-img">

        <section class="lede">
            <h2>Sådan har I aldrig prøvet et <span style="color:#FAFF10">kommunikationskursus</span> før</h2>
            <div class="cols2 cols2-dark">
                <p>Shitstorm Lab er et kommunikationskursus hvor I kommer helt ind under huden på mennesker som poster og reagerer på negativt viralt content. I skal nemlig lave det indhold selv.</p>
                <p>På Shitstormlab bliver deltagerne indført i shitstormens psykologi og lærer teori og psykologiske mekanismer. Derefter skal de selv prøve at skabe en shitstorm.</p>
            </div>
        </section>

        <hr style="border:none; border-top:1px solid #f5f5f5; margin:48px 0 28px;">

        <img src="/img/slophub-black-logo.png" alt="SlopHub">

        <article class="clip clip-sensational" style="margin-top:25px;">
            <h2 class="upper">10.000 digitale brugere venter på jeres opslag</h2>
            <p><b style="font-weight:900">SlopHUB</b> ligner endnu en social medieplatform. Men den er befolket med 10.000 psykologisk detaljerede, <i>simulerede brugere</i> som kan reagere på jeres opslag.
                Brugerne har personligheder, historie, kommunikationsstil og mærkesager som kan trigge dem. Og de kan reagere på indhold — hvis det altså rører dem.</p>
        </article>

        <section class="row">
            <div class="row-text">
                <h3>Når dit opslag er uploadet - starter simulationen</h3>
                <p>Efter at have lært om shitstorm-psykologi, skal deltagerne selv prøve kræfter med at skabe en shitstorm. De planlægger indhold og finder billeder. Og logger så ind på SlopHUB, hvor de uploader deres indhold - og håber på det værste.</p>
                <p>Her er et opslag om ADHD, en personlig holdning, en selfie med en kvinde der kigger på os — de simulerede brugere reagerer præcis som ude i virkeligheden. Hvad kunne have gjort folk mere oprørte her, tror du?</p>
            </div>
            <div class="row-media">
                <img src="/img/slophub-feed.png" alt="SlopHub feed">
            </div>
        </section>

        <section class="row reverse">
            <div class="row-text">
                <h3>10.000 profiler — hver med en agenda og personlighed</h3>
                <p>Maja på 26 er feministisk og reagerer på toxisk maskulinitet. Hun skriver i en akademisk stil og ofte lange og belærende kommentarer. Tina på 41 holder af motorcykelmiljø og stemmer SF.
                    Alle personer er levende, har venner de kan dele indhold med og kan kommentere på jeres opslag (og I kan blande jer i kommentarfelterne).</p>

            </div>
            <div class="row-media">
                <img src="/img/slophub-profiler.png" alt="SlopHub personagalleri">
            </div>
        </section>

        <section class="row">
            <div class="row-text">
                <h3>Profilsider for alle - du kan tjekke hvem der reagerer</h3>
                <p>Hver persona har en baggrund, en indre modsigelse og et sæt triggere. Samir taler om at være en "rigtig mand", men bor alene i Køge og lever af pasta med ketchup. Trigger: woke, maskulinitet, metoo.</p>
                <p>Det er her deltagerne ser, at en shitstorm ikke handler om hvad man skriver — men om hvem man rammer.</p>
            </div>
            <div class="row-media">
                <img src="/img/slophub-persona.png" alt="SlopHub persona-detalje">
            </div>
        </section>

        <section class="row reverse">
            <div class="row-text">
                <h3>Se grafer og netværk for spredning</h3>
                <p>Profilerne reagerer som mennesker gør - og når de deler og reagerer på indhold, bliver det vist til deres netværk.
                    Efter øvelsen kan I se præcis hvordan opslaget bevægede sig. Hvem delte først. Hvem var spredere og hvor døde den.</p>

            </div>
            <div class="row-media">
                <img src="/img/slophub-analyse.png" alt="SlopHub spredningsgraf">
            </div>
        </section>

        <article class="clip" style="background-color:#FAFF10">

            <h2>EN SJOV OG LÆRERIG DAG MED KOMMUNIKATIONSAFDELINGEN</h2>
            <p>ShitstormLAB køres typisk som en samlet dag for hele kommunikationsafdelingen. Om formiddagen lærer vi om shitstormpsykologi. Om eftermiddagen prøver I selv at skabe negativ viralitet.
                Det er ubehageligt, sjovt og klart det mest lærerige I kan lave sammen i år.</p>
        </article>



        <section class="bio">
            <img src="/img/anders2025.jpg" alt="Anders Colding-Jørgensen">
            <div>
                <div class="label">Underviseren</div>
                <h2>Undervejs får I viden og feedback af Anders Colding‑Jørgensen</h2>
                <p><b>Anders er cand.psych. og adfærdspsykolog — og en af de første herhjemme, der undersøgte psykologien bag sociale medier. I 2009 lavede han
                        et af de tidlige virale fænomener i DK: en Facebook‑gruppe mod en opdigtet nedrivning af Storkespringvandet, der på en uge samlede over 27.000 medlemmer og endte i Washington Post og New York Times.</b></p>
                <p>Shitstorm LAB er ikke et nyt påfund: Kurset var topscorer i evalueringerne på Social Media Week Copenhagen i 2015, hvor deltagere selv skulle bygge shitstorm‑indhold og den mest "farlige" indsats blev præmieret.
                    Nu er formatet genopstået, denne gang med en fuldt bygget træningsplatform bagved.</p>
                <p>Til dagligt er Anders forfatter på Gyldendal, vært på den topratede podcast <em>Vaneinstituttet</em>, og holder foredrag og underviser om adfærd og vaner.
                    Anders har været ekstern lektor i digital konceptudvikling på IT Universitetet og har bl.a. udviklet SlopHUB med 10.000 simulerede brugere, som kursisterne får adgang til.</p>
            </div>
        </section>

        <section class="quotes">
            <div class="quotes-head">

                <h2>Hvad deltagerne sagde efter sidste gang</h2>
                Med en samlet rating på 92/100 blev kurset det næstbest ratede i hele Social media Week 2015.

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
                    <p>Jeg synes virkelig godt om din Shitstorm Lab. Det var et nyt og interessant take på viralitet — og gør en bedre rustet, hvis man sidder med ansvar for en FB‑side og pludselig skal arbejde med krisehåndtering. Og så er du skide sjov og en god storyteller, hvilket gør det nemmere at huske de forskellige ting.</p>
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
                <p>Udfyld kuponen - hvis du tør!</p>
            </div>
            <form method="POST" action="/booking">
                @csrf
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

        <footer>Shitstormlab · København · vær bange</footer>
    </div>
</body>

</html>