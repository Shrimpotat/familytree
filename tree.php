<?php
require 'config.php';
$people = fetch_all_persons();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree - Visual</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>
</head>
<body>
<header>
    <h1>Family Tree - Visual</h1>
    <nav><a href="index.php">Dashboard</a></nav>
</header>
<main>
    <div id="chart"></div>
</main>
<script>
const people = <?= json_encode(array_values($people)) ?>;
// Build nodes and links for force graph: parents -> child
const nodes = people.map(p => ({ id: p.id, name: (p.first_name||'') + ' ' + (p.last_name||''), gender: p.gender }));
const links = [];
people.forEach(p => {
    if (p.father_id) links.push({ source: p.father_id, target: p.id });
    if (p.mother_id) links.push({ source: p.mother_id, target: p.id });
    if (p.spouse_id) links.push({ source: p.id, target: p.spouse_id, type: 'spouse' });
});

const width = Math.max(1200, window.innerWidth - 0);
const height = Math.max(800, window.innerHeight - 200);

const svg = d3.select('#chart').append('svg').attr('width', width).attr('height', height);
const container = svg.append('g');

// Enable zoom and pan
const zoom = d3.zoom()
    .scaleExtent([0.15, 3])
    .on('zoom', event => container.attr('transform', event.transform));

svg.call(zoom).call(zoom.transform, d3.zoomIdentity.translate(width / 2, height / 2).scale(0.45));

const link = container.append('g').attr('stroke', '#999').selectAll('line').data(links).enter().append('line').attr('stroke-width', 1.5);
const node = container.append('g').selectAll('g').data(nodes).enter().append('g');

node.append('circle').attr('r', 18).attr('fill', d => d.gender === 'Female' ? '#ffb6c1' : '#87cefa');
node.append('text').attr('dy', 4).attr('x', 22).text(d => d.name);

const simulation = d3.forceSimulation(nodes)
    .force('link', d3.forceLink(links).id(d => d.id).distance(100))
    .force('charge', d3.forceManyBody().strength(-220))
    .force('center', d3.forceCenter(0, 0));

simulation.on('tick', () => {
    link.attr('x1', d => d.source.x).attr('y1', d => d.source.y).attr('x2', d => d.target.x).attr('y2', d => d.target.y);
    node.attr('transform', d => `translate(${d.x},${d.y})`);
});

// Drag
node.call(d3.drag().on('start', (event,d)=>{ if (!event.active) simulation.alphaTarget(0.3).restart(); d.fx=d.x; d.fy=d.y; }).on('drag',(event,d)=>{ d.fx=event.x; d.fy=event.y; }).on('end',(event,d)=>{ if (!event.active) simulation.alphaTarget(0); d.fx=null; d.fy=null;}));

</script>
</body>
</html>
