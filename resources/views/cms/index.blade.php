@extends('cms.layout')

@section('title', 'Sider')

@section('styles')
<style>
    .page-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .page-table th {
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: var(--ink-mute);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 12px;
        border-bottom: 1px solid var(--line);
    }
    .page-table td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--line-soft);
        vertical-align: middle;
    }
    .page-table tr:hover td { background: var(--line-soft); }
    .page-table td.title { font-weight: 600; color: var(--ink); }
    .page-table td.title .sub { color: var(--ink-soft); font-weight: 400; }
    .page-table td.title .sub::before { content: '└ '; color: var(--ink-mute); margin-right: 2px; }
    .page-table td.url { color: var(--ink-mute); font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 12px; }
    .page-table td.visible { width: 70px; }
    .page-table td.actions { text-align: right; white-space: nowrap; width: 1%; }
    .page-table td.actions a, .page-table td.actions button {
        font-size: 12px; padding: 5px 10px;
        background: transparent; border: 1px solid var(--line);
        border-radius: 5px; color: var(--ink-soft);
        text-decoration: none; cursor: pointer;
        font-family: inherit;
        margin-left: 4px;
        transition: background 0.12s, color 0.12s, border-color 0.12s;
    }
    .page-table td.actions a:hover { background: var(--accent-soft); color: var(--accent); border-color: var(--accent); }
    .page-table td.actions .del { color: var(--danger); }
    .page-table td.actions .del:hover { background: var(--danger-soft); border-color: var(--danger); }
    .vis { font-size: 11px; padding: 2px 8px; border-radius: 999px; }
    .vis.on  { background: var(--success-soft); color: #166534; }
    .vis.off { background: var(--danger-soft);  color: var(--danger); }

    .empty { text-align: center; padding: 36px 20px; color: var(--ink-mute); }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-head">
        <div>
            <h1>Sider</h1>
            <span class="sub">{{ $topLevel->sum(fn ($p) => 1 + $p->children->count()) }} sider i alt</span>
        </div>
        <a href="/cms/create" class="btn">+ Ny side</a>
    </div>

    @if($topLevel->isEmpty())
        <div class="empty">Ingen sider endnu. Klik <strong>+ Ny side</strong> for at oprette den første.</div>
    @else
        <table class="page-table">
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>URL</th>
                    <th>Synlig</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($topLevel as $page)
                    <tr>
                        <td class="title">{{ $page->title }}</td>
                        <td class="url">{{ $page->url() }}</td>
                        <td class="visible"><span class="vis {{ $page->is_visible ? 'on' : 'off' }}">{{ $page->is_visible ? 'Ja' : 'Nej' }}</span></td>
                        <td class="actions">
                            <a href="/cms/{{ $page->id }}/edit">Rediger</a>
                            <a href="/cms/create?parent={{ $page->id }}">+ Underside</a>
                            <form method="POST" action="/cms/{{ $page->id }}" style="display:inline;" onsubmit="return confirm('Slet {{ addslashes($page->title) }}{{ $page->children->isNotEmpty() ? ' og dens '.$page->children->count().' underside(r)' : '' }}?');">
                                @csrf @method('DELETE')
                                <button class="del" type="submit">Slet</button>
                            </form>
                        </td>
                    </tr>
                    @foreach($page->children as $child)
                        <tr>
                            <td class="title"><span class="sub">{{ $child->title }}</span></td>
                            <td class="url">{{ $child->url() }}</td>
                            <td class="visible"><span class="vis {{ $child->is_visible ? 'on' : 'off' }}">{{ $child->is_visible ? 'Ja' : 'Nej' }}</span></td>
                            <td class="actions">
                                <a href="/cms/{{ $child->id }}/edit">Rediger</a>
                                <form method="POST" action="/cms/{{ $child->id }}" style="display:inline;" onsubmit="return confirm('Slet {{ addslashes($child->title) }}?');">
                                    @csrf @method('DELETE')
                                    <button class="del" type="submit">Slet</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
