@extends('site.layout')

@section('title', 'Kontakt — Simbuktu')
@section('description', 'Book en samtale om jeres simulation. Ring til Simbuktu på +45 61 67 89 13, eller skriv et par ord om, hvad I gerne vil træne.')

@section('body')

<section class="pagehead">
    <div class="shell pagehead__grid">
        <div>
            <p class="tag rise">Kontakt</p>
            <h1 class="rise" style="--delay: 90ms;">Fortæl os, hvad I skal <em class="mark" style="font-style: italic;">kunne</em></h1>
        </div>
        <p class="lede rise" style="--delay: 180ms;">
            I behøver ikke have en færdig idé. Skriv et par linjer om, hvad der går galt i dag,
            eller hvad I gerne vil kunne håndtere — så vender vi tilbage med et bud på, hvordan
            det ser ud som simulation.
        </p>
    </div>
</section>

<section class="section">
    <div class="shell">
        <div class="split split--wide-left split--top">
            <div>
                @if (session('sent'))
                    <div class="notice" role="status">
                        <strong>Tak — beskeden er sendt.</strong><br>
                        Vi vender tilbage hurtigst muligt. Haster det, så ring på
                        <a class="link" href="tel:+4561678913">+45 61 67 89 13</a>.
                    </div>
                @endif

                @if ($errors->any())
                    <div class="errors" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="/kontakt/send" novalidate>
                    @csrf
                    <input type="hidden" name="ts" value="{{ time() }}">

                    {{-- Honeypot: usynligt for mennesker, uimodståeligt for bots. --}}
                    <div class="honey" aria-hidden="true">
                        <label for="firma_www">Firmaets hjemmeside</label>
                        <input type="text" id="firma_www" name="firma_www" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="split" style="gap: 0 1.25rem;">
                        <div class="field">
                            <label for="navn">Navn</label>
                            <input type="text" id="navn" name="navn" value="{{ old('navn') }}"
                                   autocomplete="name" required>
                        </div>
                        <div class="field">
                            <label for="org">Organisation</label>
                            <input type="text" id="org" name="org" value="{{ old('org') }}"
                                   autocomplete="organization" placeholder="Valgfrit">
                        </div>
                    </div>

                    <div class="split" style="gap: 0 1.25rem;">
                        <div class="field">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   autocomplete="email" required>
                        </div>
                        <div class="field">
                            <label for="telefon">Telefon</label>
                            <input type="tel" id="telefon" name="telefon" value="{{ old('telefon') }}"
                                   autocomplete="tel" placeholder="Valgfrit">
                        </div>
                    </div>

                    <div class="field">
                        <label for="emne">Hvad drejer det sig om?</label>
                        <select id="emne" name="emne" required>
                            <option value="demo" @selected(old('emne') === 'demo')>Jeg vil gerne se en demo</option>
                            <option value="workshop" @selected(old('emne') === 'workshop')>Vi overvejer en workshop</option>
                            <option value="platform" @selected(old('emne') === 'platform')>Spørgsmål om platform eller udviklerkonto</option>
                            <option value="andet" @selected(old('emne') === 'andet')>Noget helt andet</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="besked">Besked</label>
                        <textarea id="besked" name="besked" required
                                  placeholder="Hvad vil I gerne kunne håndtere bedre?">{{ old('besked') }}</textarea>
                    </div>

                    <div class="btn-row mt-2">
                        <button type="submit" class="btn btn--primary">
                            Send besked
                            <span class="btn__arrow" aria-hidden="true">→</span>
                        </button>
                        <span class="muted" style="font-size: 0.85rem;">Vi svarer normalt inden for en hverdag.</span>
                    </div>
                </form>
            </div>

            <div>
                <div class="panel">
                    <p class="tag tag--plain">Hellere ringe?</p>
                    <p class="mt-2" style="font-family: var(--serif); font-size: clamp(1.6rem, 3vw, 2.1rem); line-height: 1.15;">
                        <a href="tel:+4561678913" style="text-decoration: none;">+45 61 67 89 13</a>
                    </p>
                    <p class="muted mt-2" style="font-size: 0.93rem;">
                        Det tager som regel fem minutter at finde ud af, om der er noget her for jer.
                    </p>

                    <dl class="contact-direct">
                        <dt>Platform</dt>
                        <dd><a class="link" href="https://situationroom.dk" rel="noopener">situationroom.dk</a></dd>

                        <dt>Læs først</dt>
                        <dd><a class="link" href="/saadan-virker-det">Sådan virker en simulation</a></dd>

                        <dt>Se løsninger</dt>
                        <dd><a class="link" href="/loesninger">De seks motorer</a></dd>
                    </dl>
                </div>

                <svg class="diagram mt-3" viewBox="0 0 380 120" role="img"
                     aria-label="Diagram: samtale fører til scenarie fører til simulation.">
                    <path class="trace trace--hot" style="--i: 0;" d="M30 60 H 190"/>
                    <path class="trace" style="--i: 1;" d="M190 60 H 350"/>
                    <circle class="node node--hot" style="--i: 0;" cx="30" cy="60" r="6"/>
                    <circle class="node node--hot" style="--i: 1;" cx="190" cy="60" r="6"/>
                    <circle class="node node--open" style="--i: 2;" cx="350" cy="60" r="6"/>
                    <text class="glyph glyph--hot" style="--i: 0;" x="18" y="40">Samtale</text>
                    <text class="glyph" style="--i: 1;" x="190" y="40" text-anchor="middle">Scenarie</text>
                    <text class="glyph" style="--i: 2;" x="350" y="40" text-anchor="end">Simulation</text>
                </svg>
            </div>
        </div>
    </div>
</section>

@endsection
