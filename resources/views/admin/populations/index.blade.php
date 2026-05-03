@extends('layouts.app')
@section('content')

<div class="view-header">
  <div>
    <h1 style="margin-bottom: 3px;">Populationer</h1>
    @if ($course)
      <div style="font-size: 13px; color: #65676b;">
        {{ $course->name }}
        &nbsp;·&nbsp;
        Aktiv population:
        <strong style="color: {{ $course->population_id ? '#1c1e21' : '#b91c1c' }};">
          {{ $course->population?->name ?? 'Ingen valgt' }}
        </strong>
        @if ($activityCount > 0)
          &nbsp;<span style="color: #92400e; font-size: 12px;"><i class="fa-solid fa-lock"></i> {{ $activityCount }} reaktioner</span>
        @endif
      </div>
    @endif
  </div>
  <button class="btn btn-primary" onclick="document.getElementById('createModal').style.display='flex'">
    <i class="fa-solid fa-plus"></i> Ny population
  </button>
</div>

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->has('population'))
  <div class="alert alert-error">{{ $errors->first('population') }}</div>
@endif

@if ($populations->isEmpty())
  <div style="text-align: center; padding: 60px 20px; color: #65676b;">
    <i class="fa-solid fa-dna" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: .3;"></i>
    <p>Ingen populationer endnu. Opret en for at komme i gang.</p>
  </div>
@else
  <div style="display: grid; gap: 8px; max-width: 860px;">
    @foreach ($populations as $pop)
      @php $isCurrent = $course && $course->population_id == $pop->id; @endphp
      <div style="display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid {{ $isCurrent ? '#1877f2' : '#dadde1' }}; border-radius: 8px; padding: 12px 16px; {{ $isCurrent ? 'background: #f5f9ff;' : '' }}">

        <a href="{{ url('/simulation/admin/populations/'.$pop->id.'/personas') }}"
          style="flex: 1; min-width: 0; text-decoration: none; color: inherit; display: flex; align-items: baseline; gap: 10px;">
          <span style="font-weight: 700; font-size: 15px; color: #1c1e21; white-space: nowrap;">{{ $pop->name }}</span>
          @if ($pop->description)
            <span style="color: #65676b; font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0;">{{ $pop->description }}</span>
          @endif
          <span style="margin-left: auto; color: #65676b; font-size: 12px; white-space: nowrap; flex-shrink: 0;">{{ $pop->personas_count }} personas</span>
        </a>

        <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
          @if ($isCurrent)
            <span style="background: #dcfce7; color: #166534; font-size: 12px; font-weight: 700; padding: 3px 12px; border-radius: 20px; white-space: nowrap;">
              <i class="fa-solid fa-check"></i> Valgt
            </span>
          @elseif ($course)
            @if ($activityCount > 0)
              <form method="POST" action="{{ url('/simulation/admin/courses/'.$course->id.'/population') }}">
                @csrf @method('PATCH')
                <input type="hidden" name="population_id" value="{{ $pop->id }}">
                <input type="hidden" name="force" value="1">
                <button type="button" class="btn btn-secondary" style="font-size: 12px;"
                  onclick="if(confirm('Skift til \'{{ addslashes($pop->name) }}\'?\n\nDette sletter {{ $activityCount }} {{ $activityCount === 1 ? 'reaktion' : 'reaktioner' }} fra kurset.')) this.closest(\'form\').submit()">
                  Vælg
                </button>
              </form>
            @else
              <form method="POST" action="{{ url('/simulation/admin/courses/'.$course->id.'/population') }}">
                @csrf @method('PATCH')
                <input type="hidden" name="population_id" value="{{ $pop->id }}">
                <button type="submit" class="btn btn-secondary" style="font-size: 12px;">Vælg</button>
              </form>
            @endif
          @endif
          <a href="{{ url('/simulation/admin/populations/'.$pop->id) }}" style="color: #bcc0c4; padding: 4px 6px; font-size: 14px;" title="Indstillinger">
            <i class="fa-solid fa-gear"></i>
          </a>
        </div>
      </div>
    @endforeach
  </div>
@endif

{{-- Create modal --}}
<div id="createModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:12px; padding:28px; width:100%; max-width:460px; box-shadow:0 8px 40px rgba(0,0,0,.2);">
    <h2 style="margin:0 0 18px; font-size:18px;">Ny population</h2>
    <form method="POST" action="{{ url('/simulation/admin/populations') }}">
      @csrf
      <div style="margin-bottom:14px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Navn *</label>
        <input type="text" name="name" required autofocus placeholder="fx Gymnasieelever, Voksne danskere…"
          style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
      </div>
      <div style="margin-bottom:20px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Beskrivelse</label>
        <input type="text" name="description" placeholder="Valgfri kort beskrivelse"
          style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
      </div>
      <div style="display:flex; gap:8px; justify-content:flex-end;">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('createModal').style.display='none'">Annuller</button>
        <button type="submit" class="btn btn-primary">Opret</button>
      </div>
    </form>
  </div>
</div>
@endsection
