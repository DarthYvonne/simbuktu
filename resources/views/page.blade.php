@extends('layouts.public')

@section('title', $page->title.' | Simbuktu')

@section('styles')
    /* Hero matches the page content's width, hugs the header's bottom rule
       with no gap. Height follows the image's natural aspect ratio.
       Selector pinned to .container > .page-hero so we beat the layout's
       generic `.container > :first-child { margin-top: 0 }` typography rule. */
    .container > .page-hero {
        margin: -60px 0 18px;
    }
    .container .page-hero img {
        width: 100%; height: auto;
        display: block;
        margin: 0;
    }

    .breadcrumb {
        display: flex; flex-wrap: wrap; align-items: center; gap: 6px;
        font-size: 13px;
        color: #8a96a7;
        margin: 0 0 20px;
        padding-left: 14px; /* line up with the sidebar item text */
        letter-spacing: 0.3px;
    }
    .breadcrumb a {
        color: #8a96a7;
        text-decoration: none;
        transition: color 0.15s;
    }
    .breadcrumb a:hover { color: #3498db; }
    .breadcrumb .sep { color: #cdd5e0; user-select: none; }
    .breadcrumb .current { color: #1a2733; font-weight: 500; }
    .page-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 48px;
        align-items: start;
    }
    .page-sidebar {
        border-right: 1px solid #e0e0e0;
        padding-right: 24px;
        position: sticky;
        top: 24px;
    }
    .page-sidebar ul { list-style: none; display: flex; flex-direction: column; gap: 2px; }
    .page-sidebar a {
        display: block;
        text-decoration: none;
        font-size: 16px; font-weight: 400;
        color: #54637a;
        padding: 9px 14px;
        border-radius: 6px;
        transition: background-color 0.15s, color 0.15s;
    }
    .page-sidebar a:hover { color: #3498db; background: #f4f6f8; }
    .page-sidebar a.active { background: #3498db; color: #fff; font-weight: 500; }
    .page-content > *:first-child { margin-top: 0; }

    @media (max-width: 768px) {
        .page-layout { grid-template-columns: 1fr; gap: 24px; }
        .page-sidebar {
            border-right: 0;
            border-bottom: 1px solid #e0e0e0;
            padding-right: 0; padding-bottom: 16px;
            position: static;
        }
        .page-sidebar ul { flex-direction: row; flex-wrap: wrap; gap: 4px; }
        .container > .page-hero { margin: -60px 0 24px; }
    }
@endsection

@section('content')
    <main class="container">
        @if($page->hero_image)
            <div class="page-hero">
                <img src="{{ asset($page->hero_image) }}" alt="">
            </div>
        @endif

        <nav class="breadcrumb" aria-label="Sti">
            <a href="/">Forside</a>
            @if($page->parent_id && $page->parent)
                <span class="sep">›</span>
                <a href="{{ $page->parent->url() }}">{{ $page->parent->title }}</a>
            @endif
            <span class="sep">›</span>
            <span class="current">{{ $page->title }}</span>
        </nav>

        @php
            // Sidebar lists siblings (so the user can move around the section).
            // If this page is a top-level page with children, show its own children.
            // If it's a subpage, show its siblings (its parent's visible children).
            // Otherwise no sidebar — content uses full width.
            $sidebarPages = collect();
            if ($page->parent_id && $page->parent) {
                $sidebarPages = $page->parent->children->where('is_visible', true);
            } elseif ($page->children->isNotEmpty()) {
                $sidebarPages = $page->children->where('is_visible', true);
            }
        @endphp

        @if($sidebarPages->count() > 0)
            <div class="page-layout">
                <aside class="page-sidebar" aria-label="Undersider">
                    <ul>
                        @foreach($sidebarPages as $sibling)
                            <li><a href="{{ $sibling->url() }}" class="{{ $sibling->id === $page->id ? 'active' : '' }}">{{ $sibling->title }}</a></li>
                        @endforeach
                    </ul>
                </aside>
                <div class="page-content">
                    {!! $page->content !!}
                </div>
            </div>
        @else
            <div class="page-content">
                {!! $page->content !!}
            </div>
        @endif
    </main>
@endsection
