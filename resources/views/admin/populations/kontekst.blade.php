@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>
    <a href="{{ url('/simulation/admin/populations') }}" style="color: #1877f2;"><i class="fa-solid fa-arrow-left"></i></a>
    {{ $population->name }}
  </h1>
</div>

@include('admin.populations._tabs', ['population' => $population])

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if ($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif

<form method="POST" action="{{ url('/simulation/admin/populations/'.$population->id.'/kontekst') }}" class="card" style="max-width: 760px;">
  @csrf @method('PATCH')

  <div style="margin-bottom: 12px;">
    <h3 style="margin: 0 0 4px; font-size: 15px;">Kontekst</h3>
    <div style="color:#65676b; font-size:13px; line-height:1.5;">
      Beskriv samtiden personerne skal reagere ud fra — fx hvilke begivenheder fylder lige nu, hvilken sag debatteres. Indsættes som <code style="background:#f0f2f5; padding:1px 5px; border-radius:3px;">@{{current_context}}</code> i alle persona-prompts.
    </div>
  </div>

  <textarea name="manual_context" rows="14" placeholder="F.eks.:&#10;Det er efteråret 2026. Den nye klima-lov er netop vedtaget. Sociale medier debatterer..."
    style="width:100%; padding:12px 14px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit; resize:vertical; min-height:280px;">{{ $population->manual_context }}</textarea>

  <div style="display:flex; justify-content:flex-end; margin-top:12px;">
    <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem</button>
  </div>
</form>

@endsection
