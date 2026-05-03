@extends('layouts.public')

@section('title', $page->title.' | Simbuktu')

@section('styles')
    .page-content > *:first-child { margin-top: 0; }
@endsection

@section('content')
    <main class="container">
        <div class="page-content">
            {!! $page->content !!}
        </div>
    </main>
@endsection
