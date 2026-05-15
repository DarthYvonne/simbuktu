@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>System @if ($course)<span style="font-weight: 400; color: #65676b; font-size: 14px;">· {{ $course->name }}</span>@endif</h1>
</div>

@include('admin._opsaetning_tabs')

<div class="card" style="text-align: center; padding: 30px;">
  Det aktive kursus har ingen population tilknyttet. Vælg et kursus med population for at teste AI-svar.
</div>

@endsection
