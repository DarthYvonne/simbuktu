@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Analyse</h1>
  <form method="GET" action="{{ url('/simulation/analyse') }}">
    <select name="post" onchange="this.form.submit()" style="padding: 8px 12px; max-width: calc(100vw - 36px);">
      @foreach ($posts as $p)
        <option value="{{ $p->id }}" {{ $post && $post->id === $p->id ? 'selected' : '' }}>
          #{{ $p->id }} — {{ \Illuminate\Support\Str::limit($p->body, 60) }} ({{ $p->author_name }})
        </option>
      @endforeach
    </select>
  </form>
</div>

@if (!$post)
  <div class="card" style="text-align: center; padding: 40px; color: #65676b;">
    Ingen opslag at analysere endnu.
  </div>
@else

@php
$reactionIcon = ['like'=>'thumbs-up','love'=>'heart','haha'=>'face-laugh-squint','wow'=>'face-surprise','sad'=>'face-sad-tear','angry'=>'face-angry'];
$reactionColor = ['like'=>'#1877f2','love'=>'#e0245e','haha'=>'#f7b928','wow'=>'#f7b928','sad'=>'#3b82f6','angry'=>'#e9710f'];
$max = max(1, max($data['totals']['exposures'], $data['totals']['comments']));
function bar($v, $max, $color = '#1877f2') { return '<div style="height:8px;background:#f0f2f5;border-radius:4px;overflow:hidden;"><div style="height:100%;background:'.$color.';width:'.($max>0?($v/$max*100):0).'%;"></div></div>'; }
@endphp

