{{-- Repeated closing call to action. --}}
<section class="closer">
    <div class="shell">
        <p class="tag tag--plain" data-reveal>{{ $kicker ?? 'Næste skridt' }}</p>
        <h2 class="mt-1" data-reveal style="--delay: 60ms;">
            {!! $heading ?? 'Lad os bygge <em class="mark">jeres</em> scenarie' !!}
        </h2>
        <p class="lede" data-reveal style="--delay: 120ms;">
            {{ $body ?? 'Fortæl hvad I skal træne, så vender vi tilbage med et bud på, hvordan det ser ud som simulation. Første samtale koster ingenting.' }}
        </p>
        <div class="btn-row" data-reveal style="--delay: 180ms;">
            <a class="btn btn--primary" href="/kontakt">
                Book en samtale
                <span class="btn__arrow" aria-hidden="true">→</span>
            </a>
            <a class="btn btn--ghost" href="tel:+4561678913">Ring +45 61 67 89 13</a>
        </div>
    </div>
</section>
