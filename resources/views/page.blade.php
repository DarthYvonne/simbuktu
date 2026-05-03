@extends('layouts.public')

@section('title', $page->title.' | Simbuktu')

@section('content')
    <main class="container">
        <div class="page-content">
            {!! $page->content !!}
        </div>
    </main>
@endsection
