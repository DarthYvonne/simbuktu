@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1><a href="{{ url('/simulation/profiler') }}" style="color: #1877f2;"><i class="fa-solid fa-arrow-left"></i> Profiler</a></h1>
</div>

<div style="background:#fff;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,0.1);padding:32px 28px;max-width:520px;margin-top:24px;">
  <div style="width:56px;height:56px;border-radius:50%;background:#e4e6eb;color:#65676b;display:inline-flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:14px;">
    <i class="fa-regular fa-user"></i>
  </div>
  <h2 style="font-size:20px;font-weight:700;color:#1c1e21;margin-bottom:8px;">Profil ikke fundet</h2>
  <p style="color:#65676b;font-size:15px;line-height:1.5;margin-bottom:20px;">Denne profil er desværre blevet slettet.</p>
  <a href="#" onclick="event.preventDefault(); history.back();" style="display:inline-block;background:#1877f2;color:#fff;font-weight:600;font-size:14px;padding:9px 18px;border-radius:6px;text-decoration:none;">Tilbage</a>
</div>

@endsection
