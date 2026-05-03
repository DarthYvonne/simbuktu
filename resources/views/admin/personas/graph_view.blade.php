@extends('layouts.app')
@section('content')

@php $base = '/simulation/admin/populations/'.$population->id; @endphp
<div class="view-header">
  <h1>
    <a href="{{ url('/simulation/admin/populations') }}" style="color:#1877f2;"><i class="fa-solid fa-arrow-left"></i></a>
    <span style="font-weight:400;color:#65676b;">Population:</span> {{ $population->name }}
    <span style="font-weight:400;color:#65676b;font-size:14px;">({{ $personaCount }} personer, {{ $edgeCount }} venskaber)</span>
  </h1>
  <a href="{{ url("$base/personas/graph") }}" class="btn btn-primary"><i class="fa-solid fa-rotate"></i> Genbyg graf</a>
</div>

@include('admin.populations._tabs', ['population' => $population])

@if ($edgeCount === 0)
  <div class="card" style="text-align: center; padding: 40px; color: #65676b;">
    <p>Ingen graf bygget endnu.</p>
    <a href="{{ url("$base/personas/graph") }}" class="btn btn-primary" style="margin-top: 10px;"><i class="fa-solid fa-play"></i> Byg social graf</a>
  </div>
@else

<style>
.graph-wrap { position: relative; height: 720px; background: #fff; border: 1px solid #dadde1; border-radius: 12px; overflow: hidden; }
#sigma-canvas { position: absolute; inset: 0; }
.graph-hint { position: absolute; bottom: 10px; left: 10px; background: rgba(0,0,0,0.6); color: #fff; padding: 6px 10px; border-radius: 6px; font-size: 12px; z-index: 10; }
.node-tooltip { position: fixed; background: rgba(0,0,0,0.9); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px; pointer-events: none; z-index: 9999; display: none; max-width: 260px; }
.loading { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #65676b; z-index: 5; flex-direction: column; gap: 10px; }
#errMsg { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); background: #fee2e2; color: #b91c1c; padding: 14px 18px; border-radius: 8px; z-index: 20; display: none; max-width: 500px; font-size: 13px; }
</style>

<div class="graph-wrap">
  <div id="sigma-canvas"></div>
  <div class="graph-hint">Træk, zoom. Klik en person for at åbne profil.</div>
  <div class="node-tooltip" id="tooltip"></div>
  <div class="loading" id="loading">
    <div id="loadingText">Indlæser graf...</div>
  </div>
  <div id="errMsg"></div>
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
(async function () {
  const errEl = document.getElementById('errMsg');
  const loading = document.getElementById('loading');
  const setStatus = (t) => { document.getElementById('loadingText').textContent = t; console.log('[graph]', t); };

  function fail(msg) {
    loading.style.display = 'none';
    errEl.innerHTML = msg;
    errEl.style.display = 'block';
    console.error('[graph]', msg);
  }

  try {
    setStatus('Indlæser libraries...');
    const [graphologyMod, fa2Mod, sigmaMod, nodeImageMod] = await Promise.all([
      import('graphology'),
      import('graphology-layout-forceatlas2'),
      import('sigma'),
      import('@sigma/node-image').catch(() => null),
    ]);

    const Graph = graphologyMod.default || graphologyMod.Graph || graphologyMod;
    const forceAtlas2 = fa2Mod.default || fa2Mod;
    const Sigma = sigmaMod.Sigma || sigmaMod.default || sigmaMod;
    const createNodeImageProgram = nodeImageMod ? (nodeImageMod.createNodeImageProgram || nodeImageMod.default) : null;

    console.log('[graph] libs:', { Graph: !!Graph, forceAtlas2: !!forceAtlas2, Sigma: !!Sigma, createNodeImageProgram: !!createNodeImageProgram });

    if (typeof Graph !== 'function') return fail('graphology ikke loadet: ' + typeof Graph);
    if (typeof Sigma !== 'function') return fail('Sigma ikke loadet: ' + typeof Sigma);
    if (!forceAtlas2 || typeof forceAtlas2.assign !== 'function') return fail('ForceAtlas2.assign mangler');

    setStatus('Henter graf-data...');
    const res = await fetch('{{ url("$base/personas/graph/data") }}');
    if (!res.ok) return fail('HTTP ' + res.status + ' ved hent af data');
    const data = await res.json();
    console.log('[graph] data:', data.nodes.length, 'nodes,', data.edges.length, 'edges');

    setStatus('Bygger graf...');
    const graph = new Graph();

    const spread = 100;
    data.nodes.forEach(n => {
      graph.addNode(n.id, {
        label: n.label,
        x: (Math.random() - 0.5) * spread,
        y: (Math.random() - 0.5) * spread,
        size: 10,
        color: n.color,
        image: n.image,
        type: 'circle',
        url: n.url,
        subculture: n.subculture,
        age: n.age,
      });
    });
    data.edges.forEach(e => {
      if (!graph.hasNode(e.source) || !graph.hasNode(e.target)) return;
      try { graph.addEdgeWithKey(e.id, e.source, e.target, { color: '#bcc0c4', size: 1.5 }); } catch {}
    });

    setStatus('Kører layout...');
    forceAtlas2.assign(graph, {
      iterations: 200,
      settings: {
        gravity: 1,
        scalingRatio: 10,
        slowDown: 2,
        barnesHutOptimize: graph.order > 200,
        strongGravityMode: false,
      },
    });

    // Size nodes by degree, using percentile-based scaling so distribution drives the visual
    const nodeCount = graph.order;
    const minSize = nodeCount < 50 ? 20 : (nodeCount < 200 ? 10 : 6);
    const maxSize = nodeCount < 50 ? 60 : (nodeCount < 200 ? 34 : 20);

    const degrees = [];
    graph.forEachNode((node) => degrees.push(graph.degree(node)));
    const sorted = [...degrees].sort((a, b) => a - b);
    const pct = (p) => sorted[Math.min(sorted.length - 1, Math.floor(sorted.length * p))];
    const lo = pct(0.10), hi = pct(0.90);
    const range = Math.max(1, hi - lo);
    console.log('[graph] degree percentiles — p10:', lo, 'p90:', hi);

    graph.forEachNode((node) => {
      const deg = graph.degree(node);
      const t = Math.max(0, Math.min(1, (deg - lo) / range));
      graph.setNodeAttribute(node, 'size', minSize + t * (maxSize - minSize));
    });

    setStatus('Renderer...');
    const container = document.getElementById('sigma-canvas');

    const sigmaSettings = {
      renderEdgeLabels: false,
      labelRenderedSizeThreshold: 0,
      labelDensity: 1,
      labelGridCellSize: 60,
      defaultNodeType: 'circle',
      minCameraRatio: 0.05,
      maxCameraRatio: 20,
      labelColor: { color: '#1c1e21' },
      labelSize: 12,
      labelWeight: '600',
    };

    // Try to enable image program — if anything fails, fall back silently to circles
    let imageProgramWorks = false;
    if (createNodeImageProgram) {
      try {
        const imgProg = createNodeImageProgram();
        if (typeof imgProg === 'function') {
          sigmaSettings.nodeProgramClasses = { image: imgProg };
          graph.forEachNode((node, attrs) => {
            if (attrs.image) graph.setNodeAttribute(node, 'type', 'image');
          });
          imageProgramWorks = true;
          console.log('[graph] image program active');
        }
      } catch (e) {
        console.warn('[graph] image program failed, using circles:', e.message);
      }
    }

    const renderer = new Sigma(graph, container, sigmaSettings);

    loading.style.display = 'none';
    console.log('[graph] rendered with', imageProgramWorks ? 'images' : 'circles');

    const tooltip = document.getElementById('tooltip');
    renderer.on('enterNode', ({ node }) => {
      const a = graph.getNodeAttributes(node);
      const deg = graph.degree(node);
      tooltip.innerHTML = `<strong>${a.label}</strong>${a.age ? ', ' + a.age : ''}<br><span style="color:#aaa">${a.subculture}</span><br><span style="color:#aaa">${deg} venner</span>`;
      tooltip.style.display = 'block';
    });
    renderer.on('leaveNode', () => { tooltip.style.display = 'none'; });
    document.addEventListener('mousemove', (e) => {
      tooltip.style.left = (e.clientX + 14) + 'px';
      tooltip.style.top = (e.clientY + 14) + 'px';
    });
    renderer.on('clickNode', ({ node }) => {
      const a = graph.getNodeAttributes(node);
      if (a.url) window.open(a.url, '_blank');
    });
  } catch (e) {
    fail('Fejl: ' + (e.message || e) + '<br><small>Se browser console for detaljer</small>');
    console.error(e);
  }
})();
</script>

@endif
@endsection
