@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <style>
        .md-editor-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            min-height: calc(100vh - 280px);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        @media (max-width: 991px) {
            .md-editor-layout {
                grid-template-columns: 1fr;
                min-height: auto;
            }
        }
        .md-editor-pane {
            display: flex;
            flex-direction: column;
            min-height: 420px;
        }
        .md-editor-pane + .md-editor-pane {
            border-left: 1px solid #dee2e6;
        }
        @media (max-width: 991px) {
            .md-editor-pane + .md-editor-pane {
                border-left: none;
                border-top: 1px solid #dee2e6;
            }
        }
        .md-editor-pane-header {
            padding: 0.5rem 0.75rem;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6c757d;
        }
        .md-editor-textarea {
            flex: 1;
            width: 100%;
            border: none;
            resize: none;
            padding: 1rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.875rem;
            line-height: 1.55;
            outline: none;
            background: #fff;
        }
        .md-editor-textarea:focus {
            box-shadow: inset 0 0 0 2px rgba(13, 110, 253, 0.15);
        }
        .markdown-body {
            flex: 1;
            overflow: auto;
            padding: 1rem;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        .markdown-body h1, .markdown-body h2, .markdown-body h3, .markdown-body h4 {
            margin-top: 1.25rem;
            margin-bottom: 0.65rem;
        }
        .markdown-body table {
            width: 100%;
            margin-bottom: 1rem;
            border-collapse: collapse;
        }
        .markdown-body th, .markdown-body td {
            border: 1px solid #dee2e6;
            padding: 0.5rem 0.75rem;
            vertical-align: top;
        }
        .markdown-body th {
            background: #f8f9fa;
        }
        .markdown-body blockquote {
            border-left: 4px solid #0d6efd;
            padding-left: 1rem;
            color: #6c757d;
            margin: 1rem 0;
        }
        .markdown-body code {
            background: #f1f3f5;
            padding: 0.1rem 0.35rem;
            border-radius: 4px;
            font-size: 0.875em;
        }
        .markdown-body pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
            overflow-x: auto;
        }
        .md-editor-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }
        .md-editor-toolbar .btn {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
        }
    </style>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ $systemDocument
                  ? route('systems.documents.update-markdown', [$system, $systemDocument])
                  : route('systems.documents.store-markdown', $system) }}"
              id="markdownEditorForm">
            @csrf
            @if($systemDocument)
                @method('PUT')
            @endif

            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="mb-0">{{ $systemDocument ? 'Edit Markdown Document' : 'Write Markdown Document' }}</h4>
                        <small class="text-muted">Live preview updates as you type</small>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Save
                        </button>
                        <a href="{{ route('systems.documents', $system) }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6 mb-2 mb-md-0">
                    <label class="form-label">Document Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required
                           value="{{ old('name', $systemDocument?->name) }}"
                           placeholder="e.g. Operations Runbook">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Version</label>
                    <input type="text" name="version" class="form-control"
                           value="{{ old('version', $systemDocument?->version) }}"
                           placeholder="e.g. 1.0">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <small class="text-muted">Ctrl+S to save</small>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-12">
                    <div class="md-editor-toolbar mb-2">
                        <button type="button" class="btn btn-outline-secondary insert-md" data-snippet="# Heading">H1</button>
                        <button type="button" class="btn btn-outline-secondary insert-md" data-snippet="## Heading">H2</button>
                        <button type="button" class="btn btn-outline-secondary insert-md" data-snippet="**bold**">Bold</button>
                        <button type="button" class="btn btn-outline-secondary insert-md" data-snippet="*italic*">Italic</button>
                        <button type="button" class="btn btn-outline-secondary insert-md" data-snippet="`code`">Code</button>
                        <button type="button" class="btn btn-outline-secondary insert-md" data-snippet="- list item">List</button>
                        <button type="button" class="btn btn-outline-secondary insert-md" data-snippet="| Column | Value |
| --- | --- |
| Row | Data |">Table</button>
                        <button type="button" class="btn btn-outline-secondary insert-md" data-snippet="```\ncode block\n```">Block</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card p-0">
                        <div class="card-body p-0">
                            <div class="md-editor-layout">
                                <div class="md-editor-pane">
                                    <div class="md-editor-pane-header">Markdown</div>
                                    <textarea name="content" id="markdownInput" class="md-editor-textarea" required spellcheck="false">{{ $content }}</textarea>
                                </div>
                                <div class="md-editor-pane">
                                    <div class="md-editor-pane-header">Live Preview</div>
                                    <div id="markdownPreview" class="markdown-body"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    <script>
        const markdownInput = document.getElementById('markdownInput');
        const markdownPreview = document.getElementById('markdownPreview');
        const editorForm = document.getElementById('markdownEditorForm');
        let renderTimer;

        function renderPreview() {
            markdownPreview.innerHTML = marked.parse(markdownInput.value || '');
        }

        function scheduleRender() {
            clearTimeout(renderTimer);
            renderTimer = setTimeout(renderPreview, 120);
        }

        markdownInput.addEventListener('input', scheduleRender);
        renderPreview();

        document.querySelectorAll('.insert-md').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const snippet = this.dataset.snippet || '';
                const start = markdownInput.selectionStart;
                const end = markdownInput.selectionEnd;
                const before = markdownInput.value.substring(0, start);
                const after = markdownInput.value.substring(end);
                const needsNewline = before.length > 0 && !before.endsWith('\n') ? '\n' : '';

                markdownInput.value = before + needsNewline + snippet + (after.startsWith('\n') || after === '' ? '' : '\n') + after;
                markdownInput.focus();
                const cursor = (before + needsNewline + snippet).length;
                markdownInput.setSelectionRange(cursor, cursor);
                scheduleRender();
            });
        });

        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                editorForm.requestSubmit();
            }

            if (e.key === 'Tab' && document.activeElement === markdownInput) {
                e.preventDefault();
                const start = markdownInput.selectionStart;
                markdownInput.value = markdownInput.value.substring(0, start) + '    ' + markdownInput.value.substring(markdownInput.selectionEnd);
                markdownInput.selectionStart = markdownInput.selectionEnd = start + 4;
                scheduleRender();
            }
        });
    </script>
@endpush
