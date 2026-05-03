@extends('layouts.app')
@section('content')

@php $isActive = auth()->user()->currentCourse()?->id === $course->id; @endphp
<div class="view-header">
  <h1><a href="{{ url('/simulation/admin/courses') }}" style="color: #1877f2;"><i class="fa-solid fa-arrow-left"></i> Kurser</a> <span style="font-weight: 400; color: #1c1e21;">· {{ $course->name }}</span></h1>
  <div style="display: flex; gap: 8px; align-items: center;">
    @if ($isActive)
      <span style="background: #dcfce7; color: #166534; font-size: 12px; font-weight: 700; padding: 5px 12px; border-radius: 14px;">Aktivt kursus</span>
    @else
      <form method="POST" action="{{ url('/simulation/admin/courses/'.$course->id.'/switch') }}">
        @csrf
        <button class="btn btn-primary"><i class="fa-solid fa-right-left"></i> Skift til dette kursus</button>
      </form>
    @endif
    <form method="POST" action="{{ url('/simulation/admin/courses/'.$course->id) }}" onsubmit="return confirm('Slet kurset? ALLE opslag og tilmeldinger slettes.')">
      @csrf
      @method('DELETE')
      <button class="btn btn-danger">Slet kursus</button>
    </form>
  </div>
</div>

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<style>
.course-tabs { display: flex; gap: 2px; border-bottom: 1px solid #dadde1; margin-bottom: 14px; }
.course-tabs button { padding: 10px 18px; border: none; background: transparent; border-bottom: 3px solid transparent; cursor: pointer; font-weight: 600; font-size: 14px; color: #65676b; }
.course-tabs button.active { color: #1877f2; border-bottom-color: #1877f2; }
.tab-pane { display: none; }
.tab-pane.active { display: block; }
.km-panel { background: #fff; border-radius: 12px; padding: 18px 22px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 14px; }
.km-panel h3 { margin-bottom: 12px; font-size: 14px; color: #65676b; text-transform: uppercase; letter-spacing: 0.4px; }
.km-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f2f5; font-size: 13px; gap: 10px; }
.km-row:last-child { border-bottom: none; }
.km-empty { color: #65676b; font-size: 13px; padding: 10px 0; }
.km-invite { background: #e7f3ff; border: 1px solid #b6d8fb; border-radius: 12px; padding: 16px 20px; margin-bottom: 14px; }
.km-invite h3 { margin-bottom: 4px; font-size: 12px; color: #1877f2; text-transform: uppercase; letter-spacing: 0.4px; }
.km-invite .hint { color: #1c1e21; font-size: 13px; margin-bottom: 10px; }
.km-invite .row { display: flex; gap: 8px; align-items: center; }
.km-invite input { flex: 1; padding: 10px 14px; border: 1px solid #b6d8fb; border-radius: 8px; background: #fff; font-family: monospace; font-size: 13px; }
.form-field { display: grid; gap: 4px; margin-bottom: 12px; }
.form-field label { font-weight: 600; font-size: 13px; color: #65676b; }
.form-field input { padding: 9px 12px; border: 1px solid #dadde1; border-radius: 6px; font-family: inherit; font-size: 14px; }
.form-field input:focus { outline: none; border-color: #1877f2; }
.stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 14px; }
.stat-card { background: #fff; border-radius: 12px; padding: 16px 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 12px; }
.stat-card .icon { width: 44px; height: 44px; border-radius: 50%; background: #e7f3ff; display: flex; align-items: center; justify-content: center; color: #1877f2; font-size: 18px; flex-shrink: 0; }
.stat-card .num { font-size: 22px; font-weight: 800; color: #1c1e21; line-height: 1.1; }
.stat-card .lbl { font-size: 11px; color: #65676b; text-transform: uppercase; letter-spacing: 0.4px; margin-top: 2px; }
</style>

<div class="course-tabs">
  <button class="active" data-tab="info">Info</button>
  <button data-tab="branding">Branding</button>
  <button data-tab="deltagere">Deltagere ({{ $memberships->count() }})</button>
  <button data-tab="opslag">Opslag ({{ $posts->count() }})</button>
  <button data-tab="statistik">Statistik</button>
</div>

{{-- INFO --}}
<div class="tab-pane active" data-pane="info">
  <form method="POST" action="{{ url('/simulation/admin/courses/'.$course->id) }}" class="km-panel">
    @csrf
    @method('PATCH')
    <input type="hidden" name="_tab" value="info">
    <h3>Grundlæggende info</h3>
    <div class="form-field">
      <label>Kursusnavn</label>
      <input type="text" name="name" value="{{ $course->name }}" required>
    </div>
    <div class="form-field">
      <label>Beskrivelse</label>
      <input type="text" name="description" value="{{ $course->description }}" placeholder="Kort beskrivelse">
    </div>

    <h3 style="margin-top: 18px;">Kursus-manager</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 10px;">Kontaktoplysninger til underviser / ansvarlig. Vises for deltagerne.</p>
    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
      <div class="form-field">
        <label>Navn</label>
        <input type="text" name="manager_name" value="{{ $course->manager_name }}" placeholder="Fornavn Efternavn">
      </div>
      <div class="form-field">
        <label>Email</label>
        <input type="email" name="manager_email" value="{{ $course->manager_email }}" placeholder="navn@eksempel.dk">
      </div>
      <div class="form-field">
        <label>Telefon</label>
        <input type="text" name="manager_phone" value="{{ $course->manager_phone }}" placeholder="+45 ...">
      </div>
    </div>

    <div style="text-align: right; margin-top: 6px;">
      <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem ændringer</button>
    </div>
  </form>
</div>

{{-- BRANDING --}}
<div class="tab-pane" data-pane="branding">
  <form method="POST" action="{{ url('/simulation/admin/courses/'.$course->id) }}" enctype="multipart/form-data" class="km-panel">
    @csrf
    @method('PATCH')
    <input type="hidden" name="_tab" value="branding">
    <h3>Platform-navn</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 10px;">Navnet kursets deltagere ser. Som standard: "Simbuktu".</p>
    <div class="form-field">
      <label>Navn</label>
      <input type="text" name="platform_name" value="{{ $course->platform_name }}" placeholder="Simbuktu" maxlength="60">
    </div>

    <h3 style="margin-top: 18px;">Logo</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 10px;">Vises i sidebaren. PNG med transparent baggrund anbefales (maks 2 MB).</p>
    @if ($course->logo_path)
      <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 10px; padding: 10px 14px; background: #f7f8fa; border-radius: 8px;">
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->logo_path) }}" alt="Logo" style="max-width: 180px; max-height: 60px; object-fit: contain;">
        <label style="font-size: 13px; color: #b91c1c; cursor: pointer;">
          <input type="checkbox" name="remove_logo" value="1"> Fjern logo
        </label>
      </div>
    @endif
    <div class="form-field">
      <label>Upload nyt logo</label>
      <input type="file" name="logo" accept="image/*">
    </div>

    <h3 style="margin-top: 18px;">Accent-farve</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 10px;">Bruges på knapper, links og aktive elementer. En lysere variant bruges automatisk som baggrund (f.eks. active-nav og tags).</p>
    @php $accentValue = $course->accent_color ?: '#1877f2'; @endphp
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
      <input type="color" id="accentColorPicker" name="accent_color" value="{{ $accentValue }}" style="width: 56px; height: 40px; padding: 2px; border: 1px solid #dadde1; border-radius: 6px; cursor: pointer; background: #fff;">
      <input type="text" id="accentColorText" value="{{ $accentValue }}" maxlength="7" pattern="^#[0-9a-fA-F]{6}$" style="width: 110px; font-family: monospace; font-size: 14px; padding: 9px 12px; border: 1px solid #dadde1; border-radius: 6px;">
      <button type="button" id="accentResetBtn" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">Nulstil til standard</button>
      <div style="display: flex; gap: 6px; margin-left: 8px; align-items: center;">
        <span id="accentPreviewBtn" style="background: {{ $accentValue }}; color: #fff; font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 6px;">Knap</span>
        <span id="accentPreviewTag" style="background: color-mix(in srgb, {{ $accentValue }} 12%, #fff); color: {{ $accentValue }}; font-size: 12px; padding: 3px 10px; border-radius: 12px;">tag</span>
      </div>
    </div>
    <script>
      (function() {
        const picker = document.getElementById('accentColorPicker');
        const text = document.getElementById('accentColorText');
        const resetBtn = document.getElementById('accentResetBtn');
        const previewBtn = document.getElementById('accentPreviewBtn');
        const previewTag = document.getElementById('accentPreviewTag');
        const DEFAULT = '#1877f2';
        function applyPreview(val) {
          previewBtn.style.background = val;
          previewTag.style.background = 'color-mix(in srgb, ' + val + ' 12%, #fff)';
          previewTag.style.color = val;
        }
        picker.addEventListener('input', () => { text.value = picker.value; applyPreview(picker.value); });
        text.addEventListener('input', () => {
          const v = text.value.trim();
          if (/^#[0-9a-fA-F]{6}$/.test(v)) { picker.value = v; applyPreview(v); }
        });
        resetBtn.addEventListener('click', () => { picker.value = DEFAULT; text.value = DEFAULT; applyPreview(DEFAULT); });
      })();
    </script>

    <h3 style="margin-top: 18px;">Favicon</h3>
    <p style="color: #65676b; font-size: 13px; margin-bottom: 10px;">Ikon i browser-fanen. Brug et kvadratisk billede, PNG (maks 1 MB).</p>
    @if ($course->favicon_path)
      <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 10px; padding: 10px 14px; background: #f7f8fa; border-radius: 8px;">
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->favicon_path) }}" alt="Favicon" style="width: 32px; height: 32px; object-fit: contain;">
        <label style="font-size: 13px; color: #b91c1c; cursor: pointer;">
          <input type="checkbox" name="remove_favicon" value="1"> Fjern favicon
        </label>
      </div>
    @endif
    <div class="form-field">
      <label>Upload ny favicon</label>
      <input type="file" name="favicon" accept="image/*">
    </div>

    <div style="text-align: right; margin-top: 6px;">
      <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gem branding</button>
    </div>
  </form>
</div>

{{-- DELTAGERE --}}
<div class="tab-pane" data-pane="deltagere">
  <div class="km-invite">
    <h3>Invitationslink</h3>
    <div class="hint">Del dette link med deltagerne. De opretter konto og får automatisk adgang til kurset.</div>
    <div class="row">
      <input type="text" readonly value="{{ $course->inviteUrl() }}" id="inviteUrl">
      <button class="btn btn-primary" type="button" onclick="copyInvite()"><i class="fa-solid fa-copy"></i> Kopiér</button>
    </div>
  </div>

  <div class="km-panel">
    <h3>Deltagere ({{ $memberships->count() }})</h3>
    @if ($memberships->isEmpty())
      <div class="km-empty">Ingen deltagere endnu. Del invitationslinket ovenfor.</div>
    @else
      @foreach ($memberships as $m)
        <div class="km-row">
          <div style="flex: 1; min-width: 0;">
            <strong>{{ $m->user->name }}</strong>
            <div style="color: #65676b; font-size: 12px;">{{ $m->user->email }}</div>
          </div>
          <span style="color: #65676b; white-space: nowrap; text-align: right;">{{ $m->role }}<br><small>{{ $m->created_at->diffForHumans() }}</small></span>
        </div>
      @endforeach
    @endif
  </div>
</div>

{{-- OPSLAG --}}
<div class="tab-pane" data-pane="opslag">
  <div class="km-panel">
    <h3>Opslag ({{ $posts->count() }})</h3>
    @if ($posts->isEmpty())
      <div class="km-empty">Ingen opslag endnu.</div>
    @else
      @foreach ($posts as $p)
        <div class="km-row">
          <div style="flex: 1; min-width: 0;">
            <strong>{{ $p->author_name }}</strong>
            <div style="color: #65676b; font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ \Illuminate\Support\Str::limit($p->body, 80) }}</div>
          </div>
          <span style="color: #65676b; white-space: nowrap; text-align: right; font-size: 12px;">
            {{ $p->comments_count }} kom · {{ $p->reactionTotal() }} rekt · {{ $p->shares ?? 0 }} del<br>
            <small>Set af {{ $p->reach }} · runde {{ $p->round }}</small>
          </span>
        </div>
      @endforeach
    @endif
  </div>
</div>

{{-- STATISTIK --}}
<div class="tab-pane" data-pane="statistik">
  <div class="stat-grid">
    <div class="stat-card"><div class="icon"><i class="fa-regular fa-pen-to-square"></i></div><div><div class="num">{{ $stats['posts'] }}</div><div class="lbl">Opslag</div></div></div>
    <div class="stat-card"><div class="icon"><i class="fa-regular fa-comment"></i></div><div><div class="num">{{ $stats['comments'] }}</div><div class="lbl">Kommentarer</div></div></div>
    <div class="stat-card"><div class="icon"><i class="fa-solid fa-thumbs-up"></i></div><div><div class="num">{{ $stats['reactions'] }}</div><div class="lbl">Reaktioner</div></div></div>
    <div class="stat-card"><div class="icon"><i class="fa-solid fa-share"></i></div><div><div class="num">{{ $stats['shares'] }}</div><div class="lbl">Delinger</div></div></div>
    <div class="stat-card"><div class="icon"><i class="fa-solid fa-eye"></i></div><div><div class="num">{{ $stats['reach'] }}</div><div class="lbl">Total reach</div></div></div>
    <div class="stat-card"><div class="icon"><i class="fa-solid fa-fire"></i></div><div><div class="num">{{ $stats['avg_virality'] }}</div><div class="lbl">Snit viralitet</div></div></div>
  </div>

  @if ($stats['top_post'])
    <div class="km-panel">
      <h3>Mest virale opslag</h3>
      <div style="font-size: 14px;">
        <strong>{{ $stats['top_post']->author_name }}</strong> ·
        <span style="color: #1877f2; font-weight: 700;">{{ $stats['top_post']->virality_score }}</span> viralitet
      </div>
      <div style="margin-top: 6px; color: #1c1e21; font-size: 14px; line-height: 1.45;">"{{ \Illuminate\Support\Str::limit($stats['top_post']->body, 200) }}"</div>
      <div style="margin-top: 8px; color: #65676b; font-size: 12px;">
        {{ $stats['top_post']->comments_count ?? $stats['top_post']->comments()->count() }} kommentarer · {{ $stats['top_post']->reactionTotal() }} reaktioner · {{ $stats['top_post']->shares ?? 0 }} delinger
      </div>
    </div>
  @else
    <div class="km-panel km-empty">Ingen opslag at lave statistik på endnu.</div>
  @endif
</div>

<script>
function copyInvite() {
  const input = document.getElementById('inviteUrl');
  input.select();
  document.execCommand('copy');
  const btn = event.target.closest('button');
  if (btn) { const old = btn.innerHTML; btn.innerHTML = '<i class="fa-solid fa-check"></i> Kopieret'; setTimeout(() => btn.innerHTML = old, 1500); }
}

document.querySelectorAll('.course-tabs button').forEach(btn => {
  btn.addEventListener('click', () => {
    const tab = btn.dataset.tab;
    document.querySelectorAll('.course-tabs button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelector('.tab-pane[data-pane="' + tab + '"]').classList.add('active');
    history.replaceState(null, '', '#' + tab);
  });
});

// Activate tab from URL hash if present
if (location.hash) {
  const tab = location.hash.slice(1);
  const btn = document.querySelector('.course-tabs button[data-tab="' + tab + '"]');
  if (btn) btn.click();
}
</script>
@endsection
