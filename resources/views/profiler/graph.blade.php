@extends('layouts.app')
@section('content')

<div class="view-header">
  <h1>Profiler <span style="font-weight: 400; color: #65676b; font-size: 14px;">({{ $personaCount }} personer, {{ $edgeCount }} venskaber)</span></h1>
</div>

@include('profiler._tabs')

@if ($edgeCount === 0)
  <div class="card" style="text-align: center; padding: 40px; color: #65676b;">Ingen social graf bygget endnu.</div>
@else

<style>
.graph-wrap { position: relative; height: 720px; background: #fff; border: 1px solid #dadde1; border-radius: 12px; overflow: hidden; }
#sigma-canvas { position: absolute; inset: 0; }
.graph-hint { position: absolute; bottom: 10px; left: 10px; background: rgba(0,0,0,0.6); color: #fff; padding: 6px 10px; border-radius: 6px; font-size: 12px; z-index: 10; }
.node-tooltip { position: fixed; background: rgba(0,0,0,0.9); color: #fff; padding: 8px 12px; border-radius: 6px; font-size: 13px; pointer-events: none; z-index: 9999; display: none; max-width: 260px; }
.loading { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #65676b; z-index: 5; }
#errMsg { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); background: #fee2e2; color: #b91c1c; padding: 14px 18px; border-radius: 8px; z-index: 20; display: none; max-width: 500px; font-size: 13px; }
</style>

<div class="graph-wrap">
  <div id="sigma-canvas"></div>
  <div class="graph-hint">Træk, zoom. Klik en person for at åbne profil.</div>
  <div class="node-tooltip" id="tooltip"></div>
  <div class="loading" id="loading"><div id="loadingText">Indlæser graf...</div></div>
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
  const setStatus = t => { document.getElementById('loadingText').textContent = t; };
  function fail(msg) { loading.style.display = 'none'; errEl.innerHTML = msg; errEl.style.display = 'block'; console.error(msg); }

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

    setStatus('Henter data...');
    const res = await fetch('{{ url("/slophub/profiler/graph/data") }}');
    const data = await res.json();

    const graph = new Graph();
    data.nodes.forEach(n => {
      graph.addNode(n.id, {
        label: n.label, x: (Math.random()-0.5)*100, y: (Math.random()-0.5)*100,
        size: 10, color: n.color, image: n.image, type: 'circle',
        url: n.url, subculture: n.subculture, age: n.age,
      });
    });
    data.edges.forEach(e => { if (graph.hasNode(e.source) && graph.hasNode(e.target)) try { graph.addEdgeWithKey(e.id, e.source, e.target, { color: '#bcc0c4', size: 1.5 }); } catch {} });

    forceAtlas2.assign(graph, { iterations: 200, settings: { gravity: 1, scalingRatio: 10, slowDown: 2, barnesHutOptimize: graph.order > 200 } });

    const nodeCount = graph.order;
    const minSize = nodeCount < 50 ? 20 : (nodeCount < 200 ? 10 : 6);
    const maxSize = nodeCount < 50 ? 60 : (nodeCount < 200 ? 34 : 20);
    const degs = []; graph.forEachNode(n => degs.push(graph.degree(n)));
    const sorted = [...degs].sort((a,b)=>a-b);
    const pct = p => sorted[Math.min(sorted.length-1, Math.floor(sorted.length*p))];
    const lo = pct(0.10), hi = pct(0.90), range = Math.max(1, hi-lo);
    graph.forEachNode(n => {
      const t = Math.max(0, Math.min(1, (graph.degree(n)-lo)/range));
      graph.setNodeAttribute(n, 'size', minSize + t*(maxSize-minSize));
    });

    const settings = {
      renderEdgeLabels: false, labelRenderedSizeThreshold: 0,
      defaultNodeType: 'circle', minCameraRatio: 0.05, maxCameraRatio: 20,
      labelColor: { color: '#1c1e21' }, labelSize: 12, labelWeight: '600',
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

    const container = document.getElementById('sigma-canvas');
    const renderer = new Sigma(graph, container, settings);
    loading.style.display = 'none';

    const tooltip = document.getElementById('tooltip');
    renderer.on('enterNode', ({ node }) => {
      const a = graph.getNodeAttributes(node);
      tooltip.innerHTML = `<strong>${a.label}</strong>${a.age ? ', '+a.age : ''}<br><span style="color:#aaa">${a.subculture}</span><br><span style="color:#aaa">${graph.degree(node)} venner</span>`;
      tooltip.style.display = 'block';
    });
    renderer.on('leaveNode', () => tooltip.style.display = 'none');
    document.addEventListener('mousemove', e => { tooltip.style.left = (e.clientX+14)+'px'; tooltip.style.top = (e.clientY+14)+'px'; });
    renderer.on('clickNode', ({ node }) => { const a = graph.getNodeAttributes(node); if (a.url) window.open(a.url, '_blank'); });
  } catch (e) { fail('Fejl: ' + e.message); }
})();
</script>
@endif
@endsection
