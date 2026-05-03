@extends('cms.layout')

@section('title', $page->exists ? 'Rediger '.$page->title : 'Ny side')

@section('content')
    <div class="card">
        <h2 style="font-size:18px;margin-bottom:16px;">{{ $page->exists ? 'Rediger side' : 'Ny side' }}</h2>

        <form method="POST" action="{{ $page->exists ? '/cms/'.$page->id : '/cms' }}">
            @csrf
            @if($page->exists) @method('PATCH') @endif

            <div class="form-row">
                <label>Titel</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required>
            </div>

            <div class="form-row form-row--inline">
                <div>
                    <label>Slug (URL)</label>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="f.eks. simulationer (tom = forside)">
                </div>
                <div>
                    <label>Sortering</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}">
                </div>
            </div>

            <div class="form-row">
                <label class="checkbox">
                    <input type="hidden" name="is_visible" value="0">
                    <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', $page->is_visible) ? 'checked' : '' }}>
                    Synlig i menu
                </label>
            </div>

            <div class="form-row">
                <label>Indhold (HTML)</label>
                <textarea name="content">{{ old('content', $page->content) }}</textarea>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button class="btn">Gem</button>
                <a href="/cms" class="btn btn--secondary">Annuller</a>
            </div>
        </form>
    </div>
@endsection
