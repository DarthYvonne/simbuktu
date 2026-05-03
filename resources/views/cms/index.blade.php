@extends('cms.layout')

@section('title', 'Sider')

@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
            <h2 style="font-size:18px;">Menu og sider</h2>
            <a href="/cms/create" class="btn">+ Ny side</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Titel</th>
                    <th>URL</th>
                    <th>Synlig</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($pages as $page)
                <tr>
                    <td>{{ $page->sort_order }}</td>
                    <td><strong>{{ $page->title }}</strong></td>
                    <td><code>{{ $page->url() }}</code></td>
                    <td>
                        <span class="pill {{ $page->is_visible ? 'pill--on' : 'pill--off' }}">
                            {{ $page->is_visible ? 'Ja' : 'Nej' }}
                        </span>
                    </td>
                    <td class="actions">
                        <a href="/cms/{{ $page->id }}/edit" class="btn btn--secondary">Rediger</a>
                        <form method="POST" action="/cms/{{ $page->id }}" onsubmit="return confirm('Slet siden?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn--danger">Slet</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
