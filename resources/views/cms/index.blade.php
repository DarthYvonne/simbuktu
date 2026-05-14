@extends('cms.layout')

@section('title', 'Sider')

@section('styles')
<style>
    .pages { display: flex; flex-direction: column; gap: 10px; }

    .page-group {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 12px;
        overflow: hidden;
        transition: border-color 0.12s, box-shadow 0.12s;
    }
    .page-group:hover { border-color: #cdd5e0; }

    .page-row {
        display: flex; align-items: center; gap: 14px;
        padding: 16px 18px;
        text-decoration: none; color: inherit;
        cursor: pointer;
    }
    .page-row .title-block { flex: 1; min-width: 0; }
    .page-row .title {
        font-weight: 600; font-size: 15px; color: var(--ink);
        letter-spacing: -0.01em;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .page-row .url {
        display: block; color: var(--ink-mute); font-size: 12px;
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        margin-top: 2px;
    }
    .page-row .status-dot {
        width: 8px; height: 8px; border-radius: 999px;
        flex-shrink: 0;
    }
    .page-row .status-dot.on  { background: var(--success); }
    .page-row .status-dot.off { background: var(--danger); opacity: 0.6; }
    .page-row .actions {
        display: flex; gap: 4px; align-items: center;
    }
    .page-row .actions form { display: inline-flex; }

    /* Inverted box for selected top-level page */
    .page-group.selected {
        border-color: transparent;
        background: linear-gradient(135deg, #1a2733 0%, #243447 100%);
        box-shadow: 0 10px 30px -12px rgba(15,23,42,0.35), 0 2px 6px rgba(15,23,42,0.08);
    }
    .page-group.selected > .page-row .title { color: #ffffff; }
    .page-group.selected > .page-row .url   { color: rgba(255,255,255,0.55); }
    .page-group.selected > .page-row .iconbtn { color: rgba(255,255,255,0.65); }
    .page-group.selected > .page-row .iconbtn:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .page-group.selected > .page-row .btn--secondary { background: rgba(255,255,255,0.13); color: #fff; }
    .page-group.selected > .page-row .btn--secondary:hover { background: rgba(255,255,255,0.22); }
    .page-group.selected > .page-row .status-dot.on  { background: #4ade80; box-shadow: 0 0 0 3px rgba(74,222,128,0.18); }
    .page-group.selected > .page-row .status-dot.off { background: #f87171; opacity: 1; }

    /* Subpages */
    .subpages {
        position: relative;
        background: var(--bg);
        padding: 6px 0 6px;
        border-top: 1px solid var(--line);
    }
    .page-group.selected .subpages {
        background: rgba(255,255,255,0.04);
        border-top-color: rgba(255,255,255,0.08);
    }
    .subpage-row {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 18px 10px 46px;
        text-decoration: none; color: inherit;
        position: relative;
        font-size: 13px;
        transition: background 0.1s;
    }
    .subpage-row::before {
        content: ''; position: absolute;
        left: 26px; top: 50%;
        width: 12px; height: 1px; background: var(--ink-mute); opacity: 0.4;
    }
    .subpage-row::after {
        content: ''; position: absolute;
        left: 26px; top: 0; bottom: 50%;
        width: 1px; background: var(--ink-mute); opacity: 0.4;
    }
    .subpage-row:last-of-type::after { display: none; }
    .subpage-row:hover { background: var(--line-soft); }
    .subpage-row .title { flex: 1; min-width: 0; font-weight: 500; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .subpage-row .url { color: var(--ink-mute); font-size: 11px; font-family: 'JetBrains Mono', ui-monospace, monospace; flex-shrink: 0; }
    .subpage-row .actions { display: flex; gap: 2px; flex-shrink: 0; }
    .subpage-row .iconbtn { width: 26px; height: 26px; }
    .subpage-row .iconbtn svg { width: 13px; height: 13px; }

    .page-group.selected .subpage-row { color: #d1d8e0; }
    .page-group.selected .subpage-row::before,
    .page-group.selected .subpage-row::after { background: rgba(255,255,255,0.25); opacity: 1; }
    .page-group.selected .subpage-row:hover { background: rgba(255,255,255,0.06); }
    .page-group.selected .subpage-row .title { color: #ffffff; }
    .page-group.selected .subpage-row .url { color: rgba(255,255,255,0.45); }
    .page-group.selected .subpage-row .iconbtn { color: rgba(255,255,255,0.55); }
    .page-group.selected .subpage-row .iconbtn:hover { background: rgba(255,255,255,0.12); color: #fff; }

    .add-subpage {
        display: flex; align-items: center; gap: 8px;
        padding: 11px 18px 13px 46px;
        font-size: 13px; font-weight: 500;
        color: var(--accent); text-decoration: none;
        position: relative;
    }
    .add-subpage::before {
        content: ''; position: absolute;
        left: 26px; top: 0; bottom: calc(50% - 1px);
        width: 1px; background: var(--ink-mute); opacity: 0.4;
    }
    .add-subpage::after {
        content: ''; position: absolute;
        left: 26px; top: calc(50% - 1px);
        width: 12px; height: 1px; background: var(--ink-mute); opacity: 0.4;
    }
    .page-group.selected .add-subpage { color: #7cc4ee; }
    .page-group.selected .add-subpage:hover { color: #ffffff; }
    .page-group.selected .add-subpage::before,
    .page-group.selected .add-subpage::after { background: rgba(255,255,255,0.25); opacity: 1; }
    .add-subpage .plus {
        display: inline-flex; align-items: center; justify-content: center;
        width: 18px; height: 18px; border-radius: 5px;
        background: var(--accent-soft); color: var(--accent);
        font-weight: 700; font-size: 14px; line-height: 1;
    }
    .page-group.selected .add-subpage .plus { background: rgba(255,255,255,0.16); color: #fff; }
    .add-subpage:hover { color: var(--accent-h); }

    .empty {
        text-align: center; padding: 50px 20px;
        color: var(--ink-mute);
    }
    .empty svg { width: 48px; height: 48px; opacity: 0.3; margin-bottom: 12px; }
    .empty p { font-size: 14px; }

    /* Click area: only the title block should set the selected state.
       The action buttons stop propagation so they don't change selection. */
    .page-row .actions { z-index: 2; }
</style>
@endsection

@section('content')
    <div class="card">
        <div class="card-head">
            <div>
                <h1>Sider</h1>
                <span class="sub">{{ $topLevel->count() }} {{ $topLevel->count() === 1 ? 'topside' : 'topsider' }} · klik en side for at se og redigere dens undersider</span>
            </div>
            <a href="/cms/create" class="btn">+ Ny side</a>
        </div>

        @if($topLevel->isEmpty())
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <p>Ingen sider endnu. Klik <strong>+ Ny side</strong> for at oprette den første.</p>
            </div>
        @else
        <div class="pages">
            @foreach($topLevel as $page)
                @php $isSelected = $page->id === $selectedId; @endphp
                <div class="page-group {{ $isSelected ? 'selected' : '' }}">
                    <a href="{{ url('/cms?selected='.$page->id) }}" class="page-row">
                        <span class="status-dot {{ $page->is_visible ? 'on' : 'off' }}" title="{{ $page->is_visible ? 'Synlig' : 'Skjult' }}"></span>
                        <div class="title-block">
                            <div class="title">{{ $page->title }}</div>
                            <span class="url">{{ $page->url() }}</span>
                        </div>
                        <span class="actions" onclick="event.stopPropagation();">
                            @if($page->is_visible)
                                <a href="{{ $page->url() }}" class="iconbtn" title="Vis side" target="_blank" rel="noopener" onclick="event.stopPropagation();">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                            @endif
                            <a href="/cms/{{ $page->id }}/edit" class="btn btn--secondary btn--sm" onclick="event.stopPropagation();">Rediger</a>
                            <form method="POST" action="/cms/{{ $page->id }}" onsubmit="event.stopPropagation(); return confirm('Slet siden{{ $page->children->isNotEmpty() ? ' og dens '.$page->children->count().' underside(r)' : '' }}?');" onclick="event.stopPropagation();">
                                @csrf @method('DELETE')
                                <button class="iconbtn" title="Slet" type="submit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14H7L5 6m5 0V4a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </span>
                    </a>

                    @if($page->children->isNotEmpty() || $isSelected)
                        <div class="subpages">
                            @foreach($page->children as $child)
                                <a href="/cms/{{ $child->id }}/edit" class="subpage-row">
                                    <span class="status-dot {{ $child->is_visible ? 'on' : 'off' }}" style="width:6px;height:6px;" title="{{ $child->is_visible ? 'Synlig' : 'Skjult' }}"></span>
                                    <span class="title">{{ $child->title }}</span>
                                    <span class="url">{{ $child->url() }}</span>
                                    <span class="actions" onclick="event.stopPropagation();">
                                        @if($child->is_visible)
                                            <a href="{{ $child->url() }}" class="iconbtn" title="Vis side" target="_blank" rel="noopener">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                        @endif
                                        <form method="POST" action="/cms/{{ $child->id }}" onsubmit="event.preventDefault(); event.stopPropagation(); if(confirm('Slet undersiden?')) this.submit();">
                                            @csrf @method('DELETE')
                                            <button class="iconbtn" title="Slet" type="submit">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14H7L5 6m5 0V4a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    </span>
                                </a>
                            @endforeach
                            @if($isSelected)
                                <a href="/cms/create?parent={{ $page->id }}" class="add-subpage">
                                    <span class="plus">+</span> Tilføj underside til “{{ $page->title }}”
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @endif
    </div>
@endsection
