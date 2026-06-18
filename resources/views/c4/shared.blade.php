@extends('master')

@push('head-src')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/c4-diagram.css') }}" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="{{ asset('js/d3.v7.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/dagre@0.8.5/dist/dagre.min.js"></script>
@endpush

@section('body')
<div class="content-body c4-page c4-shared" x-data="c4DiagramApp()" x-init="init()">
    <div class="container-fluid p-0">
        <div class="c4-toolbar">
            <div class="c4-toolbar-left">
                <h5 class="mb-0">{{ $system->name }} — C4 {{ ucfirst($level) }} (Read-only)</h5>
            </div>
            <div class="c4-toolbar-right">
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="resetZoom()">Reset Zoom</button>
                <button type="button" class="btn btn-sm btn-outline-success" @click="exportSvg()">Export SVG</button>
            </div>
        </div>
        <div class="c4-workspace c4-workspace--shared">
            <main class="c4-canvas-wrap c4-canvas-wrap--full">
                <div id="c4-diagram-container">
                    <svg id="c4-diagram-svg"></svg>
                </div>
            </main>
        </div>
    </div>
</div>

<script>
    window.c4DiagramData = @json($diagramData);
    window.c4DiagramConfig = {
        level: @json($level),
        systemId: {{ $system->id }},
        readOnly: true,
        routes: {},
        csrf: '',
    };
</script>
@endsection

@push('footer-src')
    <script src="{{ asset('js/c4-diagram.js') }}"></script>
@endpush
