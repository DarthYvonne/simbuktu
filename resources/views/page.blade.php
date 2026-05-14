@extends('layouts.public')

@section('title', $page->title.' | Simbuktu')

@section('styles')
    .page-hero {
        width: 100%; max-height: 420px;
        margin-bottom: 28px;
        border-radius: 12px;
        overflow: hidden;
        background: #f4f6f8;
    }
    .page-hero img {
        width: 100%; height: 100%; object-fit: cover;
        display: block; max-height: 420px;
    }
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
        .page-hero { max-height: 260px; border-radius: 8px; margin-bottom: 20px; }
        .page-hero img { max-height: 260px; }
    }
@endsection

@section('content')
    <main class="container">
        @if($page->hero_image)
            <div class="page-hero">
                <img src="{{ asset($page->hero_image) }}" alt="">
            </div>
        @endif

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