<style>
.analyse-tabs { display: flex; gap: 2px; border-bottom: 1px solid #dadde1; margin-bottom: 14px; }
.analyse-tabs button { padding: 10px 18px; border: none; background: transparent; border-bottom: 3px solid transparent; cursor: pointer; font-weight: 600; font-size: 14px; color: #65676b; font-family: inherit; }
.analyse-tabs button.active { color: #1877f2; border-bottom-color: #1877f2; }
.analyse-pane { display: none; }
.analyse-pane.active { display: block; }
</style>

<div class="analyse-tabs">
  <button class="active" data-tab="statistik"><i class="fa-solid fa-chart-simple"></i> Statistik</button>
  <button data-tab="graf"><i class="fa-solid fa-circle-nodes"></i> Graf</button>
  <button data-tab="sentiment"><i class="fa-solid fa-face-grin-wink"></i> Sentiment</button>
</div>

<div class="analyse-pane active" data-pane="statistik">

<div class="stats-grid">
  <div class="card" style="text-align: center;"><div style="font-size: 28px; font-weight: 700; color: #1877f2;">{{ $data['totals']['exposures'] }}</div><div style="font-size: 11px; color: #65676b; text-transform: uppercase;">Set af</div></div>
  <div class="card" style="text-align: center;"><div style="font-size: 28px; font-weight: 700; color: #1877f2;">{{ $data['totals']['comments'] }}</div><div style="font-size: 11px; color: #65676b; text-transform: uppercase;">Kommentarer</div></div>
  <div class="card" style="text-align: center;"><div style="font-size: 28px; font-weight: 700; color: #1877f2;">{{ $data['totals']['reactions_total'] }}</div><div style="font-size: 11px; color: #65676b; text-transform: uppercase;">Reaktioner</div></div>
  <div class="card" style="text-align: center;"><div style="font-size: 28px; font-weight: 700; color: #1877f2;">{{ $data['totals']['shares'] }}</div><div style="font-size: 11px; color: #65676b; text-transform: uppercase;">Delinger</div></div>
</div>

<div class="card">
  <h3 style="font-size: 14px; color: #65676b; text-transform: uppercase; margin-bottom: 12px;">Aktivitet pr. runde</h3>
  <div id="rounds-chart" style="height: 280px; width: 100%;"></div>
</div>

<script src="https://code.highcharts.com/11.4.8/highcharts.js"></script>
<script>
(function () {
  const rounds = @json($data['rounds']);
  Highcharts.chart('rounds-chart', {
    chart: { type: 'spline', backgroundColor: 'transparent', style: { fontFamily: 'inherit' } },
    title: { text: null },
    credits: { enabled: false },
    xAxis: {
      categories: rounds.map(r => 'Runde ' + r.round),
      tickmarkPlacement: 'on',
      lineColor: '#dadde1',
      labels: { style: { color: '#65676b', fontSize: '11px' } }
    },
    yAxis: {
      title: { text: null },
      allowDecimals: false,
      gridLineColor: '#f0f2f5',
      labels: { style: { color: '#65676b', fontSize: '11px' } }
    },
    legend: { itemStyle: { color: '#65676b', fontWeight: '500' } },
    tooltip: { shared: true, backgroundColor: '#fff', borderColor: '#dadde1', style: { fontSize: '12px' } },
    plotOptions: {
      spline: {
        marker: { enabled: true, radius: 4, symbol: 'circle', lineWidth: 2, lineColor: '#fff' },
        lineWidth: 3
      }
    },
    series: [
      { name: 'Eksponeret', data: rounds.map(r => r.exposed), color: '#7cb9f0' },
      { name: 'Reaktioner', data: rounds.map(r => r.reactions), color: '#1877f2' }
    ]
  });
})();
</script>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
  <div class="card">
    <h3 style="font-size: 14px; color: #65676b; text-transform: uppercase; margin-bottom: 12px;">Reaktioner</h3>
    @if (empty($data['reactions']))
      <div style="color: #65676b; font-size: 13px;">Ingen reaktioner endnu.</div>
    @else
      @foreach ($data['reactions'] as $type => $count)
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
          <i class="fa-solid fa-{{ $reactionIcon[$type] ?? 'thumbs-up' }}" style="color: {{ $reactionColor[$type] ?? '#1877f2' }}; font-size: 20px; width: 28px; text-align: center;"></i>
          <div style="flex: 1;">{!! bar($count, $data['totals']['reactions_total']) !!}</div>
          <span style="font-family: monospace; font-weight: 600; min-width: 30px; text-align: right;">{{ $count }}</span>
        </div>
      @endforeach
    @endif
  </div>

  <div class="card">
    <h3 style="font-size: 14px; color: #65676b; text-transform: uppercase; margin-bottom: 12px;">Alder</h3>
    @php $ageMax = max($data['age_buckets']) ?: 1; @endphp
    @foreach ($data['age_buckets'] as $bucket => $count)
      <div style="margin-bottom: 6px;">
        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 2px;">
          <span>{{ $bucket }}</span><strong>{{ $count }}</strong>
        </div>
        {!! bar($count, $ageMax, '#7c3aed') !!}
      </div>
    @endforeach
  </div>
</div>

</div><!-- /statistik pane -->

<div class="analyse-pane" data-pane="graf">

<div class="card">
  <h3 style="font-size: 14px; color: #65676b; text-transform: uppercase; margin-bottom: 12px;">Spredning — hvordan opslaget nåede ud</h3>
  <p style="font-size: 12px; color: #65676b; margin-bottom: 10px;">Større punkter = personen delte opslaget.</p>
  <div id="spread-wrap" style="position: relative; height: 540px; width: 100%; max-width: 100%; background: #fff; border: 1px solid #dadde1; border-radius: 8px; overflow: hidden;">
    <div id="spread-canvas" style="position: absolute; inset: 0;"></div>
    <div id="spread-loading" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #65676b;">Indlæser spredning...</div>
    <div id="spread-tooltip" style="position: fixed; background: rgba(0,0,0,0.9); color: #fff; padding: 6px 10px; border-radius: 6px; font-size: 12px; pointer-events: none; z-index: 9999; display: none;"></div>
  </div>
</div>

<script type="importmap">
{
  "imports": {
    "graphology": "https://cdn.jsdelivr.net/npm/graphology@0.25.4/+esm",
    "graphology-layout-forceatlas2": "https://cdn.jsdelivr.net/npm/graphology-layout-forceatlas2@0.10.1/+esm",
    "sigma": "https://cdn.jsdelivr.net/npm/sigma@3.0.0/+esm",
    "@sigma/node-image": "https://cdn.jsdelivr.net/npm/@sigma/node-image@3.0.0/+esm"
  }
}
</script>
<script type="module">
let __spreadStarted = false;
window.__initSpread = async function () {
  if (__spreadStarted) return;
  __spreadStarted = true;
  const loading = document.getElementById('spread-loading');
  const tooltip = document.getElementById('spread-tooltip');
  try {
    const [g, fa2, s, ni] = await Promise.all([
      import('graphology'),
      import('graphology-layout-forceatlas2'),
      import('sigma'),
      import('@sigma/node-image').catch(() => null),
    ]);
    const Graph = g.default || g.Graph || g;
    const forceAtlas2 = fa2.default || fa2;
    const Sigma = s.Sigma || s.default || s;
    const createNodeImageProgram = ni ? (ni.createNodeImageProgram || ni.default) : null;

    const res = await fetch('{{ url("/simulation/analyse/$post->id/spread") }}');
    const data = await res.json();
    if (data.nodes.length <= 1) { loading.textContent = 'Ingen spredningsdata endnu (vent på næste runde).'; return; }

    const graph = new Graph();
    data.nodes.forEach(n => {
      graph.addNode(n.id, {
        label: n.label, x: (Math.random()-0.5)*100, y: (Math.random()-0.5)*100,
        size: n.size, color: n.color, image: n.image, type: 'circle',
        attribution: n.attribution, action: n.action, round: n.round, url: n.url,
      });
    });
    data.edges.forEach(e => { try { graph.addEdgeWithKey(e.id, e.source, e.target, { color: '#d1d5db', size: 0.8 }); } catch {} });

    forceAtlas2.assign(graph, { iterations: 250, settings: { gravity: 0.5, scalingRatio: 12, slowDown: 3, barnesHutOptimize: graph.order > 100 } });

    const settings = {
      renderEdgeLabels: false, labelRenderedSizeThreshold: 8,
      defaultNodeType: 'circle', minCameraRatio: 0.05, maxCameraRatio: 20,
      labelColor: { color: '#1c1e21' }, labelSize: 11,
    };
    if (createNodeImageProgram) {
      try {
        const img = createNodeImageProgram();
        if (typeof img === 'function') {
          settings.nodeProgramClasses = { image: img };
          graph.forEachNode((n, a) => { if (a.image) graph.setNodeAttribute(n, 'type', 'image'); });
        }
      } catch {}
    }

    const renderer = new Sigma(graph, document.getElementById('spread-canvas'), settings);
    loading.style.display = 'none';

    const labelMap = { via_friend: 'via ven', discovery: 'tilfældig', post: '' };
    renderer.on('enterNode', ({ node }) => {
      const a = graph.getNodeAttributes(node);
      const lines = [`<strong>${a.label}</strong>`];
      if (a.attribution !== 'post') {
        lines.push(`Runde ${a.round} · ${labelMap[a.attribution] || a.attribution}`);
        if (a.action) lines.push(`Handling: ${a.action.replace('react_','😊 ')}`);
      }
      tooltip.innerHTML = lines.join('<br>');
      tooltip.style.display = 'block';
    });
    renderer.on('leaveNode', () => tooltip.style.display = 'none');
    document.addEventListener('mousemove', e => { tooltip.style.left = (e.clientX+14)+'px'; tooltip.style.top = (e.clientY+14)+'px'; });
    renderer.on('clickNode', ({ node }) => { const a = graph.getNodeAttributes(node); if (a.url) window.open(a.url, '_blank'); });
  } catch (e) { loading.textContent = 'Fejl: ' + e.message; console.error(e); }
};
</script>

</div><!-- /graf pane -->

<div class="analyse-pane" data-pane="sentiment">

<style>
.sentiment-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
.sentiment-btn { background: #1877f2; color: #fff; border: none; padding: 9px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; font-family: inherit; }
.sentiment-btn:hover { background: #166fe0; }
.sentiment-btn:disabled { background: #8a8d91; cursor: not-allowed; }
.sentiment-meta { color: #65676b; font-size: 12px; }
.sentiment-summary { background: #fff; border-radius: 8px; padding: 14px 16px; border: 1px solid #dadde1; margin-bottom: 14px; line-height: 1.45; font-size: 14px; color: #1c1e21; }
.sentiment-summary.empty { color: #65676b; font-style: italic; }
.sentiment-cols { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
@media (max-width: 800px) { .sentiment-cols { grid-template-columns: 1fr; } }
.sentiment-col { background: #fff; border-radius: 8px; border: 1px solid #dadde1; overflow: hidden; display: flex; flex-direction: column; }
.sentiment-col-head { padding: 10px 14px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #dadde1; }
.sentiment-col.pro     .sentiment-col-head { background: #e7f7ec; color: #137a3c; }
.sentiment-col.con     .sentiment-col-head { background: #fdecec; color: #b42424; }
.sentiment-col.neutral .sentiment-col-head { background: #f0f2f5; color: #65676b; }
.sentiment-col-count { font-family: monospace; font-size: 13px; }
.sentiment-col-body { padding: 8px; flex: 1; max-height: 560px; overflow-y: auto; }
.sentiment-comment { background: #f8f9fa; border-radius: 8px; padding: 8px 10px; margin-bottom: 6px; font-size: 13px; line-height: 1.4; }
.sentiment-comment .who { font-weight: 600; font-size: 12px; color: #1c1e21; margin-bottom: 2px; }
.sentiment-empty { padding: 14px; color: #8a8d91; font-size: 13px; text-align: center; font-style: italic; }
.sentiment-error { background: #fdecec; border: 1px solid #f5c2c2; color: #b42424; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 12px; }
</style>

<div class="sentiment-bar">
  <button id="sentiment-run" class="sentiment-btn"><i class="fa-solid fa-play"></i> <span id="sentiment-btn-label">Kør sentiment-analyse</span></button>
  <span id="sentiment-meta" class="sentiment-meta">
    @if ($sentiment)
      Senest kørt {{ \Carbon\Carbon::parse($sentiment['generated_at'])->diffForHumans() }} · {{ $sentiment['totals']['total'] }} kommentarer
    @else
      Ingen analyse kørt endnu.
    @endif
  </span>
</div>

<div id="sentiment-error" class="sentiment-error" style="display: none;"></div>

<div id="sentiment-summary" class="sentiment-summary {{ $sentiment ? '' : 'empty' }}">
  {{ $sentiment['summary'] ?? 'Klik på knappen for at klassificere kommentarerne som pro, con eller neutral i forhold til opslaget.' }}
</div>

<div class="sentiment-cols">
  @foreach (['pro' => 'Støttende kommentarer', 'con' => 'Opponerende kommentarer', 'neutral' => 'Neutrale kommentarer'] as $key => $label)
    <div class="sentiment-col {{ $key }}">
      <div class="sentiment-col-head">
        <span>{{ $label }}</span>
        <span class="sentiment-col-count" data-count="{{ $key }}">{{ $sentiment['totals'][$key] ?? 0 }}</span>
      </div>
      <div class="sentiment-col-body" data-bucket="{{ $key }}">
        @if ($sentiment && !empty($sentiment['buckets'][$key]))
          @foreach ($sentiment['buckets'][$key] as $c)
            <div class="sentiment-comment">
              <div class="who">{{ $c['persona_name'] }}</div>
              <div>{{ $c['body'] }}</div>
            </div>
          @endforeach
        @else
          <div class="sentiment-empty">Ingen.</div>
        @endif
      </div>
    </div>
  @endforeach
</div>

<script>
(function () {
  const btn = document.getElementById('sentiment-run');
  const btnLabel = document.getElementById('sentiment-btn-label');
  const errorBox = document.getElementById('sentiment-error');
  const summaryEl = document.getElementById('sentiment-summary');
  const metaEl = document.getElementById('sentiment-meta');

  function escape(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

  function render(data) {
    summaryEl.classList.remove('empty');
    summaryEl.textContent = data.summary || '(ingen opsummering)';
    ['pro','con','neutral'].forEach(k => {
      document.querySelector('[data-count="'+k+'"]').textContent = data.totals[k] || 0;
      const body = document.querySelector('[data-bucket="'+k+'"]');
      const items = data.buckets[k] || [];
      if (!items.length) {
        body.innerHTML = '<div class="sentiment-empty">Ingen.</div>';
        return;
      }
      body.innerHTML = items.map(c =>
        '<div class="sentiment-comment"><div class="who">'+escape(c.persona_name)+'</div><div>'+escape(c.body)+'</div></div>'
      ).join('');
    });
    metaEl.textContent = 'Lige nu kørt · ' + data.totals.total + ' kommentarer';
  }

  const POST_URL = '{{ url("/simulation/analyse/$post->id/sentiment") }}';
  const STATUS_URL = '{{ url("/simulation/analyse/$post->id/sentiment/status") }}';
  let polling = false;

  async function pollStatus() {
    if (polling) return;
    polling = true;
    let attempts = 0;
    while (attempts++ < 90) { // 90 * 2s = 180s
      try {
        const res = await fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } });
        if (res.ok) {
          const data = await res.json();
          if (data.status === 'complete') {
            render(data);
            polling = false;
            btn.disabled = false;
            btnLabel.textContent = 'Kør igen';
            return;
          }
          if (data.status === 'failed') {
            errorBox.textContent = data.error || 'Analysen fejlede.';
            errorBox.style.display = 'block';
            polling = false;
            btn.disabled = false;
            btnLabel.textContent = 'Kør igen';
            return;
          }
          // 'pending' or 'idle' — keep polling
        }
      } catch {}
      await new Promise(r => setTimeout(r, 2000));
    }
    errorBox.textContent = 'Analysen tog for lang tid — prøv igen.';
    errorBox.style.display = 'block';
    polling = false;
    btn.disabled = false;
    btnLabel.textContent = 'Kør igen';
  }

  btn.addEventListener('click', async () => {
    errorBox.style.display = 'none';
    btn.disabled = true;
    btnLabel.textContent = 'Analyserer…';
    try {
      const res = await fetch(POST_URL, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json',
        },
      });
      if (!res.ok && res.status !== 202) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err.error || ('HTTP '+res.status));
      }
      pollStatus();
    } catch (e) {
      errorBox.textContent = e.message;
      errorBox.style.display = 'block';
      btn.disabled = false;
      btnLabel.textContent = 'Kør igen';
    }
  });

  // If the page loads while a sentiment job is already pending (e.g. user
  // navigated away and came back), pick up the poll automatically.
  @if (($post->intelligence['sentiment_pending'] ?? false) === true)
    btn.disabled = true;
    btnLabel.textContent = 'Analyserer…';
    pollStatus();
  @endif
})();
</script>

</div><!-- /sentiment pane -->

<script>
document.querySelectorAll('.analyse-tabs button').forEach(btn => {
  btn.addEventListener('click', () => {
    const tab = btn.dataset.tab;
    document.querySelectorAll('.analyse-tabs button').forEach(b => b.classList.toggle('active', b === btn));
    document.querySelectorAll('.analyse-pane').forEach(p => p.classList.toggle('active', p.dataset.pane === tab));
    if (tab === 'graf' && window.__initSpread) window.__initSpread();
  });
});
</script>

@endif
@endsection
