@extends('layouts.app')
@section('content')

@php
  $base = '/simulation/admin/populations/'.$population->id;
@endphp

<style>
.pop-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
.pop-switch { padding: 8px 12px; border: 1px solid #dadde1; border-radius: 8px; font-size: 15px; font-weight: 700; font-family: inherit; background: #fff; cursor: pointer; min-width: 220px; max-width: 320px; color: #1c1e21; }
.pop-count { color: #65676b; font-size: 13px; font-weight: 500; }

.demo-strip { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
@media (max-width: 900px) { .demo-strip { grid-template-columns: 1fr; } }
.demo-block { background: #fff; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); padding: 12px 14px; }
.demo-title { font-size: 11px; font-weight: 700; color: #65676b; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 10px; }
.demo-row { display: grid; grid-template-columns: 56px 1fr 28px; gap: 8px; align-items: center; padding: 2px 0; font-size: 12px; }
.demo-row .lbl { color: #65676b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.demo-row .bar-wrap { background: #f0f2f5; border-radius: 3px; height: 8px; overflow: hidden; }
.demo-row .bar { height: 100%; background: var(--accent); border-radius: 3px; transition: width 0.3s; }
.demo-row .num { color: #1c1e21; font-weight: 600; text-align: right; font-variant-numeric: tabular-nums; }
.demo-row.r .lbl { font-size: 11.5px; }

.gen-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.split-btn { display: inline-flex; align-items: stretch; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.08); }
.split-btn .main, .split-btn .more { background: var(--accent); color: #fff; border: none; cursor: pointer; padding: 8px 14px; font-weight: 600; font-size: 13px; font-family: inherit; }
.split-btn .main:hover, .split-btn .more:hover { background: var(--accent-hover); }
.split-btn .main:disabled, .split-btn .more:disabled { opacity: 0.5; cursor: not-allowed; }
.split-btn .more { padding: 8px 10px; border-left: 1px solid rgba(255,255,255,0.25); }

.popover { position: absolute; background: #fff; border: 1px solid #dadde1; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); padding: 14px; z-index: 100; min-width: 260px; }
.popover label { display: block; font-size: 12px; font-weight: 600; color: #65676b; margin-bottom: 4px; }
.popover input[type=number], .popover select { width: 100%; padding: 8px 10px; border: 1px solid #dadde1; border-radius: 6px; font-size: 13px; font-family: inherit; }

.filter-bar { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; position: relative; }
.filter-bar input.q { flex: 1; min-width: 200px; padding: 8px 12px; border: 1px solid #dadde1; border-radius: 6px; font-size: 13px; font-family: inherit; }
.filter-toggle { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; background: #fff; border: 1px solid #dadde1; border-radius: 6px; font-size: 13px; font-weight: 600; color: #1c1e21; cursor: pointer; font-family: inherit; }
.filter-toggle .badge { background: var(--accent); color: #fff; border-radius: 10px; padding: 1px 7px; font-size: 11px; }
.chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: var(--accent-soft); color: var(--accent); border-radius: 12px; font-size: 12px; font-weight: 600; }
.chip a { color: inherit; opacity: 0.7; }
.chip a:hover { opacity: 1; }

.modal-bg { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; }
.modal-bg.open { display: flex; }
.modal-card { background:#fff; border-radius:14px; padding:24px 26px; width:100%; max-width:420px; box-shadow:0 12px 48px rgba(0,0,0,.22); }
.modal-card h3 { font-size: 16px; margin-bottom: 6px; }
.modal-card p { font-size: 14px; color: #65676b; line-height: 1.5; margin-bottom: 16px; }
.modal-actions { display: flex; gap: 8px; justify-content: flex-end; }
</style>

<div class="pop-bar">
  @if ($course)
    <form id="switchPopForm" method="POST" action="{{ url('/simulation/admin/courses/'.$course->id.'/population') }}">
      @csrf @method('PATCH')
      <input type="hidden" name="population_id" id="switchPopId" value="">
      <input type="hidden" name="force" id="switchPopForce" value="0">
    </form>
  @endif
  <select id="popSwitcher" class="pop-switch">
    @foreach ($allPopulations as $pop)
      <option value="{{ $pop->id }}"
        data-url="{{ url('/simulation/admin/populations/'.$pop->id.'/personas') }}"
        {{ $pop->id === $population->id ? 'selected' : '' }}>
        {{ $pop->name }} ({{ $pop->personas_count }})
      </option>
    @endforeach
    <option value="__new__">+ Opret ny population…</option>
  </select>
  <span class="pop-count">{{ $count === $total ? "$total personas" : "$count af $total" }}</span>
</div>

@include('admin.populations._tabs', ['population' => $population])

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<div class="card">
  <div class="gen-row">
    <div style="position: relative;">
      @if ($courseBlueprint)
        <div class="split-btn">
          <button type="button" class="main" id="genQuickBtn">
            <i class="fa-solid fa-plus"></i> Generér <span id="genCountLabel">10</span> personas
          </button>
          <button type="button" class="more" id="genOptsBtn" title="Indstillinger">
            <i class="fa-solid fa-caret-down"></i>
          </button>
        </div>
        <div id="genPopover" class="popover" style="display: none; top: 42px; left: 0;">
          <div style="margin-bottom: 10px;">
            <label>Antal</label>
            <input type="number" id="genCount" value="10" min="1" max="50">
          </div>
          <div style="margin-bottom: 12px;">
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; color: #1c1e21; cursor: pointer;">
              <input type="checkbox" id="genSkipImages" style="margin: 0;"> Spring billeder over
            </label>
          </div>
          <div style="display: flex; justify-content: flex-end; gap: 6px;">
            <button type="button" class="btn btn-secondary" style="font-size: 12px;" onclick="closeGenPopover()">Luk</button>
          </div>
        </div>
      @else
        <div style="display:flex; gap:10px; align-items:center; padding:12px 14px; background:#fef9e7; border:1px solid #fde68a; border-radius:8px; font-size:13px; color:#92400e; line-height:1.5;">
          <i class="fa-solid fa-circle-exclamation" style="font-size:16px;"></i>
          <div>
            Vælg først en personlighed under
            <a href="{{ url("$base") }}" style="color:#92400e; font-weight:700; text-decoration:underline;">Konfiguration</a>
            — så kan du generere personas her.
          </div>
        </div>
      @endif
    </div>
    <form method="POST" action="{{ url("$base/personas/generate") }}" id="genForm" style="display: none;">
      @csrf
      <input type="hidden" name="count" id="genFormCount" value="10">
      <input type="hidden" name="skip_images" id="genFormSkip" value="">
    </form>
    @if ($total > 0)
      <button type="button" class="btn btn-danger" style="font-size: 12px; margin-left: auto;" onclick="openModal('clearModal')">
        <i class="fa-solid fa-trash"></i> Slet alle
      </button>
    @endif
  </div>
  <div id="progress" style="display: none; margin-top: 12px; padding: 10px 12px; background: #e7f3ff; border-radius: 6px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
      <strong style="color: #1877f2; font-size: 13px;"><span class="spinner" style="border-color: #1877f2; border-top-color: transparent;"></span> Genererer…</strong>
      <span id="progLabel" style="font-size: 13px; color: #1877f2;">0 / 0</span>
    </div>
    <div style="height: 5px; background: #fff; border-radius: 3px; overflow: hidden;">
      <div id="progBar" style="height: 100%; background: #1877f2; width: 0%; transition: width 0.3s;"></div>
    </div>
    <div id="progErrors" style="font-size: 11px; color: #b91c1c; margin-top: 4px;"></div>
  </div>
</div>

@if ($total > 0)
<div class="card">
  <form method="GET" action="{{ url("$base/personas") }}" id="filterForm">
    <input type="text" name="q" value="{{ $q }}" placeholder="Søg navn, beskrivelse, etc." style="width: 100%; padding: 8px 12px; border: 1px solid #dadde1; border-radius: 6px; font-size: 14px;">
  </form>
  @if ($q)
    <div style="margin-top: 8px;">
      <a href="{{ url("$base/personas") }}" style="color: #1877f2; font-size: 13px;">Nulstil søgning</a>
    </div>
  @endif
</div>
@endif

@if ($total === 0)
  @if ($generating)
    <div class="card" style="text-align: center; padding: 50px 20px;">
      <span class="spinner" style="border-color: #1877f2; border-top-color: transparent; width: 30px; height: 30px; border-width: 3px;"></span>
      <div style="margin-top: 16px; font-size: 15px; color: #1877f2; font-weight: 600;">Genererer dine første personas…</div>
      <div id="emptyProgLabel" style="margin-top: 6px; font-size: 13px; color: #65676b;">{{ $genDone }} / {{ $genQueued }}</div>
    </div>
  @else
    <div class="card" style="text-align: center; padding: 40px; color: #65676b;">
      Ingen personas endnu. Generér dine første ovenfor.
    </div>
  @endif
@elseif ($personas->isEmpty())
  <div class="card" style="text-align: center; padding: 40px; color: #65676b;">
    Ingen personas matcher din søgning.
  </div>
@else
<div class="persona-grid">
  @foreach ($personas as $p)
    @include('partials._persona-card', [
      'href'     => url("$base/personas/".$p['id']),
      'thumbUrl' => url("$base/personas/".$p['id']."/thumb"),
    ])
  @endforeach
</div>
@endif

{{-- Switch population confirm modal --}}
<div id="switchPopModal" class="modal-bg">
  <div class="modal-card">
    <div style="display:flex; align-items:flex-start; gap:14px; margin-bottom:18px;">
      <div style="width:40px; height:40px; border-radius:50%; background:#fee2e2; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <i class="fa-solid fa-triangle-exclamation" style="color:#b91c1c; font-size:16px;"></i>
      </div>
      <div>
        <div style="font-weight:700; font-size:16px; margin-bottom:6px;">Skift population?</div>
        <div style="font-size:14px; color:#65676b; line-height:1.5;">
          Skift til <strong id="switchPopName"></strong>?<br>
          <span id="switchPopWarning" style="display:none;">
            Dette kursus har <strong id="switchPopCount"></strong> — de slettes permanent ved skift.
          </span>
        </div>
      </div>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn btn-secondary" onclick="cancelPopSwitch()">Annuller</button>
      <button type="button" id="switchPopConfirmBtn" class="btn btn-primary" onclick="confirmPopSwitch()">Skift</button>
    </div>
  </div>
</div>

{{-- Slet alle modal --}}
<div id="clearModal" class="modal-bg">
  <div class="modal-card">
    <div style="display:flex; align-items:flex-start; gap:14px; margin-bottom:18px;">
      <div style="width:40px; height:40px; border-radius:50%; background:#fee2e2; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <i class="fa-solid fa-trash" style="color:#b91c1c; font-size:15px;"></i>
      </div>
      <div>
        <div style="font-weight:700; font-size:16px; margin-bottom:6px;">Slet alle personas?</div>
        <div style="font-size:14px; color:#65676b; line-height:1.5;">
          Sletter <strong>{{ $total }}</strong> personas og deres billeder fra <strong>{{ $population->name }}</strong>. Kan ikke fortrydes.
        </div>
      </div>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn btn-secondary" onclick="closeModal('clearModal')">Annuller</button>
      <form method="POST" action="{{ url("$base/personas/clear") }}" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-danger">Slet alle</button>
      </form>
    </div>
  </div>
</div>

{{-- Create population modal --}}
<div id="createPopModal" class="modal-bg">
  <div class="modal-card">
    <h3 style="margin:0 0 14px;">Ny population</h3>
    <form method="POST" action="{{ url('/simulation/admin/populations') }}">
      @csrf
      <div style="margin-bottom:14px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Navn *</label>
        <input type="text" name="name" required autofocus placeholder="fx Gymnasieelever, Voksne danskere…"
          style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
      </div>
      <div style="margin-bottom:18px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#65676b; margin-bottom:4px;">Beskrivelse</label>
        <input type="text" name="description" placeholder="Valgfri kort beskrivelse"
          style="width:100%; padding:9px 12px; border:1px solid #dadde1; border-radius:6px; font-size:14px; font-family:inherit;">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="closeModal('createPopModal')">Annuller</button>
        <button type="submit" class="btn btn-primary">Opret</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Population switcher
const hasCourse    = {{ $course ? 'true' : 'false' }};
const currentPopId = '{{ $population->id }}';
const coursePopId  = '{{ $course?->population_id ?? '' }}';
const activityCount = {{ $activityCount ?? 0 }};
let pendingPopId = null;

document.getElementById('popSwitcher').addEventListener('change', function () {
  const newId = this.value;
  if (newId === '__new__') {
    this.value = currentPopId;
    openModal('createPopModal');
    return;
  }
  if (newId === currentPopId) return;
  if (!hasCourse) {
    window.location = this.options[this.selectedIndex].dataset.url;
    return;
  }
  pendingPopId = newId;
  const name = this.options[this.selectedIndex].text.replace(/\s*\(\d+\)\s*$/, '');
  document.getElementById('switchPopName').textContent = name;
  const hasActivity = activityCount > 0 && newId !== coursePopId;
  const warning = document.getElementById('switchPopWarning');
  const btn = document.getElementById('switchPopConfirmBtn');
  if (hasActivity) {
    document.getElementById('switchPopCount').textContent =
      activityCount + ' {{ $activityCount === 1 ? "reaktion" : "reaktioner og kommentarer" }}';
    warning.style.display = 'inline';
    btn.textContent = 'Skift og slet aktivitet';
    btn.className = 'btn btn-danger';
  } else {
    warning.style.display = 'none';
    btn.textContent = 'Skift';
    btn.className = 'btn btn-primary';
  }
  openModal('switchPopModal');
});

function confirmPopSwitch() {
  const hasActivity = activityCount > 0 && pendingPopId !== coursePopId;
  document.getElementById('switchPopId').value = pendingPopId;
  document.getElementById('switchPopForce').value = hasActivity ? '1' : '0';
  closeModal('switchPopModal');
  document.getElementById('switchPopForm').submit();
}
function cancelPopSwitch() {
  closeModal('switchPopModal');
  document.getElementById('popSwitcher').value = currentPopId;
  pendingPopId = null;
}

// Generation popover + submit
const genPopover = document.getElementById('genPopover');
const genCount   = document.getElementById('genCount');
const genSkip    = document.getElementById('genSkipImages');
const genLabel   = document.getElementById('genCountLabel');
function closeGenPopover() { genPopover.style.display = 'none'; }
document.getElementById('genOptsBtn').addEventListener('click', (e) => {
  e.stopPropagation();
  genPopover.style.display = genPopover.style.display === 'none' ? 'block' : 'none';
});
genCount.addEventListener('input', () => { genLabel.textContent = genCount.value || '0'; });
document.addEventListener('click', (e) => {
  if (!genPopover.contains(e.target) && !e.target.closest('#genOptsBtn')) closeGenPopover();
});
document.getElementById('genQuickBtn').addEventListener('click', () => {
  document.getElementById('genFormCount').value = genCount.value;
  document.getElementById('genFormSkip').value = genSkip.checked ? '1' : '';
  document.getElementById('genForm').submit();
});

// Generation status polling
const STATUS_URL = '{{ url("$base/personas/status") }}';
let lastTotal = {{ $total }};
async function pollStatus() {
  try {
    const res = await fetch(STATUS_URL);
    const s = await res.json();
    const pending = s.queued - s.done - s.errors;
    if (s.queued > 0 && pending > 0) {
      document.getElementById('progress').style.display = 'block';
      document.getElementById('genQuickBtn').disabled = true;
      document.getElementById('progLabel').textContent = `${s.done + s.errors} / ${s.queued}`;
      document.getElementById('progBar').style.width = (((s.done + s.errors) / s.queued) * 100) + '%';
      if (s.errors > 0) document.getElementById('progErrors').textContent = `${s.errors} fejl`;
      const emptyLabel = document.getElementById('emptyProgLabel');
      if (emptyLabel) emptyLabel.textContent = `${s.done + s.errors} / ${s.queued}`;
    } else {
      document.getElementById('progress').style.display = 'none';
      document.getElementById('genQuickBtn').disabled = false;
    }
    if (s.total !== lastTotal) { lastTotal = s.total; window.location.reload(); }
  } catch {}
}
setInterval(pollStatus, 3000);
pollStatus();
</script>
@endsection
