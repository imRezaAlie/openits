@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <style>
        .markdown-body {
            line-height: 1.6;
            font-size: 0.95rem;
        }
        .markdown-body h1, .markdown-body h2, .markdown-body h3, .markdown-body h4 {
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
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
        .markdown-source {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85rem;
            min-height: 400px;
        }
    </style>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">{{ $typeLabel }}</h4>
                    <small class="text-muted">{{ $system->name }}</small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-primary active" id="renderedTab">Rendered</button>
                        <button type="button" class="btn btn-outline-primary" id="sourceTab">Source</button>
                    </div>
                    @if(isset($systemDocument))
                        <a href="{{ route('systems.documents.edit-markdown', [$system, $systemDocument]) }}" class="btn btn-success btn-sm">
                            <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                        </a>
                        <a href="{{ route('systems.documents.download', [$system, $systemDocument]) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-download me-1"></i>Download
                        </a>
                    @elseif($type)
                        <form action="{{ route('systems.documents.generate', $system) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="types[]" value="{{ $type }}">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-file-circle-plus me-1"></i>Save to Library
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('systems.documents', $system) }}" class="btn btn-outline-secondary btn-sm">Back</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div id="renderedView" class="markdown-body"></div>
                        <textarea id="sourceView" class="form-control markdown-source d-none" readonly>{{ $markdown }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.2.4/dist/purify.min.js"></script>
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    <script>
        const markdown = @json($markdown);
        const renderedView = document.getElementById('renderedView');
        const sourceView = document.getElementById('sourceView');
        const renderedTab = document.getElementById('renderedTab');
        const sourceTab = document.getElementById('sourceTab');

        renderedView.innerHTML = DOMPurify.sanitize(marked.parse(markdown));

        function showRendered() {
            renderedView.classList.remove('d-none');
            sourceView.classList.add('d-none');
            renderedTab.classList.add('active', 'btn-primary');
            renderedTab.classList.remove('btn-outline-primary');
            sourceTab.classList.remove('active', 'btn-primary');
            sourceTab.classList.add('btn-outline-primary');
        }

        function showSource() {
            renderedView.classList.add('d-none');
            sourceView.classList.remove('d-none');
            sourceTab.classList.add('active', 'btn-primary');
            sourceTab.classList.remove('btn-outline-primary');
            renderedTab.classList.remove('active', 'btn-primary');
            renderedTab.classList.add('btn-outline-primary');
        }

        renderedTab.addEventListener('click', showRendered);
        sourceTab.addEventListener('click', showSource);
    </script>
@endpush
