@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Konto</h1>
</div>

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if ($errors->any())
  <div class="alert alert-error">
    @foreach ($errors->all() as $err)<div>{{ $err }}</div>@endforeach
  </div>
@endif

<form method="POST" action="{{ url('/simulation/konto') }}" style="max-width: 560px;">
  @csrf

  <div class="card">
    <h3 style="margin-bottom: 4px;">Login</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 14px;">Email-adressen du logger ind med.</p>
    <div style="display: grid; gap: 10px;">
      <label>Email<input type="email" name="email" value="{{ old('email', $user->email) }}" style="width: 100%;" required></label>
      <label>Telefon<input type="text" name="phone" value="{{ old('phone', $user->phone) }}" style="width: 100%;" placeholder="F.eks. +45 12 34 56 78"></label>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-bottom: 4px;">Skift kodeord</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 14px;">Lad felterne være tomme hvis du ikke vil skifte kodeord.</p>
    <div style="display: grid; gap: 10px;">
      <label>Nuværende kodeord<input type="password" name="current_password" autocomplete="current-password" style="width: 100%;"></label>
      <label>Nyt kodeord<input type="password" name="password" autocomplete="new-password" style="width: 100%;"></label>
      <label>Gentag nyt kodeord<input type="password" name="password_confirmation" autocomplete="new-password" style="width: 100%;"></label>
    </div>
  </div>

  <div style="text-align: right; margin: 20px 0;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem</button>
  </div>
</form>

<style>
form label { font-weight: 600; font-size: 13px; }
form input { font-family: inherit; font-size: 14px; padding: 8px 12px; border: 1px solid #dadde1; border-radius: 6px; background: #fff; }
form input:focus { outline: none; border-color: #1877f2; }
</style>
@endsection
