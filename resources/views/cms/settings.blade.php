@extends('cms.layout')

@section('title', 'Forside')

@section('content')
    <div class="card">
        <h2 style="font-size:18px;margin-bottom:16px;">Forside · hero-billede</h2>

        <p style="color:#666;font-size:14px;margin-bottom:20px;">
            Vælg det billede der vises i højre side af forsidens hero-sektion.
            Billedet skaleres så det dækker hele området (object-fit: cover).
        </p>

        @if($heroImage)
            <div style="margin-bottom:20px;">
                <label>Nuværende billede</label>
                <img src="{{ asset($heroImage) }}?v={{ time() }}" alt="Hero" style="max-width:100%;max-height:320px;border:1px solid #e0e0e0;border-radius:4px;">
                <div style="margin-top:8px;font-size:13px;color:#888;"><code>{{ $heroImage }}</code></div>
            </div>
        @else
            <div style="margin-bottom:20px;color:#888;font-size:14px;">
                Intet brugerdefineret billede sat — forsiden bruger standardbilledet
                <code>img/hero-feed.png</code>.
            </div>
        @endif

        <form method="POST" action="/cms/settings" enctype="multipart/form-data">
            @csrf

            <div class="form-row">
                <label>Upload nyt billede</label>
                <input type="file" name="hero_image" accept="image/png,image/jpeg,image/webp,image/gif">
                <div style="font-size:12px;color:#888;margin-top:4px;">JPG, PNG, WebP eller GIF · maks 5 MB</div>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <button class="btn" type="submit">Gem</button>
                <a href="/cms" class="btn btn--secondary">Tilbage</a>
                @if($heroImage)
                    <button class="btn btn--danger" type="submit" name="remove_hero" value="1"
                        onclick="return confirm('Fjern hero-billedet og gå tilbage til standard?');">
                        Fjern billede
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection
