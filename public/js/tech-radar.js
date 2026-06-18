(function () {
    'use strict';

    const data = window.techRadarData;
    if (!data || !window.d3) return;

    const container = document.getElementById('tech-radar-container');
    const minChartSize = 280;

    const chartSize = () => {
        const width = container?.getBoundingClientRect().width || container?.clientWidth || 400;
        return Math.max(minChartSize, Math.floor(width));
    };

    const labelPad = () => Math.max(32, Math.round(chartSize() * 0.075));
    const cx = () => chartSize() / 2 + labelPad();
    const cy = () => chartSize() / 2 + labelPad();
    const maxR = () => chartSize() / 2 - labelPad();
    const viewBoxSize = () => chartSize() + labelPad() * 2;

    const ringColors = {
        adopt: '#22c55e',
        trial: '#3b82f6',
        assess: '#f59e0b',
        hold: '#ef4444',
    };

    let selectedBlipId = null;
    let svg;
    let zoomRoot;
    let zoomBehavior;
    let currentTransform = d3.zoomIdentity;

    const techSelect = document.getElementById('radar-tech-select');
    const ringSelect = document.getElementById('radar-ring');
    const notesField = document.getElementById('radar-notes');
    const form = document.getElementById('radar-update-form');
    const selectedLabel = document.getElementById('radar-selected-label');

    function applyFormForTechnology(id) {
        if (!techSelect || !id) return;
        const opt = techSelect.querySelector(`option[value="${id}"]`);
        if (!opt) return;

        techSelect.value = String(id);
        if (form && window.techRadarUpdateUrl) {
            form.action = `${window.techRadarUpdateUrl}/${id}`;
        }
        if (ringSelect) ringSelect.value = opt.dataset.ring || 'assess';
        if (notesField) notesField.value = opt.dataset.notes || '';
        if (selectedLabel) {
            selectedLabel.textContent = opt.textContent.trim();
            selectedLabel.classList.remove('d-none');
        }
    }

    function selectBlip(id) {
        selectedBlipId = String(id);
        applyFormForTechnology(id);
        renderChart();
        document.getElementById('radar-position-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function initSvg() {
        svg = d3.select('#tech-radar-svg');
        svg.selectAll('*').remove();

        zoomRoot = svg.append('g').attr('class', 'radar-zoom-root');

        zoomBehavior = d3.zoom()
            .scaleExtent([0.5, 4])
            .filter((event) => {
                if (event.type === 'wheel') return true;
                if (event.target.closest && event.target.closest('.radar-blip-group')) return false;
                return event.button === 0 || event.button === 1;
            })
            .on('zoom', (event) => {
                currentTransform = event.transform;
                zoomRoot.attr('transform', currentTransform);
            });

        svg.call(zoomBehavior);
    }

    function renderChart() {
        if (!svg) initSvg();

        const vb = viewBoxSize();
        const size = chartSize();
        const pad = labelPad();
        const blipR = Math.max(5, size * 0.014);
        const blipRSelected = blipR + 2;
        const labelFontSize = Math.max(9, size * 0.019);
        const ringFontSize = Math.max(10, size * 0.021);
        const quadrantFontSize = Math.max(9, size * 0.019);

        svg
            .attr('viewBox', [0, 0, vb, vb])
            .attr('width', '100%')
            .attr('height', '100%')
            .attr('preserveAspectRatio', 'xMidYMid meet');

        zoomRoot.selectAll('*').remove();

        const g = zoomRoot.append('g').attr('class', 'radar-chart-root')
            .attr('transform', `translate(${cx()},${cy()})`);

        const rings = data.rings || ['adopt', 'trial', 'assess', 'hold'];
        const radii = [0.25, 0.45, 0.65, 0.85];

        rings.forEach((ring, i) => {
            g.append('circle')
                .attr('r', maxR() * radii[i])
                .attr('fill', 'none')
                .attr('stroke', '#e2e8f0')
                .attr('stroke-width', 1);
            g.append('text')
                .attr('class', 'radar-ring-label')
                .attr('x', 4)
                .attr('y', -maxR() * radii[i] + ringFontSize)
                .attr('font-size', ringFontSize)
                .text(data.ring_labels?.[ring] || ring);
        });

        const quadrants = data.quadrants || [];
        const sector = (2 * Math.PI) / Math.max(quadrants.length, 1);
        quadrants.forEach((label, i) => {
            const angle = i * sector - Math.PI / 2 + sector / 2;
            g.append('line')
                .attr('x1', 0).attr('y1', 0)
                .attr('x2', Math.cos(angle) * maxR())
                .attr('y2', Math.sin(angle) * maxR())
                .attr('stroke', '#e2e8f0');
            g.append('text')
                .attr('class', 'radar-quadrant-label')
                .attr('x', Math.cos(angle) * (maxR() + pad * 0.55))
                .attr('y', Math.sin(angle) * (maxR() + pad * 0.55))
                .attr('font-size', quadrantFontSize)
                .attr('text-anchor', 'middle')
                .attr('dominant-baseline', 'middle')
                .text(label.length > 14 ? label.slice(0, 12) + '…' : label);
        });

        (data.blips || []).forEach((blip) => {
            const r = maxR() * (blip.radius || 0.65);
            const x = Math.cos(blip.angle) * r;
            const y = Math.sin(blip.angle) * r;
            const isSelected = selectedBlipId === String(blip.id);

            const blipG = g.append('g')
                .attr('class', 'radar-blip-group')
                .attr('data-id', blip.id)
                .style('cursor', 'pointer')
                .on('click', (event) => {
                    event.stopPropagation();
                    selectBlip(blip.id);
                });

            blipG.append('circle')
                .attr('class', `radar-blip${isSelected ? ' selected' : ''}`)
                .attr('cx', x)
                .attr('cy', y)
                .attr('r', isSelected ? blipRSelected : blipR)
                .attr('fill', ringColors[blip.ring] || '#64748b')
                .attr('stroke', isSelected ? '#1e293b' : '#fff')
                .attr('stroke-width', isSelected ? 2.5 : 1.5)
                .append('title')
                .text(`${blip.name} (${blip.ring_label}) — ${blip.systems_count} systems — click to edit`);

            blipG.append('text')
                .attr('class', 'radar-blip-label')
                .attr('x', x + blipR + 3)
                .attr('y', y + 4)
                .attr('font-size', labelFontSize)
                .attr('font-weight', isSelected ? 600 : 400)
                .attr('fill', isSelected ? '#0f172a' : '#334155')
                .attr('pointer-events', 'none')
                .text(blip.name.length > 12 ? blip.name.slice(0, 11) + '…' : blip.name);
        });

        zoomRoot.attr('transform', currentTransform);
        svg.call(zoomBehavior.transform, currentTransform);
    }

    function zoomIn() {
        svg?.transition().duration(200).call(zoomBehavior.scaleBy, 1.25);
    }

    function zoomOut() {
        svg?.transition().duration(200).call(zoomBehavior.scaleBy, 0.8);
    }

    function resetZoom() {
        currentTransform = d3.zoomIdentity;
        svg?.transition().duration(200).call(zoomBehavior.transform, d3.zoomIdentity);
    }

    initSvg();
    requestAnimationFrame(() => renderChart());

    if (container && typeof ResizeObserver !== 'undefined') {
        const observer = new ResizeObserver(() => renderChart());
        observer.observe(container);
    } else {
        window.addEventListener('resize', renderChart);
    }

    document.getElementById('radar-zoom-in')?.addEventListener('click', zoomIn);
    document.getElementById('radar-zoom-out')?.addEventListener('click', zoomOut);
    document.getElementById('radar-zoom-reset')?.addEventListener('click', resetZoom);

    techSelect?.addEventListener('change', function () {
        const opt = this.selectedOptions[0];
        if (!opt?.value) {
            selectedBlipId = null;
            if (selectedLabel) selectedLabel.classList.add('d-none');
            renderChart();
            return;
        }
        selectedBlipId = String(opt.value);
        if (form) form.action = `${window.techRadarUpdateUrl}/${opt.value}`;
        if (ringSelect) ringSelect.value = opt.dataset.ring || 'assess';
        if (notesField) notesField.value = opt.dataset.notes || '';
        if (selectedLabel) {
            selectedLabel.textContent = opt.textContent.trim();
            selectedLabel.classList.remove('d-none');
        }
        renderChart();
    });
})();
