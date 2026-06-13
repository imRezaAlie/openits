(function () {
    'use strict';

    const ARROW_SYNC = '->>';
    const ARROW_ASYNC = '-->>';

    const config = window.SequenceDesignerConfig || {};
    let participants = [];
    let messages = [];
    let activeTab = 'designer';
    let debounceTimer = null;

    function init() {
        mermaid.initialize({
            startOnLoad: false,
            theme: 'default',
            securityLevel: 'loose',
            sequence: { useMaxWidth: true, mirrorActors: false },
        });

        const source = config.initialSource || '';
        if (source.trim()) {
            parseMermaid(source);
        } else {
            parseMermaid(getDefaultTemplate());
        }

        bindEvents();
        renderParticipants();
        renderMessages();
        syncSourceFromDesigner();
        renderPreview();
    }

    function getDefaultTemplate() {
        return 'sequenceDiagram\n    participant Client\n    participant API\n    Client->>API: Request';
    }

    function bindEvents() {
        document.getElementById('add-participant').addEventListener('click', addParticipant);
        document.getElementById('add-message').addEventListener('click', addMessage);
        document.getElementById('btn-save').addEventListener('click', saveProcess);
        document.getElementById('btn-export-svg').addEventListener('click', exportSvg);
        document.getElementById('btn-export-png').addEventListener('click', exportPng);
        document.getElementById('btn-sync-to-designer').addEventListener('click', syncDesignerFromSource);

        document.querySelectorAll('.seq-tab').forEach((tab) => {
            tab.addEventListener('click', () => switchTab(tab.dataset.tab));
        });

        const sourceEditor = document.getElementById('source-editor');
        sourceEditor.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (activeTab === 'source') {
                    renderPreviewFromSource(sourceEditor.value);
                }
            }, 400);
        });
    }

    function switchTab(tab) {
        if (tab !== 'designer' && tab !== 'source') {
            renderPreview();
            return;
        }

        activeTab = tab;
        document.querySelectorAll('.seq-tab[data-tab="designer"], .seq-tab[data-tab="source"]').forEach((el) => {
            el.classList.toggle('active', el.dataset.tab === tab);
        });
        document.getElementById('designer-view').style.display = tab === 'designer' ? '' : 'none';
        document.getElementById('source-view').style.display = tab === 'source' ? '' : 'none';

        if (tab === 'source') {
            syncSourceFromDesigner();
            renderPreviewFromSource(document.getElementById('source-editor').value);
        } else {
            syncDesignerFromSource();
        }
    }

    function addParticipant() {
        const name = 'Actor' + (participants.length + 1);
        participants.push({ id: uid(), name });
        renderParticipants();
        renderMessages();
        onDesignerChange();
    }

    function removeParticipant(id) {
        const removed = participants.find((p) => p.id === id);
        participants = participants.filter((p) => p.id !== id);
        if (removed) {
            messages = messages.filter((m) => m.from !== removed.name && m.to !== removed.name);
        }
        renderParticipants();
        renderMessages();
        onDesignerChange();
    }

    function addMessage() {
        const from = participants[0]?.name || 'Actor1';
        const to = participants[1]?.name || participants[0]?.name || 'Actor2';
        messages.push({
            id: uid(),
            from,
            to,
            label: 'Message',
            async: false,
        });
        renderMessages();
        onDesignerChange();
    }

    function removeMessage(id) {
        messages = messages.filter((m) => m.id !== id);
        renderMessages();
        onDesignerChange();
    }

    function renderParticipants() {
        const container = document.getElementById('participants-list');
        container.innerHTML = '';

        if (participants.length === 0) {
            container.innerHTML = '<p class="text-muted small mb-2">No participants yet. Add lifelines to begin.</p>';
            return;
        }

        participants.forEach((p, index) => {
            const row = document.createElement('div');
            row.className = 'seq-list-item';
            row.innerHTML = `
                <span class="text-muted small" style="width:20px">${index + 1}</span>
                <input type="text" value="${escapeAttr(p.name)}" data-participant-id="${p.id}" class="participant-name" placeholder="Participant name">
                <button type="button" class="seq-icon-btn" data-remove-participant="${p.id}" title="Remove">&times;</button>
            `;
            container.appendChild(row);
        });

        container.querySelectorAll('.participant-name').forEach((input) => {
            input.addEventListener('change', (e) => {
                const id = e.target.dataset.participantId;
                const participant = participants.find((p) => p.id === id);
                if (!participant) return;
                const oldName = participant.name;
                const newName = e.target.value.trim() || oldName;
                participant.name = newName;
                messages.forEach((m) => {
                    if (m.from === oldName) m.from = newName;
                    if (m.to === oldName) m.to = newName;
                });
                renderMessages();
                onDesignerChange();
            });
        });

        container.querySelectorAll('[data-remove-participant]').forEach((btn) => {
            btn.addEventListener('click', () => removeParticipant(btn.dataset.removeParticipant));
        });
    }

    function renderMessages() {
        const container = document.getElementById('messages-list');
        const names = participants.map((p) => p.name);
        container.innerHTML = '';

        if (messages.length === 0) {
            container.innerHTML = '<p class="text-muted small mb-2">No messages yet. Add interactions between participants.</p>';
            return;
        }

        messages.forEach((m, index) => {
            const row = document.createElement('div');
            row.className = 'seq-list-item';
            row.style.flexWrap = 'wrap';

            const fromOptions = names.map((n) => `<option value="${escapeAttr(n)}" ${n === m.from ? 'selected' : ''}>${escapeHtml(n)}</option>`).join('');
            const toOptions = names.map((n) => `<option value="${escapeAttr(n)}" ${n === m.to ? 'selected' : ''}>${escapeHtml(n)}</option>`).join('');

            row.innerHTML = `
                <span class="text-muted small" style="width:100%;margin-bottom:0.25rem">#${index + 1}</span>
                <select data-message-id="${m.id}" class="message-from" style="flex:1">${fromOptions}</select>
                <select data-message-id="${m.id}" class="message-arrow" style="flex:0 0 auto;width:70px">
                    <option value="sync" ${!m.async ? 'selected' : ''}>→ sync</option>
                    <option value="async" ${m.async ? 'selected' : ''}>⇢ async</option>
                </select>
                <select data-message-id="${m.id}" class="message-to" style="flex:1">${toOptions}</select>
                <input type="text" value="${escapeAttr(m.label)}" data-message-id="${m.id}" class="message-label" placeholder="Label" style="flex:1 1 100%">
                <button type="button" class="seq-icon-btn" data-remove-message="${m.id}" title="Remove">&times;</button>
            `;
            container.appendChild(row);
        });

        container.querySelectorAll('.message-from').forEach((el) => {
            el.addEventListener('change', (e) => updateMessage(e.target.dataset.messageId, 'from', e.target.value));
        });
        container.querySelectorAll('.message-to').forEach((e) => {
            e.addEventListener('change', (ev) => updateMessage(ev.target.dataset.messageId, 'to', ev.target.value));
        });
        container.querySelectorAll('.message-arrow').forEach((el) => {
            el.addEventListener('change', (e) => updateMessage(e.target.dataset.messageId, 'async', e.target.value === 'async'));
        });
        container.querySelectorAll('.message-label').forEach((el) => {
            el.addEventListener('input', (e) => updateMessage(e.target.dataset.messageId, 'label', e.target.value));
        });
        container.querySelectorAll('[data-remove-message]').forEach((btn) => {
            btn.addEventListener('click', () => removeMessage(btn.dataset.removeMessage));
        });
    }

    function updateMessage(id, field, value) {
        const message = messages.find((m) => m.id === id);
        if (message) {
            message[field] = value;
            onDesignerChange();
        }
    }

    function onDesignerChange() {
        syncSourceFromDesigner();
        if (activeTab !== 'source') {
            renderPreview();
        }
    }

    function buildMermaid() {
        let lines = ['sequenceDiagram'];

        participants.forEach((p) => {
            const safe = sanitizeName(p.name);
            lines.push(`    participant ${safe}`);
        });

        if (participants.length > 0) {
            lines.push('');
        }

        messages.forEach((m) => {
            const from = sanitizeName(m.from);
            const to = sanitizeName(m.to);
            const arrow = m.async ? ARROW_ASYNC : ARROW_SYNC;
            const label = (m.label || '').replace(/"/g, "'");
            lines.push(`    ${from}${arrow}${to}: ${label}`);
        });

        return lines.join('\n');
    }

    function sanitizeName(name) {
        const trimmed = (name || 'Actor').trim();
        if (/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(trimmed)) {
            return trimmed;
        }
        return '"' + trimmed.replace(/"/g, "'") + '"';
    }

    function syncSourceFromDesigner() {
        document.getElementById('source-editor').value = buildMermaid();
    }

    function syncDesignerFromSource() {
        const source = document.getElementById('source-editor').value;
        parseMermaid(source);
        renderParticipants();
        renderMessages();
        renderPreview();
    }

    function parseMermaid(source) {
        participants = [];
        messages = [];
        const lines = source.split('\n').map((l) => l.trim()).filter(Boolean);

        lines.forEach((line) => {
            const participantMatch = line.match(/^participant\s+(.+)$/i);
            if (participantMatch) {
                let name = participantMatch[1].trim();
                const aliasMatch = name.match(/^(\S+)\s+as\s+(.+)$/i);
                if (aliasMatch) {
                    name = aliasMatch[2].replace(/^"|"$/g, '');
                } else {
                    name = name.replace(/^"|"$/g, '');
                }
                if (!participants.find((p) => p.name === name)) {
                    participants.push({ id: uid(), name });
                }
                return;
            }

            const messageMatch = line.match(/^(.+?)(-->>|->>)(.+?):\s*(.+)$/);
            if (messageMatch) {
                let from = messageMatch[1].trim().replace(/^"|"$/g, '');
                let to = messageMatch[3].trim().replace(/^"|"$/g, '');
                const label = messageMatch[4].trim();
                const async = messageMatch[2] === ARROW_ASYNC;

                [from, to].forEach((n) => {
                    if (n && !participants.find((p) => p.name === n)) {
                        participants.push({ id: uid(), name: n });
                    }
                });

                messages.push({ id: uid(), from, to, label, async });
            }
        });
    }

    function getCurrentSource() {
        if (activeTab === 'source') {
            return document.getElementById('source-editor').value;
        }
        return buildMermaid();
    }

    async function renderPreview() {
        await renderPreviewFromSource(getCurrentSource());
    }

    async function renderPreviewFromSource(source) {
        const container = document.getElementById('mermaid-preview');
        const errorEl = document.getElementById('preview-error');
        errorEl.classList.remove('visible');
        errorEl.textContent = '';

        try {
            const id = 'mermaid-' + Date.now();
            const { svg } = await mermaid.render(id, source);
            container.innerHTML = svg;
        } catch (err) {
            container.innerHTML = '';
            errorEl.textContent = 'Diagram error: ' + (err.message || 'Invalid Mermaid syntax');
            errorEl.classList.add('visible');
        }
    }

    function saveProcess() {
        const nameInput = document.getElementById('process-name');
        const name = (nameInput?.value || config.processName || '').trim();
        if (!name) {
            alert('Please enter a process name.');
            nameInput?.focus();
            return;
        }

        const source = getCurrentSource();
        const btn = document.getElementById('btn-save');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        const isEdit = config.mode === 'edit';
        const url = isEdit ? config.updateUrl : config.storeUrl;
        const data = {
            _token: config.csrfToken,
            name,
            diagram_xml: source,
            diagram_type: 'sequence',
        };

        if (!isEdit) {
            data.system_id = config.systemId;
        } else {
            data._method = 'PUT';
        }

        $.ajax({
            type: 'POST',
            url,
            data,
            success: function (res) {
                btn.disabled = false;
                btn.textContent = 'Save Process';
                alert(res.message || 'Process saved successfully!');
                if (res.redirect) {
                    window.location.href = res.redirect;
                } else if (config.backUrl) {
                    window.location.href = config.backUrl;
                }
            },
            error: function () {
                btn.disabled = false;
                btn.textContent = 'Save Process';
                alert('Failed to save process.');
            },
        });
    }

    function exportSvg() {
        const svg = document.querySelector('#mermaid-preview svg');
        if (!svg) {
            alert('Nothing to export. Fix diagram errors first.');
            return;
        }
        downloadFile(getFileName() + '.svg', new XMLSerializer().serializeToString(svg), 'image/svg+xml');
    }

    function exportPng() {
        const svg = document.querySelector('#mermaid-preview svg');
        if (!svg) {
            alert('Nothing to export. Fix diagram errors first.');
            return;
        }
        const svgData = new XMLSerializer().serializeToString(svg);
        const img = new Image();
        img.onload = function () {
            const canvas = document.createElement('canvas');
            canvas.width = img.width * 2;
            canvas.height = img.height * 2;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            canvas.toBlob((blob) => {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = getFileName() + '.png';
                a.click();
                URL.revokeObjectURL(url);
            });
        };
        img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
    }

    function getFileName() {
        const name = (document.getElementById('process-name')?.value || config.processName || 'sequence-diagram').trim();
        return name.replace(/[^a-z0-9-_]+/gi, '-').toLowerCase() || 'sequence-diagram';
    }

    function downloadFile(filename, content, mime) {
        const blob = new Blob([content], { type: mime });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    }

    function uid() {
        return 'id-' + Math.random().toString(36).slice(2, 9);
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function escapeAttr(str) {
        return escapeHtml(str).replace(/"/g, '&quot;');
    }

    document.addEventListener('DOMContentLoaded', init);
})();
