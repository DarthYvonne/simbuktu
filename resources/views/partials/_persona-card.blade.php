{{-- Variables expected: $p (persona array), $href (card link URL), $thumbUrl (persona photo thumb URL) --}}

@once
<style>
.persona-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }
.pc {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 0 0 1px rgba(0,0,0,0.05);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  color: #1c1e21;
  text-decoration: none;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.pc:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.05); }
.pc-banner {
  height: 64px;
  background-color: #dde0e6;
  background-size: cover;
  background-position: center;
  flex-shrink: 0;
}
.pc-body { padding: 0 14px 12px; flex: 1; }
.pc-head {
  display: flex;
  gap: 12px;
  align-items: center;
  margin-top: -14px;
  padding-bottom: 10px;
}
.pc-av {
  width: 64px; height: 64px; border-radius: 50%; object-fit: cover; display: block; flex-shrink: 0;
  border: 3px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
.pc-av-ph {
  width: 64px; height: 64px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 20px;
  border: 3px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.2);
  background: #606770; color: #fff;
}
.pc-ident { min-width: 0; padding-top: 12px; }
.pc-name { font-weight: 700; font-size: 15px; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pc-occ { font-size: 12px; color: #65676b; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pc-bio {
  font-size: 12.5px; color: #444; line-height: 1.45; font-style: italic;
  padding: 0 0 0 16px;
  position: relative;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.pc-bio::before {
  content: '\201C'; position: absolute; left: 0; top: -5px;
  font-size: 24px; font-style: normal; line-height: 1;
  color: #1877f2; font-family: Georgia, serif; opacity: 0.35;
}
.pc-footer {
  padding: 8px 14px;
  background: var(--accent-soft);
  border-top: 1px solid color-mix(in srgb, var(--accent) 15%, transparent);
  display: flex; flex-wrap: wrap; gap: 5px;
  min-height: 36px; align-items: center;
}
.pc-footer-tag {
  font-size: 11px; padding: 2px 9px; border-radius: 10px; font-weight: 500; line-height: 1.6;
  background: rgba(255,255,255,0.75);
  border: 1px solid rgba(255,255,255,0.9);
  color: #b91c1c;
}
.pc-footer-tag.sub { color: #166534; }
.pc-flag-badge {
  position: absolute; top: 8px; right: 8px;
  background: #fef3c7; color: #92400e;
  border: 1px solid #fde68a;
  width: 24px; height: 24px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.15);
  z-index: 2;
}
.pc { position: relative; }
</style>
@endonce

@php
  $_cover = app(\App\Services\CoverPicker::class)->pickFor($p);
  $_dims = array_slice(array_values(array_filter($p['dimensions'] ?? [], fn ($d) => !empty($d['show_on_profile']))), 0, 3);
  $_hasFooter = !empty($_dims);
@endphp

<a href="{{ $href }}" class="pc">
  @if (!empty($p['coherence_flags']))
    <div class="pc-flag-badge" title="LLM har markeret mulige inkohærente træk — gennemse personaen"><i class="fa-solid fa-triangle-exclamation"></i></div>
  @endif
  <div class="pc-banner" @if($_cover) style="background-image: url('{{ $_cover['thumb'] }}&fit=crop&crop=center&w=800&h=200')" @endif></div>
  <div class="pc-body">
    <div class="pc-head">
      @if (!empty($p['image_file']))
        <img class="pc-av" src="{{ $thumbUrl }}" alt="">
      @else
        <div class="pc-av-ph">{{ strtoupper(substr($p['name'] ?? '?', 0, 2)) }}</div>
      @endif
      <div class="pc-ident">
        @php $_age = $p['demographics']['age'] ?? null; @endphp
        <div class="pc-name">{{ $p['name'] ?? 'Ukendt' }}@if($_age), {{ $_age }}@endif</div>
        <div class="pc-occ">{{ $p['demographics']['occupation_hint'] ?? '' }}</div>
      </div>
    </div>

    @if (!empty($p['bio']))
    <div class="pc-bio">{{ $p['bio'] }}</div>
    @endif
  </div>

  @if ($_hasFooter)
  <div class="pc-footer">
    @foreach ($_dims as $_d)
      <span class="pc-footer-tag sub">{{ $_d['facet'] ?? '' }}</span>
    @endforeach
  </div>
  @endif
</a>
