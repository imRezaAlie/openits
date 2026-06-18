(function () {
    'use strict';

    const hints = {
        openapi: 'Accepted: .json, .yaml, .yml — creates API Gateway, Backend, and Controller components per operation',
        asyncapi: 'Accepted: .json, .yaml, .yml — creates Event Bus, Producers, and Consumers per channel',
        structurizr: 'Accepted: .dsl, .txt — parses Structurizr DSL workspace model',
        json_backup: 'Accepted: .json — restores a previously exported C4 JSON backup',
    };

    const acceptMap = {
        openapi: '.json,.yaml,.yml',
        asyncapi: '.json,.yaml,.yml',
        structurizr: '.dsl,.txt,.structurizr',
        json_backup: '.json',
    };

    let pollTimer = null;

    function init() {
        const form = document.getElementById('c4ImportForm');
        const typeSelect = document.getElementById('c4-import-type');
        const submitBtn = document.getElementById('c4ImportSubmitBtn');
        const importUrl = window.c4DiagramConfig?.routes?.import;

        if (!form || !importUrl) return;

        typeSelect?.addEventListener('change', updateHint);
        updateHint();

        submitBtn?.addEventListener('click', () => submitImport(importUrl));

        document.getElementById('c4ImportModal')?.addEventListener('hidden.bs.modal', () => {
            clearInterval(pollTimer);
            resetForm();
        });
    }

    function updateHint() {
        const type = document.getElementById('c4-import-type')?.value || 'openapi';
        const hint = document.getElementById('c4-import-hint');
        const fileInput = document.getElementById('c4-import-file');
        const baseUrlField = document.getElementById('c4-base-url-field');

        if (hint) hint.textContent = hints[type] || '';
        if (fileInput) fileInput.accept = acceptMap[type] || '.json';
        if (baseUrlField) baseUrlField.style.display = type === 'openapi' ? '' : 'none';
    }

    async function submitImport(importUrl) {
        const form = document.getElementById('c4ImportForm');
        const fileInput = document.getElementById('c4-import-file');
        const submitBtn = document.getElementById('c4ImportSubmitBtn');

        if (!fileInput?.files?.length) {
            alert('Please select a file.');
            return;
        }

        const formData = window.LaravelCsrf ? window.LaravelCsrf.appendToken(new FormData(form)) : new FormData(form);
        submitBtn.disabled = true;
        showProgress('Uploading…', 0);

        try {
            const fetchFn = window.LaravelCsrf?.fetch || fetch;
            const res = await fetchFn(importUrl, {
                method: 'POST',
                body: formData,
            });

            const data = await res.json();
            if (!res.ok) {
                showResult('danger', data.message || 'Import failed to start.');
                submitBtn.disabled = false;
                return;
            }

            showProgress('Queued — processing in background…', 5);
            pollImportStatus(data.status_url);
        } catch (e) {
            showResult('danger', 'Upload failed: ' + e.message);
            submitBtn.disabled = false;
        }
    }

    function pollImportStatus(statusUrl) {
        clearInterval(pollTimer);

        pollTimer = setInterval(async () => {
            try {
                const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();

                const label = data.status === 'processing'
                    ? 'Processing…'
                    : data.status === 'completed'
                        ? 'Completed'
                        : data.status === 'failed'
                            ? 'Failed'
                            : 'Queued…';

                showProgress(label, data.progress || 0);

                if (data.is_finished) {
                    clearInterval(pollTimer);
                    const submitBtn = document.getElementById('c4ImportSubmitBtn');
                    if (submitBtn) submitBtn.disabled = false;

                    if (data.status === 'completed') {
                        const summary = data.result
                            ? Object.entries(data.result).map(([k, v]) => `${k}: ${v}`).join(', ')
                            : '';
                        showResult('success', `Import completed. ${summary}`);
                        if (data.redirect_url) {
                            setTimeout(() => { window.location.href = data.redirect_url; }, 1500);
                        }
                    } else {
                        showResult('danger', data.error_message || 'Import failed.');
                    }
                }
            } catch (e) {
                clearInterval(pollTimer);
                showResult('danger', 'Status poll failed.');
                document.getElementById('c4ImportSubmitBtn').disabled = false;
            }
        }, 1500);
    }

    function showProgress(text, percent) {
        const wrap = document.getElementById('c4-import-progress-wrap');
        const bar = document.getElementById('c4-import-progress-bar');
        const statusText = document.getElementById('c4-import-status-text');
        const percentEl = document.getElementById('c4-import-percent');
        const result = document.getElementById('c4-import-result');

        wrap?.classList.remove('d-none');
        result?.classList.add('d-none');
        if (statusText) statusText.textContent = text;
        if (percentEl) percentEl.textContent = percent + '%';
        if (bar) {
            bar.style.width = percent + '%';
            bar.classList.toggle('progress-bar-animated', percent < 100);
        }
    }

    function showResult(type, message) {
        const result = document.getElementById('c4-import-result');
        if (!result) return;
        result.className = 'alert alert-' + type;
        result.textContent = message;
        result.classList.remove('d-none');
    }

    function resetForm() {
        document.getElementById('c4-import-progress-wrap')?.classList.add('d-none');
        document.getElementById('c4-import-result')?.classList.add('d-none');
        document.getElementById('c4ImportSubmitBtn').disabled = false;
        document.getElementById('c4-import-progress-bar').style.width = '0%';
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
