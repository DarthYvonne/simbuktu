@extends('cms.layout')

@section('title', 'Sider')

@section('content')
    <style>
        .pages { display: flex; flex-direction: column; gap: 6px; }
        .page-group { background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; overflow: hidden; }
        .page-row {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 16px; text-decoration: none; color: inherit;
            transition: background 0.12s;
        }
        .page-row:hover { background: #f4f6f8; }
        .page-row .title { font-weight: 600; font-size: 15px; flex: 1; min-width: 0; }
        .page-row .url { color: #888; font-size: 13px; font-family: ui-monospace, monospace; }
        .page-row .pill { font-size: 11px; padding: 2px 8px; border-radius: 999px; }
        .page-row .actions { display: flex; gap: 6px; align-items: center; }
        .page-row .actions form { display: inline; }
        .page-row .btn { padding: 6px 12px; font-size: 12px; }
        .page-row .iconbtn {
            background: none; border: 1px solid transparent; color: #888;
            width: 30px; height: 30px; border-radius: 4px; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 14px; text-decoration: none;
        }
        .page-row .iconbtn:hover { background: #ebeef2; color: #2c3e50; }

        /* Inverted box style for selected top-level page */
        .page-group.selected { border-color: #2c3e50; }
        .page-group.selected > .page-row { background: #2c3e50; color: #fff; }
        .page-group.selected > .page-row .url { color: #aab2bb; }
        .page-group.selected > .page-row .pill--on { background: #1f9d55; color: #fff; }
        .page-group.selected > .page-row .pill--off { background: #c0392b; color: #fff; }
        .page-group.selected > .page-row .iconbtn { color: #d0d8e0; }
        .page-group.selected > .page-row .iconbtn:hover { background: rgba(255,255,255,0.12); color: #fff; }
        .page-group.selected > .page-row .btn--secondary { background: rgba(255,255,255,0.18); color: #fff; }
        .page-group.selected > .page-row .btn--secondary:hover { background: rgba(255,255,255,0.28); }

        /* Subpages — indented, smaller, lighter */
        .subpages { border-top: 1px solid #eef0f3; background: #fafbfc; padding: 4px 0; }
        .subpage-row {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 16px 9px 44px; text-decoration: none; color: inherit;
            font-size: 13px;
        }
        .subpage-row:hover { background: #f0f2f5; }
        .subpage-row .title { flex: 1; min-width: 0; color: #34495e; font-weight: 500; }
        .subpage-row .url { color: #95a5a6; font-size: 12px; font-family: ui-monospace, monospace; }
        .subpage-row .actions { display: flex; gap: 4px; }
        .subpage-row .iconbtn { width: 26px; height: 26px; font-size: 12px; }
        .subpage-empty { padding: 10px 16px 12px 44px; font-size: 12px; color: #95a5a6; font-style: italic; }
        .add-subpage {
            display: flex; align-items: center; gap: 6px;
            padding: 9px 16px 11px 44px; font-size: 13px; color: #3498db;
            text-decoration: none; font-weight: 500;
        }
        .add-subpage:hover { color: #2980b9; }
        .add-subpage::before { content: '+'; font-weight: 700; font-size: 15px; }
    </style>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
            <h2 style="font-size:18px;">Sider</h2>
            <a href="/cms/create" class="btn">+ Ny side</a>
        </div>

        @if($topLevel->isEmpty())
            <p style="color:#888;font-size:14px;text-align:center;padding:20px 0;">
                Ingen sider endnu. Klik <em>+ Ny topside</em> for at oprette den første.
            </p>
        @else
        <div class="pages">
            @foreach($topLevel as $page)
                @php $isSelected = $page->id === $selectedId; @endphp
                <div class="page-group {{ $isSelected ? 'selected' : '' }}">
                    <a href="{{ url('/cms?selected='.$page->id) }}" class="page-row">
                        <span class="title">{{ $page->title }}</span>
                        <span class="url">{{ $page->url() }}</span>
                        <span class="pill {{ $page->is_visible ? 'pill--on' : 'pill--off' }}">
                            {{ $page->is_visible ? 'Synlig' : 'Skjult' }}
                        </span>
                        <span class="actions" onclick="event.stopPropagation();">
                            @if($page->is_visible)
                                <a href="{{ $page->url() }}" class="iconbtn" title="Vis" target="_blank">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                            @endif
                            <a href="/cms/{{ $page->id }}/edit" class="btn btn--secondary">Rediger</a>
                            <form method="POST" action="/cms/{{ $page->id }}" onsubmit="return confirm('Slet siden{{ $page->children->isNotEmpty() ? ' og dens '.$page->children->count().' underside(r)' : '' }}?');">
                                @csrf @method('DELETE')
                                <button class="iconbtn" title="Slet" type="submit">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14H7L5 6m5 0V4a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </span>
                    </a>

                    @if($page->children->isNotEmpty() || $isSelected)
                        <div class="subpages">
                            @foreach($page->children as $child)
                                <a href="/cms/{{ $child->id }}/edit" class="subpage-row">
                                    <span class="title">{{ $child->title }}</span>
                                    <span class="url">{{ $child->url() }}</span>
                                    @if(!$child->is_visible)
                                        <span class="pill pill--off">Skjult</span>
                                    @endif
                                    <span class="actions" onclick="event.stopPropagation();">
                                        @if($child->is_visible)
                                            <a href="{{ $child->url() }}" class="iconbtn" title="Vis" target="_blank">
                                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                        @endif
                                        <form method="POST" action="/cms/{{ $child->id }}" onsubmit="event.preventDefault(); if(confirm('Slet undersiden?')) this.submit();">
                                            @csrf @method('DELETE')
                                            <button class="iconbtn" title="Slet" type="submit">
                                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14H7L5 6m5 0V4a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    </span>
                                </a>
                            @endforeach
                            @if($isSelected)
                                <a href="/cms/create?parent={{ $page->id }}" class="add-subpage">Tilføj underside til “{{ $page->title }}”</a>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @endif
    </div>
@endsection
