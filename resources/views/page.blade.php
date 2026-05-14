@extends('layouts.public')

@section('title', $page->title.' | Simbuktu')

@section('styles')
    .page-submenu {
        display: flex; flex-wrap: wrap; gap: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 32px;
    }
    .page-submenu a {
        text-decoration: none;
        font-size: 14px; font-weight: 400;
        letter-spacing: 0.4px;
        color: #7a8694;
        padding: 4px 10px; border-radius: 5px;
        transition: background-color 0.15s, color 0.15s;
    }
    .page-submenu a:hover { color: #3498db; }
    .page-submenu a.active { background: #3498db; color: #fff; }
    .page-content > *:first-child { margin-top: 0; }
@endsection

@section('content')
    <main class="container">
        @php
            // Submenu = the siblings of this page (so the user can move around the
            // section). If this page is a top-level page with children, show its
            // own children. If it's a subpage, show its siblings (its parent's
            // visible children). Otherwise no submenu.
            $submenuPages = collect();
            if ($page->parent_id && $page->parent) {
                $submenuPages = $page->parent->children->where('is_visible', true);
            } elseif ($page->children->isNotEmpty()) {
                $submenuPages = $page->children->where('is_visible', true);
            }
        @endphp

        @if($submenuPages->count() > 0)
            <nav class="page-submenu" aria-label="Undersider">
                @foreach($submenuPages as $sibling)
                    <a href="{{ $sibling->url() }}" class="{{ $sibling->id === $page->id ? 'active' : '' }}">{{ $sibling->title }}</a>
                @endforeach
            </nav>
        @endif

        <div class="page-content">
            {!! $page->content !!}
        </div>
    </main>
@endsection
