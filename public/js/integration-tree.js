(function () {
    'use strict';

    const data = window.integrationTreeData;
    if (!data) return;

    const container = document.getElementById('integration-tree-container');
    const svgEl = document.getElementById('integration-tree-svg');
    let layoutDirection = 'vertical';
    let root, svg, g, zoomBehavior;
    let i = 0;

    const width = () => container.clientWidth;
    const height = () => container.clientHeight;

    function init() {
        d3.select('#integration-tree-svg').selectAll('*').remove();

        svg = d3.select('#integration-tree-svg')
            .attr('viewBox', [0, 0, width(), height()]);

        g = svg.append('g');

        zoomBehavior = d3.zoom()
            .scaleExtent([0.2, 3])
            .on('zoom', (event) => {
                g.attr('transform', event.transform);
            });

        svg.call(zoomBehavior);

        root = d3.hierarchy(data);
        root.x0 = height() / 2;
        root.y0 = 0;

        // Expand systems so APIs are visible by default
        expandFromRoot(root);

        update(root);
        bindControls();
    }

    function expandFromRoot(d) {
        if (d._children) {
            d.children = d._children;
            d._children = null;
        }
        if (d.children) {
            d.children.forEach(function (child) {
                if (child.data.type === 'vendor' || child.data.type === 'system' || child.data.type === 'api' || d.data.type === 'root') {
                    expandFromRoot(child);
                }
            });
        }
    }

    function collapse(d) {
        if (d.children) {
            d._children = d.children;
            d._children.forEach(collapse);
            d.children = null;
        }
    }

    function expand(d) {
        if (d._children) {
            d.children = d._children;
            d._children = null;
        }
        if (d.children) {
            d.children.forEach(expand);
        }
    }

    function getNodeClass(d) {
        const type = d.data.type || 'system';
        if (type === 'root') return 'root-node';
        if (type === 'vendor') return 'vendor-node';
        if (type === 'api') return 'api-' + (d.data.api_type || 'rest');
        if (type === 'integration') return 'integration-node';
        return 'system-node';
    }

    const API_TYPE_LABELS = {
        rest: 'REST',
        graphql: 'GraphQL',
        grpc: 'gRPC',
        websocket: 'WS',
        sse: 'SSE',
        socketio: 'S.IO',
        soap: 'SOAP',
    };

    function apiTypeLabel(apiType) {
        return API_TYPE_LABELS[apiType] || (apiType || 'REST').toUpperCase();
    }

    function nodeClick(event, d) {
        if ((d.data.type === 'api' || d.data.type === 'integration') && d.data.url) {
            window.location.href = d.data.url;
            return;
        }
        if (d.children) {
            d._children = d.children;
            d.children = null;
        } else if (d._children) {
            d.children = d._children;
            d._children = null;
        }
        update(d);
    }

    function formatSystemLabel(d) {
        const count = d.data.api_count || 0;
        const suffix = count ? ` (${count} API${count !== 1 ? 's' : ''})` : '';
        return (d.data.name || '') + suffix;
    }

    function formatNodeLabel(d) {
        if (d.data.type === 'vendor') {
            const count = d.data.system_count || 0;
            const suffix = count ? ` (${count} system${count !== 1 ? 's' : ''})` : '';
            return (d.data.name || '') + suffix;
        }
        if (d.data.type === 'system') {
            return formatSystemLabel(d);
        }
        if (d.data.type === 'integration') {
            const vendor = d.data.vendor_name ? ` · ${d.data.vendor_name}` : '';
            return `↔ ${d.data.name || ''}${vendor}`;
        }
        return d.data.name || '';
    }

    function buildApiCardHtml(d) {
        const apiType = d.data.api_type || 'rest';
        const type = apiTypeLabel(apiType);
        const cardClass = 'tree-api-card-' + apiType;
        const typeClass = 'tree-api-card-type-' + apiType;
        const tpsText = d.data.tps_value != null
            ? `${Math.round(d.data.tps_value).toLocaleString()} TPS`
            : 'N/A';
        const tpsClass = d.data.tps_value != null ? 'tree-api-card-tps' : 'tree-api-card-tps tree-api-card-tps-na';
        const name = escapeHtml(d.data.name || '');

        return `<div xmlns="http://www.w3.org/1999/xhtml" class="tree-api-card ${cardClass}" title="${name}">
            <div class="tree-api-card-body">
                <span class="tree-api-card-type ${typeClass}">${type}</span>
                <span class="tree-api-card-name">${name}</span>
                <span class="${tpsClass}">${tpsText}</span>
            </div>
        </div>`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function update(source) {
        const duration = 300;
        const isHorizontal = layoutDirection === 'horizontal';
        const defaultNodeSize = isHorizontal ? [58, 280] : [64, 280];

        const treeLayout = d3.tree().nodeSize(defaultNodeSize);
        treeLayout(root);

        const nodes = root.descendants();
        const links = root.links();

        if (isHorizontal) {
            nodes.forEach(d => { d.y = d.depth * 280; });
        }

        const linkGen = d3.linkHorizontal()
            .x(d => isHorizontal ? d.y : d.x)
            .y(d => isHorizontal ? d.x : d.y);

        const link = g.selectAll('.tree-link')
            .data(links, d => d.target.id || (d.target.id = ++i));

        link.enter().append('path')
            .attr('class', 'tree-link')
            .attr('fill', 'none')
            .attr('stroke', '#64748b')
            .attr('stroke-width', 2)
            .attr('d', d => {
                const o = { x: source.x0, y: source.y0 };
                return linkGen({ source: o, target: o });
            })
            .merge(link)
            .attr('stroke', '#64748b')
            .attr('fill', 'none')
            .transition().duration(duration)
            .attr('d', linkGen);

        link.exit().transition().duration(duration)
            .attr('d', d => {
                const o = { x: source.x, y: source.y };
                return linkGen({ source: o, target: o });
            })
            .remove();

        const node = g.selectAll('.tree-node')
            .data(nodes, d => d.id || (d.id = ++i));

        const nodeEnter = node.enter().append('g')
            .attr('class', d => 'tree-node ' + getNodeClass(d))
            .attr('transform', d => {
                const x = isHorizontal ? source.y0 : source.x0;
                const y = isHorizontal ? source.x0 : source.y0;
                return `translate(${x},${y})`;
            })
            .on('click', nodeClick);

        // System / root nodes: circle + text label
        nodeEnter.filter(d => d.data.type !== 'api')
            .append('circle')
            .attr('r', d => d.data.type === 'root' ? 8 : 10)
            .style('cursor', 'pointer');

        nodeEnter.filter(d => d.data.type !== 'api')
            .append('text')
            .attr('class', 'node-label system-label')
            .attr('dy', '0.31em')
            .attr('x', d => d.children || d._children ? -14 : 14)
            .attr('text-anchor', d => d.children || d._children ? 'end' : 'start')
            .text(d => formatNodeLabel(d));

        // API nodes: card with name + type + TPS
        nodeEnter.filter(d => d.data.type === 'api')
            .append('foreignObject')
            .attr('class', 'tree-api-card-foreign')
            .attr('x', -140)
            .attr('y', -18)
            .attr('width', 280)
            .attr('height', 36)
            .style('cursor', 'pointer')
            .style('overflow', 'visible')
            .html(d => buildApiCardHtml(d));

        const tooltip = d3.select('body').selectAll('.tree-tooltip').data([0])
            .enter().append('div').attr('class', 'tree-tooltip').style('opacity', 0);

        nodeEnter
            .on('mouseover', function (event, d) {
                let html = `<strong>${escapeHtml(d.data.name || '')}</strong>`;
                if (d.data.type === 'api') {
                    if (d.data.endpoint_url) html += `<br><small>${escapeHtml(d.data.endpoint_url)}</small>`;
                    if (d.data.description) html += `<br>${escapeHtml(d.data.description)}`;
                    if (d.data.integrated_systems && d.data.integrated_systems.length) {
                        html += '<br><small>Integrates with: ' +
                            d.data.integrated_systems.map(s => escapeHtml(s.name)).join(', ') +
                            '</small>';
                    }
                } else if (d.data.type === 'integration') {
                    if (d.data.vendor_name) html += `<br><small>Vendor: ${escapeHtml(d.data.vendor_name)}</small>`;
                } else if (d.data.description) {
                    html += `<br>${escapeHtml(d.data.description)}`;
                }
                tooltip.transition().duration(200).style('opacity', 1);
                tooltip.html(html)
                    .style('left', (event.pageX + 12) + 'px')
                    .style('top', (event.pageY - 28) + 'px');
            })
            .on('mouseout', () => tooltip.transition().duration(300).style('opacity', 0));

        const nodeUpdate = nodeEnter.merge(node);

        nodeUpdate.transition().duration(duration)
            .attr('transform', d => {
                const x = isHorizontal ? d.y : d.x;
                const y = isHorizontal ? d.x : d.y;
                return `translate(${x},${y})`;
            });

        nodeUpdate.filter(d => d.data.type !== 'api')
            .select('.system-label')
            .text(d => formatNodeLabel(d));

        nodeUpdate.filter(d => d.data.type === 'api')
            .select('.tree-api-card-foreign')
            .html(d => buildApiCardHtml(d));

        node.exit().transition().duration(duration)
            .attr('transform', d => {
                const x = isHorizontal ? source.y : source.x;
                const y = isHorizontal ? source.x : source.y;
                return `translate(${x},${y})`;
            })
            .remove();

        nodes.forEach(d => {
            d.x0 = d.x;
            d.y0 = d.y;
        });
    }

    function bindControls() {
        document.getElementById('btn-expand-all')?.addEventListener('click', () => {
            root.each(expand);
            update(root);
        });

        document.getElementById('btn-collapse-all')?.addEventListener('click', () => {
            if (root.children) root.children.forEach(collapse);
            update(root);
        });

        document.getElementById('btn-zoom-in')?.addEventListener('click', () => {
            svg.transition().duration(300).call(zoomBehavior.scaleBy, 1.3);
        });

        document.getElementById('btn-zoom-out')?.addEventListener('click', () => {
            svg.transition().duration(300).call(zoomBehavior.scaleBy, 0.7);
        });

        document.getElementById('btn-reset-zoom')?.addEventListener('click', () => {
            svg.transition().duration(500).call(zoomBehavior.transform, d3.zoomIdentity);
        });

        document.getElementById('layout-vertical')?.addEventListener('click', function () {
            layoutDirection = 'vertical';
            this.classList.add('active');
            document.getElementById('layout-horizontal')?.classList.remove('active');
            update(root);
        });

        document.getElementById('layout-horizontal')?.addEventListener('click', function () {
            layoutDirection = 'horizontal';
            this.classList.add('active');
            document.getElementById('layout-vertical')?.classList.remove('active');
            update(root);
        });

        document.getElementById('tree-search')?.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            g.selectAll('.tree-node').classed('dimmed', false).classed('highlighted', false);
            if (!term) return;
            g.selectAll('.tree-node').each(function (d) {
                const nameMatch = (d.data.name || '').toLowerCase().includes(term);
                const tpsMatch = d.data.tps_value != null && String(Math.round(d.data.tps_value)).includes(term);
                const match = nameMatch || tpsMatch;
                d3.select(this).classed('dimmed', !match).classed('highlighted', match);
            });
        });

        document.getElementById('btn-export-svg')?.addEventListener('click', exportSvg);
        document.getElementById('btn-export-png')?.addEventListener('click', exportPng);

        window.addEventListener('resize', () => {
            svg.attr('viewBox', [0, 0, width(), height()]);
        });
    }

    function exportSvg() {
        const serializer = new XMLSerializer();
        const clone = svgEl.cloneNode(true);
        clone.setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        const source = serializer.serializeToString(clone);
        downloadBlob(new Blob([source], { type: 'image/svg+xml;charset=utf-8' }), 'integration-tree.svg');
    }

    function exportPng() {
        const serializer = new XMLSerializer();
        const clone = svgEl.cloneNode(true);
        clone.setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        const svgData = serializer.serializeToString(clone);
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        canvas.width = width() * 2;
        canvas.height = height() * 2;
        img.onload = function () {
            ctx.fillStyle = '#fafbfc';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(blob => downloadBlob(blob, 'integration-tree.png'));
        };
        img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
    }

    function downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    }

    document.addEventListener('DOMContentLoaded', init);
})();
