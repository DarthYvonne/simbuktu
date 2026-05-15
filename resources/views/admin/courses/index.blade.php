@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Simulationer</h1>
  <button type="button" class="btn btn-primary" onclick="openCreate()"><i class="fa-solid fa-plus"></i> Opret simulation</button>
</div>

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

@if ($courses->isEmpty())
  <div class="card" style="text-align: center; padding: 50px; color: #65676b;">
    <p style="margin-bottom: 14px;">Ingen simulationer endnu.</p>
    <button type="button" class="btn btn-primary" onclick="openCreate()"><i class="fa-solid fa-plus"></i> Opret din første simulation</button>
  </div>
@else
  @php $activeId = auth()->user()->currentCourse()?->id; @endphp
  @foreach ($courses as $course)
    @php $isActive = $course->id === $activeId; @endphp
    <div class="course-row {{ $isActive ? 'active' : '' }}">
      <form method="POST" action="{{ url('/simulation/admin/courses/'.$course->id.'/switch') }}" class="course-row-switch">
        @csrf
        <button type="submit" class="course-row-btn">
          <div class="course-name">
            {{ $course->name }}
            @if ($isActive)<span class="active-pill">Aktivt</span>@endif
          </div>
          @if ($course->description)<div class="course-desc">{{ $course->description }}</div>@endif
          <div class="course-meta">
            {{ $course->memberships_count }} deltagere · {{ $course->posts_count }} opslag · oprettet {{ $course->created_at->diffForHumans() }}
          </div>
          <div class="course-brand">
            <span class="brand-item">
              <span class="brand-label">Navn:</span>
              <strong>{{ $course->platform_name ?: 'Simbuktu' }}</strong>
              @if (!$course->platform_name)<span class="brand-default">(standard)</span>@endif
            </span>
            <span class="brand-item">
              <span class="brand-label">Logo:</span>
              @if ($course->logo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->logo_path) }}" alt="" class="brand-logo">
              @else
                <span class="brand-missing"><i class="fa-regular fa-image"></i> mangler</span>
              @endif
            </span>
            <span class="brand-item">
              <span class="brand-label">Favicon:</span>
              @if ($course->favicon_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->favicon_path) }}" alt="" class="brand-favicon">
              @else
                <span class="brand-missing"><i class="fa-regular fa-star"></i> mangler</span>
              @endif
            </span>
          </div>
        </button>
      </form>
      <a href="{{ url('/simulation/admin/courses/'.$course->id) }}" class="details-link" title="Indstillinger"><i class="fa-solid fa-chevron-right"></i></a>
    </div>
  @endforeach

<style>
.course-row { display: flex; align-items: stretch; gap: 0; background: #fff; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 14px; overflow: hidden; transition: transform 0.15s, box-shadow 0.15s; }
.course-row:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.course-row.active { background: #1877f2; color: #fff; }
.course-row.active .course-name, .course-row.active .course-desc { color: #fff; }
.course-row.active .course-meta { color: rgba(255,255,255,0.8); }
.course-row.active .details-link { color: #fff; border-left-color: rgba(255,255,255,0.2); }
.course-row.active .details-link:hover { background: rgba(255,255,255,0.15); }
.course-row-switch { flex: 1; min-width: 0; display: flex; }
.course-row-btn { flex: 1; min-width: 0; background: transparent; border: none; padding: 18px 22px; text-align: left; cursor: pointer; color: inherit; font: inherit; }
.course-row .course-name { font-weight: 800; font-size: 19px; letter-spacing: -0.2px; color: #1c1e21; display: flex; gap: 8px; align-items: center; }
.course-row .course-desc { color: #1c1e21; font-size: 15px; margin-top: 4px; }
.course-row .course-meta { color: #65676b; font-size: 13px; margin-top: 6px; }
.course-brand { display: flex; flex-wrap: wrap; gap: 10px 18px; align-items: center; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e4e6eb; font-size: 12px; }
.course-row.active .course-brand { border-top-color: rgba(255,255,255,0.3); }
.brand-item { display: inline-flex; align-items: center; gap: 6px; color: #65676b; }
.course-row.active .brand-item { color: rgba(255,255,255,0.9); }
.brand-label { text-transform: uppercase; letter-spacing: 0.3px; font-size: 10px; font-weight: 700; }
.brand-item strong { font-weight: 700; color: #1c1e21; font-size: 13px; }
.course-row.active .brand-item strong { color: #fff; }
.brand-default { color: #9ca3af; font-style: italic; font-size: 11px; }
.course-row.active .brand-default { color: rgba(255,255,255,0.7); }
.brand-logo { max-height: 22px; max-width: 90px; object-fit: contain; background: #fff; padding: 2px 4px; border-radius: 4px; }
.brand-favicon { width: 18px; height: 18px; object-fit: contain; background: #fff; padding: 1px; border-radius: 3px; }
.brand-missing { color: #b91c1c; font-size: 11px; font-style: italic; }
.course-row.active .brand-missing { color: #fecaca; }
.active-pill { background: #fff; color: #1877f2; font-size: 10px; padding: 2px 8px; border-radius: 10px; font-weight: 700; letter-spacing: 0.3px; }
.details-link { padding: 0 22px; color: #65676b; text-decoration: none; display: inline-flex; align-items: center; flex-shrink: 0; border-left: 1px solid #f0f2f5; font-size: 16px; }
.details-link:hover { background: #f0f2f5; color: #1877f2; }
</style>
@endif

<style>
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9998; display: none; align-items: center; justify-content: center; padding: 20px; }
.modal-backdrop.open { display: flex; }
.modal-box { background: #fff; border-radius: 12px; max-width: 440px; width: 100%; padding: 22px 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
.modal-box h3 { margin-bottom: 14px; font-size: 18px; }
.modal-box label { font-weight: 600; font-size: 13px; display: block; margin-bottom: 4px; margin-top: 10px; }
.modal-box input { width: 100%; padding: 9px 12px; border: 1px solid #dadde1; border-radius: 6px; font-family: inherit; font-size: 14px; }
.modal-box input:focus { outline: none; border-color: #1877f2; }
.modal-box .actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; }
</style>

<div class="modal-backdrop" id="createModal" onclick="if(event.target===this) closeCreate()">
  <form method="POST" action="{{ url('/simulation/admin/courses') }}" class="modal-box">
    @csrf
    <h3>Opret simulation</h3>
    <label>Simulationsnavn</label>
    <input type="text" name="name" placeholder="F.eks. Krisekom F26" required autofocus>
    <label>Beskrivelse (valgfri)</label>
    <input type="text" name="description" placeholder="Kort beskrivelse af simulationen">
    <div class="actions">
      <button type="button" class="btn btn-secondary" onclick="closeCreate()">Annuller</button>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Opret</button>
    </div>
  </form>
</div>

<script>
function openCreate() { document.getElementById('createModal').classList.add('open'); document.querySelector('#createModal input[name=name]').focus(); }
function closeCreate() { document.getElementById('createModal').classList.remove('open'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCreate(); });
</script>
@endsection
