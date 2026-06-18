(function () {
    'use strict';

    const NODE_WIDTH = 180;
    const NODE_HEIGHT = 72;
    const GRID_SIZE = 24;
    const PORT_RADIUS = 6;
    const MINIMAP_W = 160;
    const MINIMAP_H = 100;
    const MINIMAP_PAD = 6;

    let svg, g, edgesG, nodesG, connectG, zoomBehavior, data, config;
    let selectedId = null;
    let selectedEdgeId = null;
    let clickTimer = null;
    let connectMode = false;
    let connecting = null;
    let currentTransform = d3.zoomIdentity;
    let minimapSvg, minimapG, minimapViewport;
    let isDraggingNode = false;
    let positionSaveTimer = null;
    let pendingPositions = new Map();

    function containerEl() {
        return document.getElementById('c4-diagram-container');
    }

    function width() {
        return containerEl()?.clientWidth || 800;
    }

    function height() {
        return containerEl()?.clientHeight || 600;
    }

    function snap(value) {
        return Math.round(value / GRID_SIZE) * GRID_SIZE;
    }

    function cloneData(state) {
        return JSON.parse(JSON.stringify(state));
    }

    function getConnectableNodes() {
        return (data?.nodes || []).filter((n) => !n.is_boundary);
    }

    function nodeCenter(node) {
        return {
            x: (node.position?.x || 0) + NODE_WIDTH / 2,
            y: (node.position?.y || 0) + NODE_HEIGHT / 2,
        };
    }

    function portPosition(node, side) {
        const x = node.position?.x || 0;
        const y = node.position?.y || 0;
        if (side === 'right') return { x: x + NODE_WIDTH, y: y + NODE_HEIGHT / 2 };
        if (side === 'bottom') return { x: x + NODE_WIDTH / 2, y: y + NODE_HEIGHT };
        return { x, y: y + NODE_HEIGHT / 2 };
    }

    function edgePath(source, target) {
        const sx = (source.position?.x || 0) + NODE_WIDTH / 2;
        const sy = (source.position?.y || 0) + NODE_HEIGHT;
        const tx = (target.position?.x || 0) + NODE_WIDTH / 2;
        const ty = target.position?.y || 0;
        const midY = (sy + ty) / 2;
        return `M${sx},${sy} C${sx},${midY} ${tx},${midY} ${tx},${ty}`;
    }

    function buildDagreLayout(nodes, edges) {
        const graph = new dagre.graphlib.Graph();
        graph.setGraph({ rankdir: 'TB', nodesep: 60, ranksep: 80, marginx: 40, marginy: 40 });
        graph.setDefaultEdgeLabel(() => ({}));

        nodes.forEach((node) => {
            if (node.is_boundary) return;
            graph.setNode(node.id, { width: NODE_WIDTH, height: NODE_HEIGHT });
        });

        edges.forEach((edge) => {
            if (graph.hasNode(edge.source) && graph.hasNode(edge.target)) {
                graph.setEdge(edge.source, edge.target);
            }
        });

        dagre.layout(graph);

        return nodes.map((node) => {
            if (node.is_boundary) {
                return { ...node, position: node.position || { x: 40, y: 40 } };
            }
            const layoutNode = graph.node(node.id);
            if (!layoutNode) return node;
            return {
                ...node,
                position: {
                    x: snap(layoutNode.x - NODE_WIDTH / 2),
                    y: snap(layoutNode.y - NODE_HEIGHT / 2),
                },
            };
        });
    }

    function diagramBounds() {
        const nodes = getConnectableNodes();
        if (!nodes.length) {
            return { minX: 0, minY: 0, maxX: 400, maxY: 300 };
        }
        const xs = nodes.map((n) => n.position?.x || 0);
        const ys = nodes.map((n) => n.position?.y || 0);
        return {
            minX: Math.min(...xs) - 40,
            minY: Math.min(...ys) - 40,
            maxX: Math.max(...xs) + NODE_WIDTH + 40,
            maxY: Math.max(...ys) + NODE_HEIGHT + 40,
        };
    }

    function updateEdgesOnly() {
        if (!edgesG) return;
        const nodes = data.nodes || [];
        const edges = data.edges || [];

        const edgeSelection = edgesG.selectAll('.c4-edge-group')
            .data(edges, (d) => d.id);

        edgeSelection.exit().remove();

        const enter = edgeSelection.enter()
            .append('g')
            .attr('class', 'c4-edge-group')
            .style('cursor', 'pointer')
            .on('click', (event, d) => {
                event.stopPropagation();
                selectedEdgeId = d.id;
                selectedId = null;
                window.dispatchEvent(new CustomEvent('c4-edge-selected', { detail: d }));
                updateSelectionStyles();
            });

        const merged = enter.merge(edgeSelection);

        merged.selectAll('path.c4-edge').remove();
        merged.selectAll('text.c4-edge-label').remove();

        merged.each(function (edge) {
            const source = nodes.find((n) => n.id === edge.source);
            const target = nodes.find((n) => n.id === edge.target);
            if (!source || !target) return;

            const group = d3.select(this);
            const path = edgePath(source, target);
            const sourceCenter = nodeCenter(source);
            const targetCenter = nodeCenter(target);
            const midX = (sourceCenter.x + targetCenter.x) / 2;
            const midY = (sourceCenter.y + targetCenter.y) / 2;

            group.append('path')
                .attr('class', 'c4-edge' + (edge.id === selectedEdgeId ? ' selected' : ''))
                .attr('d', path)
                .attr('marker-end', 'url(#c4-arrow)');

            if (edge.label || edge.protocol) {
                group.append('text')
                    .attr('class', 'c4-edge-label')
                    .attr('x', midX)
                    .attr('y', midY - 4)
                    .attr('text-anchor', 'middle')
                    .text(edge.label || edge.protocol);
            }
        });
    }

    function updateSelectionStyles() {
        nodesG?.selectAll('.c4-node').classed('selected', (d) => d.id === selectedId);
        edgesG?.selectAll('.c4-edge').classed('selected', function () {
            const group = d3.select(this.parentNode);
            const edgeData = group.datum();
            return edgeData && edgeData.id === selectedEdgeId;
        });
    }

    function renderBoundary() {
        const nodes = data.nodes || [];
        const boundary = nodes.find((n) => n.is_boundary);
        if (!boundary) return;

        const childNodes = nodes.filter((n) => !n.is_boundary);
        const xs = childNodes.map((n) => (n.position?.x || 0));
        const ys = childNodes.map((n) => (n.position?.y || 0));
        const minX = xs.length ? Math.min(...xs) - 40 : 20;
        const minY = ys.length ? Math.min(...ys) - 60 : 20;
        const maxX = xs.length ? Math.max(...xs) + NODE_WIDTH + 40 : 400;
        const maxY = ys.length ? Math.max(...ys) + NODE_HEIGHT + 40 : 300;

        g.selectAll('.c4-boundary-group').remove();

        const boundaryG = g.insert('g', ':first-child').attr('class', 'c4-boundary-group c4-boundary');
        boundaryG.append('rect')
            .attr('x', minX)
            .attr('y', minY)
            .attr('width', maxX - minX)
            .attr('height', maxY - minY)
            .attr('rx', 12);
        boundaryG.append('text')
            .attr('x', minX + 12)
            .attr('y', minY + 20)
            .attr('class', 'c4-node-name')
            .text(boundary.name + ' [system]');
    }

    function renderNodes() {
        const nodes = getConnectableNodes();
        const readOnly = config?.readOnly;

        const drag = d3.drag()
            .filter((event) => !event.target.classList.contains('c4-port'))
            .on('start', function (event, d) {
                if (readOnly || connecting) return;
                isDraggingNode = true;
                d3.select(this).raise();
                event.sourceEvent.stopPropagation();
            })
            .on('drag', function (event, d) {
                if (readOnly || connecting) return;
                const pt = d3.pointer(event, g.node());
                d.position.x = snap(pt[0] - NODE_WIDTH / 2);
                d.position.y = snap(pt[1] - NODE_HEIGHT / 2);
                d3.select(this).attr('transform', `translate(${d.position.x},${d.position.y})`);
                updateEdgesOnly();
                updateMinimap();
            })
            .on('end', function (event, d) {
                if (readOnly || connecting) return;
                isDraggingNode = false;
                pendingPositions.set(d.id, { ...d.position });
                schedulePositionSave();
                window.dispatchEvent(new CustomEvent('c4-node-moved', { detail: d }));
            });

        const nodeSelection = nodesG.selectAll('.c4-node')
            .data(nodes, (d) => d.id);

        nodeSelection.exit().remove();

        const enter = nodeSelection.enter()
            .append('g')
            .attr('class', (d) => 'c4-node' + (d.id === selectedId ? ' selected' : ''))
            .attr('transform', (d) => `translate(${d.position?.x || 0},${d.position?.y || 0})`)
            .style('cursor', 'pointer')
            .call(readOnly ? () => {} : drag)
            .on('click', (event, d) => {
                event.stopPropagation();
                if (clickTimer) {
                    clearTimeout(clickTimer);
                    clickTimer = null;
                    if (d.drill_down) window.location.href = d.drill_down;
                    return;
                }
                clickTimer = setTimeout(() => {
                    clickTimer = null;
                    selectedId = d.id;
                    selectedEdgeId = null;
                    window.dispatchEvent(new CustomEvent('c4-node-selected', { detail: d }));
                    updateSelectionStyles();
                }, 250);
            });

        const merged = enter.merge(nodeSelection);
        merged.attr('transform', (d) => `translate(${d.position?.x || 0},${d.position?.y || 0})`);

        merged.each(function (d) {
            const group = d3.select(this);
            if (group.select('rect.node-body').size()) return;

            const color = d.color || '#3b82f6';
            group.append('rect').attr('class', 'node-body')
                .attr('width', NODE_WIDTH).attr('height', NODE_HEIGHT)
                .attr('rx', 8).attr('fill', '#ffffff').attr('stroke', color);

            group.append('text').attr('class', 'c4-node-type')
                .attr('x', 12).attr('y', 18)
                .text((d.type || '').replace(/_/g, ' '));

            group.append('text').attr('class', 'c4-node-name')
                .attr('x', 12).attr('y', 36)
                .text(truncate(d.name, 22));

            group.append('text').attr('class', 'c4-node-tech')
                .attr('x', 12).attr('y', 54)
                .text(d.technology ? truncate(d.technology, 24) : '');

            if (!readOnly) {
                ['right', 'bottom'].forEach((side) => {
                    const pos = portPosition(d, side);
                    group.append('circle')
                        .attr('class', 'c4-port c4-port-' + side)
                        .attr('cx', pos.x - (d.position?.x || 0))
                        .attr('cy', pos.y - (d.position?.y || 0))
                        .attr('r', PORT_RADIUS)
                        .on('mousedown', (event) => startConnection(event, d, side))
                        .on('mouseup', (event) => endConnection(event, d));
                });
            }
        });

        merged.select('.c4-node-name').text((d) => truncate(d.name, 22));
        merged.select('.c4-node-tech').text((d) => d.technology ? truncate(d.technology, 24) : '');
        merged.select('rect.node-body').attr('stroke', (d) => d.color || '#3b82f6');
    }

    function startConnection(event, node, side) {
        if (config?.readOnly) return;
        event.stopPropagation();
        event.preventDefault();
        connecting = { source: node, side };
        const start = portPosition(node, side);
        connectG.selectAll('*').remove();
        connectG.append('line')
            .attr('class', 'c4-connect-line')
            .attr('x1', start.x).attr('y1', start.y)
            .attr('x2', start.x).attr('y2', start.y);

        const onMove = (e) => {
            const pt = pointerInGraph(e);
            connectG.select('line')
                .attr('x2', pt.x).attr('y2', pt.y);
        };
        const onUp = () => {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            if (connecting) {
                connectG.selectAll('*').remove();
                connecting = null;
            }
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    }

    function endConnection(event, targetNode) {
        if (!connecting || config?.readOnly) return;
        event.stopPropagation();
        const source = connecting.source;
        connecting = null;
        connectG.selectAll('*').remove();

        if (source.id === targetNode.id) return;

        window.dispatchEvent(new CustomEvent('c4-connection-requested', {
            detail: { source, target: targetNode },
        }));
    }

    function pointerInGraph(event) {
        const pt = d3.pointer(event, g.node());
        return { x: pt[0], y: pt[1] };
    }

    function render() {
        if (!data || !g) return;
        renderBoundary();
        updateEdgesOnly();
        renderNodes();
        updateMinimap();
    }

    function truncate(str, len) {
        if (!str) return '';
        return str.length > len ? str.slice(0, len - 1) + '…' : str;
    }

    function initMinimap() {
        const el = document.getElementById('c4-minimap');
        if (!el) return;

        el.innerHTML = '';
        minimapSvg = d3.select(el).append('svg')
            .attr('width', MINIMAP_W)
            .attr('height', MINIMAP_H);

        minimapG = minimapSvg.append('g');
        minimapViewport = minimapSvg.append('rect')
            .attr('class', 'c4-minimap-viewport');

        minimapSvg
            .style('cursor', 'crosshair')
            .on('mousedown', minimapMouseDown)
            .on('mousemove', minimapMouseMove)
            .on('mouseup', minimapMouseUp)
            .on('mouseleave', () => { minimapDragging = false; });
    }

    let minimapDragging = false;

    function minimapScale() {
        const bounds = diagramBounds();
        const bw = bounds.maxX - bounds.minX || 400;
        const bh = bounds.maxY - bounds.minY || 300;
        const scale = Math.min(
            (MINIMAP_W - MINIMAP_PAD * 2) / bw,
            (MINIMAP_H - MINIMAP_PAD * 2) / bh,
        );
        return { scale, bounds };
    }

    function graphToMinimap(x, y) {
        const { scale, bounds } = minimapScale();
        return {
            x: MINIMAP_PAD + (x - bounds.minX) * scale,
            y: MINIMAP_PAD + (y - bounds.minY) * scale,
        };
    }

    function minimapToGraph(mx, my) {
        const { scale, bounds } = minimapScale();
        return {
            x: bounds.minX + (mx - MINIMAP_PAD) / scale,
            y: bounds.minY + (my - MINIMAP_PAD) / scale,
        };
    }

    function updateMinimap() {
        if (!minimapG) return;

        const { scale, bounds } = minimapScale();
        const nodes = getConnectableNodes();

        minimapG.selectAll('*').remove();

        minimapG.append('rect')
            .attr('x', MINIMAP_PAD)
            .attr('y', MINIMAP_PAD)
            .attr('width', (bounds.maxX - bounds.minX) * scale)
            .attr('height', (bounds.maxY - bounds.minY) * scale)
            .attr('fill', '#f1f5f9')
            .attr('stroke', '#e2e8f0');

        nodes.forEach((node) => {
            const p = graphToMinimap(node.position?.x || 0, node.position?.y || 0);
            minimapG.append('rect')
                .attr('x', p.x)
                .attr('y', p.y)
                .attr('width', NODE_WIDTH * scale)
                .attr('height', NODE_HEIGHT * scale)
                .attr('rx', 2)
                .attr('fill', node.color || '#3b82f6')
                .attr('opacity', 0.85);
        });

        const vw = width() / currentTransform.k;
        const vh = height() / currentTransform.k;
        const vx = -currentTransform.x / currentTransform.k;
        const vy = -currentTransform.y / currentTransform.k;

        const vp = graphToMinimap(vx, vy);
        minimapViewport
            .attr('x', vp.x)
            .attr('y', vp.y)
            .attr('width', Math.max(vw * scale, 8))
            .attr('height', Math.max(vh * scale, 6));
    }

    function panToGraphPoint(gx, gy) {
        const newX = width() / 2 - gx * currentTransform.k;
        const newY = height() / 2 - gy * currentTransform.k;
        const t = d3.zoomIdentity.translate(newX, newY).scale(currentTransform.k);
        svg.transition().duration(200).call(zoomBehavior.transform, t);
    }

    function minimapMouseDown(event) {
        minimapDragging = true;
        const [mx, my] = d3.pointer(event);
        const gp = minimapToGraph(mx, my);
        panToGraphPoint(gp.x, gp.y);
    }

    function minimapMouseMove(event) {
        if (!minimapDragging) return;
        const [mx, my] = d3.pointer(event);
        const gp = minimapToGraph(mx, my);
        panToGraphPoint(gp.x, gp.y);
    }

    function minimapMouseUp() {
        minimapDragging = false;
    }

    function initSvg() {
        d3.select('#c4-diagram-svg').selectAll('*').remove();

        svg = d3.select('#c4-diagram-svg')
            .attr('viewBox', [0, 0, width(), height()]);

        g = svg.append('g');

        const defs = g.append('defs');
        defs.append('marker')
            .attr('id', 'c4-arrow')
            .attr('viewBox', '0 -5 10 10')
            .attr('refX', 18)
            .attr('refY', 0)
            .attr('markerWidth', 6)
            .attr('markerHeight', 6)
            .attr('orient', 'auto')
            .append('path')
            .attr('d', 'M0,-5L10,0L0,5')
            .attr('fill', '#94a3b8');

        edgesG = g.append('g').attr('class', 'c4-edges');
        nodesG = g.append('g').attr('class', 'c4-nodes');
        connectG = g.append('g').attr('class', 'c4-connect-overlay');

        zoomBehavior = d3.zoom()
            .scaleExtent([0.2, 3])
            .on('zoom', (event) => {
                currentTransform = event.transform;
                g.attr('transform', event.transform);
                updateMinimap();
            });

        svg.call(zoomBehavior)
            .on('click', () => {
                selectedId = null;
                selectedEdgeId = null;
                window.dispatchEvent(new CustomEvent('c4-node-selected', { detail: null }));
                window.dispatchEvent(new CustomEvent('c4-edge-selected', { detail: null }));
                updateSelectionStyles();
            });
    }

    function schedulePositionSave() {
        clearTimeout(positionSaveTimer);
        positionSaveTimer = setTimeout(() => {
            const batch = new Map(pendingPositions);
            pendingPositions.clear();
            window.dispatchEvent(new CustomEvent('c4-positions-batch', { detail: batch }));
        }, 400);
    }

    function autoLayout() {
        data.nodes = buildDagreLayout(data.nodes || [], data.edges || []);
        render();
        window.dispatchEvent(new CustomEvent('c4-layout-applied', { detail: data.nodes }));
        window.dispatchEvent(new CustomEvent('c4-history-push', { detail: cloneData(data) }));
    }

    function exportSvg() {
        const svgNode = document.getElementById('c4-diagram-svg');
        const serializer = new XMLSerializer();
        const source = serializer.serializeToString(svgNode);
        const blob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'c4-diagram.svg';
        a.click();
        URL.revokeObjectURL(url);
    }

    function exportPng() {
        const svgNode = document.getElementById('c4-diagram-svg');
        const serializer = new XMLSerializer();
        const source = serializer.serializeToString(svgNode);
        const img = new Image();
        const blob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        img.onload = function () {
            const canvas = document.createElement('canvas');
            canvas.width = width() * 2;
            canvas.height = height() * 2;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#f8fafc';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            canvas.toBlob((pngBlob) => {
                const pngUrl = URL.createObjectURL(pngBlob);
                const a = document.createElement('a');
                a.href = pngUrl;
                a.download = 'c4-diagram.png';
                a.click();
                URL.revokeObjectURL(pngUrl);
            });
            URL.revokeObjectURL(url);
        };
        img.src = url;
    }

    function addEdge(edge) {
        data.edges = data.edges || [];
        const exists = data.edges.some((e) =>
            e.source === edge.source && e.target === edge.target && e.id !== edge.id,
        );
        if (!exists) {
            data.edges.push(edge);
            render();
        }
    }

    function removeEdge(edgeId) {
        data.edges = (data.edges || []).filter((e) => e.id !== edgeId);
        if (selectedEdgeId === edgeId) selectedEdgeId = null;
        render();
    }

    function updateEdge(edgeId, updates) {
        const edge = (data.edges || []).find((e) => e.id === edgeId);
        if (edge) Object.assign(edge, updates);
        render();
    }

    window.c4DiagramRenderer = {
        init(diagramData, diagramConfig) {
            data = diagramData;
            config = diagramConfig;
            initSvg();
            initMinimap();
            data.nodes = buildDagreLayout(data.nodes || [], data.edges || []);
            render();
        },
        autoLayout,
        exportSvg,
        exportPng,
        zoomIn() {
            svg.transition().duration(300).call(zoomBehavior.scaleBy, 1.2);
        },
        zoomOut() {
            svg.transition().duration(300).call(zoomBehavior.scaleBy, 0.8);
        },
        resetZoom() {
            svg.transition().duration(300).call(zoomBehavior.transform, d3.zoomIdentity);
        },
        getData() {
            return data;
        },
        setData(newData, skipRender) {
            data = newData;
            if (!skipRender) render();
        },
        highlightNode(id) {
            selectedId = id;
            selectedEdgeId = null;
            updateSelectionStyles();
        },
        setConnectMode(enabled) {
            connectMode = enabled;
            nodesG?.selectAll('.c4-port').style('opacity', enabled ? 1 : null);
        },
        addEdge,
        removeEdge,
        updateEdge,
        refresh() { render(); },
    };

    window.c4DiagramApp = function () {
        return {
            selectedNode: null,
            selectedEdge: null,
            editForm: {},
            edgeForm: { protocol: 'REST', description: '', sync: true },
            searchQuery: '',
            paletteOpen: true,
            panelOpen: true,
            connectMode: false,
            readOnly: window.c4DiagramConfig?.readOnly || false,
            history: [],
            historyIndex: -1,
            isRestoringHistory: false,

            get canUndo() {
                return this.historyIndex > 0;
            },

            get canRedo() {
                return this.historyIndex < this.history.length - 1;
            },

            init() {
                config = window.c4DiagramConfig;
                window.c4DiagramRenderer.init(window.c4DiagramData, config);
                this.pushHistory(window.c4DiagramData);

                window.addEventListener('c4-node-selected', (e) => {
                    this.selectedNode = e.detail;
                    this.selectedEdge = null;
                    if (e.detail) {
                        this.editForm = {
                            name: e.detail.name || '',
                            description: e.detail.description || '',
                            technology: e.detail.technology || '',
                        };
                    }
                });

                window.addEventListener('c4-edge-selected', (e) => {
                    this.selectedEdge = e.detail;
                    if (e.detail) {
                        this.selectedNode = null;
                        this.edgeForm = {
                            protocol: e.detail.protocol || 'REST',
                            description: e.detail.description || '',
                            sync: e.detail.sync !== false,
                        };
                    }
                });

                window.addEventListener('c4-node-moved', () => {
                    if (!this.isRestoringHistory) {
                        this.pushHistory(window.c4DiagramRenderer.getData());
                    }
                });

                window.addEventListener('c4-layout-applied', (e) => {
                    e.detail.forEach((node) => this.savePosition(node, true));
                    if (!this.isRestoringHistory) {
                        this.pushHistory(window.c4DiagramRenderer.getData());
                    }
                });

                window.addEventListener('c4-history-push', (e) => {
                    if (!this.isRestoringHistory) {
                        this.pushHistory(e.detail);
                    }
                });

                window.addEventListener('c4-positions-batch', (e) => {
                    e.detail.forEach((position, nodeId) => {
                        const data = window.c4DiagramRenderer.getData();
                        const node = data.nodes.find((n) => n.id === nodeId);
                        if (node) this.savePosition({ ...node, position }, true);
                    });
                });

                window.addEventListener('c4-connection-requested', (e) => {
                    this.createConnection(e.detail.source, e.detail.target);
                });

                document.addEventListener('keydown', (e) => {
                    if (e.target.matches('input, textarea, select')) return;
                    if (e.ctrlKey || e.metaKey) {
                        if (e.key === 'z' && !e.shiftKey) {
                            e.preventDefault();
                            this.undo();
                        } else if (e.key === 'y' || (e.key === 'z' && e.shiftKey)) {
                            e.preventDefault();
                            this.redo();
                        }
                    }
                });
            },

            pushHistory(state) {
                const snapshot = cloneData(state);
                const last = this.history[this.historyIndex];
                if (last && JSON.stringify(last) === JSON.stringify(snapshot)) return;

                this.history = this.history.slice(0, this.historyIndex + 1);
                this.history.push(snapshot);
                if (this.history.length > 50) {
                    this.history.shift();
                }
                this.historyIndex = this.history.length - 1;
            },

            undo() {
                if (!this.canUndo) return;
                this.isRestoringHistory = true;
                this.historyIndex--;
                const state = cloneData(this.history[this.historyIndex]);
                window.c4DiagramRenderer.setData(state);
                this.isRestoringHistory = false;
            },

            redo() {
                if (!this.canRedo) return;
                this.isRestoringHistory = true;
                this.historyIndex++;
                const state = cloneData(this.history[this.historyIndex]);
                window.c4DiagramRenderer.setData(state);
                this.isRestoringHistory = false;
            },

            toggleConnectMode() {
                this.connectMode = !this.connectMode;
                window.c4DiagramRenderer.setConnectMode(this.connectMode);
            },

            zoomIn() { window.c4DiagramRenderer.zoomIn(); },
            zoomOut() { window.c4DiagramRenderer.zoomOut(); },
            resetZoom() { window.c4DiagramRenderer.resetZoom(); },
            autoLayout() { window.c4DiagramRenderer.autoLayout(); },
            exportSvg() { window.c4DiagramRenderer.exportSvg(); },
            exportPng() { window.c4DiagramRenderer.exportPng(); },

            async runSearch() {
                if (!config.routes.search || !this.searchQuery) return;
                const res = await fetch(`${config.routes.search}?q=${encodeURIComponent(this.searchQuery)}`);
                const json = await res.json();
                if (json.results?.length) {
                    window.c4DiagramRenderer.highlightNode(json.results[0].id);
                }
            },

            paletteDrag(event, kind, type) {
                event.dataTransfer.setData('application/c4-element', JSON.stringify({ kind, type }));
            },

            async canvasDrop(event) {
                if (this.readOnly) return;
                const raw = event.dataTransfer.getData('application/c4-element');
                if (!raw) return;
                const { kind, type } = JSON.parse(raw);
                const name = prompt('Element name:', kind === 'container' ? 'New Container' : 'New Component');
                if (!name) return;

                const rect = containerEl().getBoundingClientRect();
                const position = {
                    x: snap(event.clientX - rect.left - NODE_WIDTH / 2),
                    y: snap(event.clientY - rect.top - NODE_HEIGHT / 2),
                };

                if (kind === 'container') {
                    await this.apiPost(config.routes.containerStore, {
                        name, type, position,
                        commit_message: `Added ${name} via palette`,
                    });
                } else if (config.routes.componentStore) {
                    await this.apiPost(config.routes.componentStore, {
                        name, type, position,
                        commit_message: `Added ${name} via palette`,
                    });
                }
                window.location.reload();
            },

            elementTypeForNode(node) {
                const map = {
                    context: 'context',
                    external_system: 'external_system',
                    user: 'user',
                    container: 'container',
                    component: 'component',
                    system: 'system',
                };
                return map[node.type] || 'container';
            },

            async createConnection(source, target) {
                if (this.readOnly) return;

                const protocols = config.protocols || ['REST', 'HTTP'];
                const protocol = prompt('Protocol (' + protocols.join(', ') + '):', 'REST');
                if (!protocol) return;

                const payload = {
                    source_id: source.id,
                    target_id: target.id,
                    source_type: this.elementTypeForNode(source),
                    target_type: this.elementTypeForNode(target),
                    protocol: protocol.toUpperCase(),
                    sync: true,
                    system_id: config.systemId,
                    commit_message: `Connected ${source.name} → ${target.name}`,
                };

                const res = await this.apiPost(config.routes.relationshipStore, payload);
                if (!res.ok) return;

                const created = await res.json();
                const rel = created.data || created;
                const edge = {
                    id: rel.id || 'edge-' + Date.now(),
                    source: source.id,
                    target: target.id,
                    protocol: payload.protocol,
                    label: payload.protocol,
                    sync: true,
                    description: null,
                };

                window.c4DiagramRenderer.addEdge(edge);
                this.pushHistory(window.c4DiagramRenderer.getData());
            },

            canDelete() {
                return this.selectedNode &&
                    !this.selectedNode.is_boundary &&
                    ['container', 'component'].includes(this.selectedNode.type);
            },

            async saveProperties() {
                if (!this.selectedNode || this.readOnly) return;
                const node = this.selectedNode;

                if (node.type === 'container') {
                    await this.apiRequest(`${config.routes.containerUpdate}/${node.id}`, 'PUT', {
                        ...this.editForm,
                        commit_message: `Updated ${this.editForm.name}`,
                    });
                } else if (node.type === 'component') {
                    await this.apiRequest(`${config.routes.componentUpdate}/${node.id}`, 'PUT', {
                        ...this.editForm,
                        commit_message: `Updated ${this.editForm.name}`,
                    });
                } else if (config.level === 'context') {
                    await this.apiRequest(config.routes.contextUpdate, 'PUT', {
                        name: this.editForm.name,
                        description: this.editForm.description,
                        commit_message: 'Updated context',
                    });
                } else {
                    return;
                }
                window.location.reload();
            },

            async saveEdge() {
                if (!this.selectedEdge || this.readOnly) return;
                const edgeId = this.selectedEdge.id;
                if (edgeId.startsWith('dep-')) {
                    alert('Dependency edges are read-only. Edit component dependencies instead.');
                    return;
                }

                await this.apiRequest(`${config.routes.relationshipUpdate}/${edgeId}`, 'PUT', {
                    ...this.edgeForm,
                    system_id: config.systemId,
                });

                window.c4DiagramRenderer.updateEdge(edgeId, {
                    protocol: this.edgeForm.protocol,
                    label: this.edgeForm.protocol,
                    description: this.edgeForm.description,
                    sync: this.edgeForm.sync,
                });
                this.pushHistory(window.c4DiagramRenderer.getData());
            },

            async deleteEdge() {
                if (!this.selectedEdge || this.readOnly) return;
                const edgeId = this.selectedEdge.id;
                if (edgeId.startsWith('dep-')) return;
                if (!confirm('Delete this connection?')) return;

                await this.apiRequest(`${config.routes.relationshipUpdate}/${edgeId}`, 'DELETE');
                window.c4DiagramRenderer.removeEdge(edgeId);
                this.selectedEdge = null;
                this.pushHistory(window.c4DiagramRenderer.getData());
            },

            async deleteSelected() {
                if (!this.canDelete() || !confirm('Delete this element?')) return;
                const node = this.selectedNode;
                const base = node.type === 'container' ? config.routes.containerUpdate : config.routes.componentUpdate;
                await this.apiRequest(`${base}/${node.id}`, 'DELETE');
                window.location.reload();
            },

            async savePosition(node, silent) {
                if (this.readOnly || !node.position) return;
                const payload = { position: node.position };
                if (node.type === 'container') {
                    await this.apiRequest(`${config.routes.containerUpdate}/${node.id}`, 'PUT', payload);
                } else if (node.type === 'component') {
                    await this.apiRequest(`${config.routes.componentUpdate}/${node.id}`, 'PUT', payload);
                }
            },

            async apiPost(url, body) {
                return this.apiRequest(url, 'POST', body);
            },

            async apiRequest(url, method, body) {
                const fetchFn = window.LaravelCsrf?.fetch || fetch;
                const res = await fetchFn(url, {
                    method,
                    body: body || undefined,
                });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    const msg = err.message || (res.status === 419 ? 'Session expired — please refresh the page.' : 'Request failed');
                    alert(msg);
                }
                return res;
            },
        };
    };

    window.addEventListener('resize', () => {
        if (window.c4DiagramRenderer && data) {
            initSvg();
            initMinimap();
            render();
        }
    });
})();
