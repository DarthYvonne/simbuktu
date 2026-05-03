@extends('layouts.public')

@section('title', $page->title.' | Simbuktu')

@section('content')
    <main class="container">
        <h1>{{ $page->title }}</h1>
        <div class="page-content">
            {!! $page->content !!}
        </div>
    </main>
@endsection
