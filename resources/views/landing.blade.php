@extends('layouts.public')

@section('styles')
    .hero {
        display: flex;
        min-height: 70vh;
        background-color: #ffffff;
        border-bottom: 1px solid #e0e0e0;
    }
    .hero-left {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 60px 5% 60px 10%;
        border-right: 1px solid #e0e0e0;
    }
    .hero-left h1 {
        font-size: 4rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 20px;
        color: #2c3e50;
    }
    .hero-left p {
        font-size: 1.25rem;
        color: #555;
        margin-bottom: 40px;
        max-width: 500px;
        line-height: 1.6;
    }
    .btn {
        display: inline-block;
        background-color: #3498db;
        color: #ffffff;
        padding: 18px 40px;
        text-decoration: none;
        font-size: 16px;
        font-weight: 700;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: background-color 0.2s;
        width: fit-content;
    }
    .btn:hover { background-color: #2980b9; }
    .hero-right {
        flex: 1;
        background-color: #f8f9fa;
        overflow: hidden;
        padding: 0;
    }
    .hero-right img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .home-content {
        max-width: 1100px;
        margin: 0 auto;
        padding: 60px 5%;
    }
    .home-content h1, .home-content h2, .home-content h3 { color: #2c3e50; margin-bottom: 16px; }
    .home-content h1 { font-size: 2.25rem; }
    .home-content h2 { font-size: 1.75rem; margin-top: 32px; }
    .home-content h3 { font-size: 1.25rem; margin-top: 24px; }
    .home-content p  { font-size: 1.1rem; color: #555; line-height: 1.7; margin-bottom: 16px; }
    .home-content ul, .home-content ol { margin: 0 0 16px 24px; }
    .home-content img { max-width: 100%; height: auto; }

    @@media (max-width: 768px) {
        .hero { flex-direction: column; min-height: auto; }
        .hero-left { padding: 40px 5%; border-right: 0; border-bottom: 1px solid #e0e0e0; }
        .hero-left h1 { font-size: 2.25rem; }
        .hero-left p { font-size: 1rem; margin-bottom: 24px; }
        .btn { padding: 14px 28px; font-size: 14px; }
        .hero-right { min-height: 280px; }
        .home-content { padding: 40px 5%; }
        .home-content h1 { font-size: 1.75rem; }
        .home-content h2 { font-size: 1.4rem; }
    }
@endsection

@section('content')
    <section class="hero">
        <div class="hero-left">
            <h1>Data-driven<br>Scenarios</h1>
            <p>PROVEN TO SIMULATE 4X<br>FASTER THAN TRADITIONAL MODELS</p>
            <a href="#" class="btn">Udforsk nu</a>
        </div>

        <div class="hero-right">
            <img src="{{ asset($heroImage ?? 'img/hero-feed.png') }}" alt="Simbuktu simulerede feeds">
        </div>
    </section>

    @if(!empty($homeContent))
        <section class="home-content">
            {!! $homeContent !!}
        </section>
    @endif
@endsection
