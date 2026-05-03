@extends('layouts.app')
@section('content')

@php $base = '/slophub/admin/populations/'.$population->id; @endphp
<div class="view-header">
  <h1>
    <a href="{{ url('/slophub/admin/populations') }}" style="color:#1877f2;"><i class="fa-solid fa-arrow-left"></i></a>
    <span style="font-weight:400;color:#65676b;">Population:</span> {{ $population->name }}
  </h1>
  <form method="POST" action="{{ url("$base/personlighed/reset-all") }}" onsubmit="return confirm('Nulstil ALLE regler til defaults?')" style="display:inline;">
    @csrf
    <button type="submit" class="btn btn-secondary" style="font-size:12px;">Nulstil alle til defaults</button>
  </form>
</div>

@include('admin.populations._tabs', ['population' => $population])
@include('admin.populations._konfig_subtabs', ['population' => $population])

<style>
.pl-layout { display: grid; grid-template-columns: 220px 1fr; gap: 16px; align-items: start; }
.pl-side { position: sticky; top: 14px; background: #fff; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); padding: 8px; max-height: calc(100vh - 40px); overflow-y: auto; }
.pl-side h4 { font-size: 11px; font-weight: 700; color: #65676b; text-transform: uppercase; letter-spacing: 0.4px; padding: 10px 10px 4px; }
.pl-side a { display: block; padding: 7px 10px; border-radius: 6px; font-size: 13px; color: #1c1e21; text-decoration: none; cursor: pointer; }
.pl-side a:hover { background: #f0f2f5; }
.pl-side a.active { background: #e7f3ff; color: #1877f2; font-weight: 600; }
.pl-side .sub { font-size: 12px; color: #65676b; padding-left: 20px; }
.pl-side .sub:hover { color: #1c1e21; }
.pl-content { min-width: 0; }
.pl-group { display: none; }
.pl-group.active { display: block; }
.pl-group h2 { font-size: 16px; font-weight: 700; margin-bottom: 10px; color: #1c1e21; padding-bottom: 6px; border-bottom: 2px solid #e7f3ff; scroll-margin-top: 14px; }
.pl-attr-card { background: #fff; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.08); margin-bottom: 12px; overflow: hidden; scroll-margin-top: 14px; }
.pl-attr-head { display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; background: #f7f8fa; border-bottom: 1px solid #e4e6eb; }
.pl-attr-head h3 { font-size: 14px; font-weight: 700; color: #1c1e21; }
.pl-mode { font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
.pl-mode.always { background: #dcfce7; color: #166534; }
.pl-mode.loud { background: #fef3c7; color: #92400e; }
.pl-mode.subculture { background: #e7f3ff; color: #1877f2; }
.pl-rules { padding: 4px 0; }
.pl-rule { display: grid; grid-template-columns: 160px 1fr auto; gap: 12px; padding: 10px 16px; border-top: 1px solid #f0f2f5; align-items: start; }
.pl-rule:first-child { border-top: none; }
.pl-rule.inactive { opacity: 0.45; }
.pl-rule-label { font-weight: 600; font-size: 13px; color: #1c1e21; padding-top: 8px; }
.pl-rule-label .k { display: block; font-size: 11px; color: #65676b; font-weight: 400; margin-top: 2px; font-family: ui-monospace, monospace; }
.pl-rule-text { position: relative; }
.pl-rule-text textarea { width: 100%; border: 1px solid transparent; border-radius: 6px; padding: 8px 10px; font-size: 13px; font-family: inherit; line-height: 1.45; color: #1c1e21; background: #fafbfc; resize: vertical; min-height: 44px; }
.pl-rule-text textarea:hover { border-color: #dadde1; background: #fff; }
.pl-rule-text textarea:focus { outline: none; border-color: #1877f2; background: #fff; box-shadow: 0 0 0 2px #e7f3ff; }
.pl-rule-text textarea.dirty { border-color: #f59e0b; background: #fffbeb; }
.pl-rule-text textarea.saved { border-color: #22c55e; }
.pl-rule-text textarea.empty { color: #9ca3af; font-style: italic; }
.pl-rule-status { font-size: 10px; color: #65676b; text-align: right; margin-top: 4px; min-height: 14px; }
.pl-rule-actions { display: flex; flex-direction: column; gap: 4px; padding-top: 4px; }
.pl-rule-actions button { background: none; border: 1px solid #dadde1; border-radius: 6px; padding: 4px 8px; font-size: 11px; cursor: pointer; color: #65676b; white-space: nowrap; }
.pl-rule-actions button:hover { background: #f0f2f5; color: #1c1e21; }
.pl-rule-actions button.toggle-on { color: #166534; border-color: #bbf7d0; }
.pl-rule-actions button.toggle-off { color: #b91c1c; border-color: #fecaca; }
.pl-summary { font-size: 12px; color: #65676b; padding: 8px 16px; background: #f7f8fa; border-top: 1px solid #e4e6eb; }
@media (max-width: 900px) {
  .pl-layout { grid-template-columns: 1fr; }
  .pl-side { position: static; max-height: none; }
  .pl-rule { grid-template-columns: 1fr; gap: 6px; }
  .pl-rule-label { padding-top: 0; }
}
</style>

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="pl-layout">
  <aside class="pl-side">
    <h4>Kategorier</h4>
    @foreach ($groups as $catKey => $cat)
      <a class="pl-cat-link" data-cat="{{ $catKey }}" href="javascript:void(0)" onclick="plSelectCat('{{ $catKey }}')">{{ $cat['label'] }}</a>
      @foreach ($cat['attributes'] as $attrKey => $attr)
        <a class="sub" data-cat="{{ $catKey }}" data-attr="{{ $attrKey }}" href="javascript:void(0)" onclick="plSelectAttr('{{ $catKey }}','{{ $attrKey }}')">{{ $attr['label'] }}</a>
      @endforeach
    @endforeach
  </aside>

  <div class="pl-content">
    @foreach ($groups as $catKey => $cat)
      <div class="pl-group" data-cat="{{ $catKey }}">
        <h2>{{ $cat['label'] }}</h2>

        @foreach ($cat['attributes'] as $attrKey => $attr)
          <div class="pl-attr-card" id="attr-{{ $attrKey }}">
            <div class="pl-attr-head">
              <h3>{{ $attr['label'] }}</h3>
              <span class="pl-mode {{ $attr['mode'] }}">
                @if ($attr['mode'] === 'always') altid
                @elseif ($attr['mode'] === 'loud') loud
                @else subkultur
                @endif
              </span>
            </div>
            <div class="pl-rules">
              @foreach ($attr['values'] as $valueKey => $defaultText)
                @php
                  $rule = $rules->get($attrKey.'::'.$valueKey);
                  $ruleId = $rule?->id;
                  $ruleText = $rule?->rule_text ?? $defaultText;
                  $isActive = $rule?->is_active ?? true;
                @endphp
                <div class="pl-rule {{ $isActive ? '' : 'inactive' }}" data-rule-id="{{ $ruleId }}">
                  <div class="pl-rule-label">
                    {{ $valueKey }}
                    <span class="k">{{ $attrKey }}</span>
                  </div>
                  <div class="pl-rule-text">
                    <textarea
                      rows="2"
                      placeholder="Skriv psykologisk regel: Du er... derfor..."
                      data-original="{{ $ruleText }}"
                      oninput="plDirty(this)"
                      onblur="plSave(this, {{ $ruleId }})">{{ $ruleText }}</textarea>
                    <div class="pl-rule-status" id="status-{{ $ruleId }}"></div>
                  </div>
                  <div class="pl-rule-actions">
                    <button type="button" class="{{ $isActive ? 'toggle-on' : 'toggle-off' }}" onclick="plToggle(this, {{ $ruleId }})">
                      {{ $isActive ? 'Aktiv' : 'Inaktiv' }}
                    </button>
                    <button type="button" onclick="plReset(this, {{ $ruleId }})" title="Nulstil til default">Nulstil</button>
                  </div>
                </div>
              @endforeach
            </div>
            <div class="pl-summary">{{ count($attr['values']) }} værdier</div>
          </div>
        @endforeach
      </div>
    @endforeach
  </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function plSelectCat(catKey) {
  document.querySelectorAll('.pl-group').forEach(el => {
    el.classList.toggle('active', el.dataset.cat === catKey);
  });
  document.querySelectorAll('.pl-cat-link').forEach(el => {
    el.classList.toggle('active', el.dataset.cat === catKey);
  });
  window.scrollTo({ top: 0, behavior: 'instant' });
}

function plSelectAttr(catKey, attrKey) {
  plSelectCat(catKey);
  const el = document.getElementById('attr-' + attrKey);
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

(function initCat() {
  const hash = window.location.hash || '';
  let catKey = null;
  if (hash.startsWith('#attr-')) {
    const attrKey = hash.slice(6);
    const link = document.querySelector(`.pl-side a.sub[data-attr="${attrKey}"]`);
    if (link) {
      plSelectAttr(link.dataset.cat, attrKey);
      return;
    }
  }
  if (!catKey) {
    const first = document.querySelector('.pl-cat-link');
    catKey = first?.dataset.cat;
  }
  if (catKey) plSelectCat(catKey);
})();

function plDirty(el) {
  if (el.value !== el.dataset.original) {
    el.classList.add('dirty');
    el.classList.remove('saved');
  } else {
    el.classList.remove('dirty');
  }
}

async function plSave(textarea, ruleId) {
  if (!ruleId) return;
  if (textarea.value === textarea.dataset.original) return;
  const status = document.getElementById('status-' + ruleId);
  status.textContent = 'Gemmer...';
  try {
    const res = await fetch(`{{ url("$base/personlighed") }}/${ruleId}`, {
      method: 'PATCH',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ rule_text: textarea.value }),
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    textarea.dataset.original = textarea.value;
    textarea.classList.remove('dirty');
    textarea.classList.add('saved');
    status.textContent = 'Gemt ' + new Date().toLocaleTimeString('da-DK', {hour:'2-digit', minute:'2-digit'});
    setTimeout(() => textarea.classList.remove('saved'), 1500);
  } catch (e) {
    status.textContent = 'Fejl — prøv igen';
    status.style.color = '#b91c1c';
  }
}

async function plToggle(btn, ruleId) {
  if (!ruleId) return;
  const row = btn.closest('.pl-rule');
  const willBeActive = row.classList.contains('inactive');
  try {
    const res = await fetch(`{{ url("$base/personlighed") }}/${ruleId}`, {
      method: 'PATCH',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ is_active: willBeActive }),
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    row.classList.toggle('inactive', !willBeActive);
    btn.textContent = willBeActive ? 'Aktiv' : 'Inaktiv';
    btn.classList.toggle('toggle-on', willBeActive);
    btn.classList.toggle('toggle-off', !willBeActive);
  } catch (e) { alert('Kunne ikke skifte status.'); }
}

async function plReset(btn, ruleId) {
  if (!ruleId) return;
  if (!confirm('Nulstil denne regel til default?')) return;
  try {
    const res = await fetch(`{{ url("$base/personlighed") }}/${ruleId}/reset`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    });
    const data = await res.json();
    const textarea = btn.closest('.pl-rule').querySelector('textarea');
    textarea.value = data.rule_text;
    textarea.dataset.original = data.rule_text;
    textarea.classList.remove('dirty');
    textarea.classList.add('saved');
    setTimeout(() => textarea.classList.remove('saved'), 1500);
  } catch (e) { alert('Kunne ikke nulstille.'); }
}
</script>

@endsection
