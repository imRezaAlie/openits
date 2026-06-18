@extends('master')

@push('head-src')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/c4-diagram.css') }}" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="{{ asset('js/d3.v7.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/dagre@0.8.5/dist/dagre.min.js"></script>
@endpush

@section('body')
<div class="content-body c4-page" :class="{ 'connect-mode': connectMode }" x-data="c4DiagramApp()" x-init="init()">
    <div class="container-fluid p-0">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible m-3 mb-0">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="c4-toolbar">
            <div class="c4-toolbar-left">
                <a href="{{ route('c4.index') }}" class="btn btn-sm btn-outline-secondary">← All Systems</a>
                <nav class="c4-breadcrumb" aria-label="C4 levels">
                    <a href="{{ route('c4.systems.context', $system) }}" class="{{ $level === 'context' ? 'active' : '' }}">Context</a>
                    <span class="sep">›</span>
                    <a href="{{ route('c4.systems.containers', $system) }}" class="{{ $level === 'container' ? 'active' : '' }}">Containers</a>
                    @if(isset($container))
                        <span class="sep">›</span>
                        <span class="active">{{ $container->name }}</span>
                    @elseif($level === 'component')
                        <span class="sep">›</span>
                        <span class="active">Components</span>
                    @endif
                </nav>
            </div>
            <div class="c4-toolbar-center">
                <input type="search" class="form-control form-control-sm c4-search" placeholder="Search elements..."
                       x-model="searchQuery" @input.debounce.300ms="runSearch()">
            </div>
            <div class="c4-toolbar-right">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" @click="undo()"
                            :disabled="!canUndo" title="Undo (Ctrl+Z)">↶</button>
                    <button type="button" class="btn btn-outline-secondary" @click="redo()"
                            :disabled="!canRedo" title="Redo (Ctrl+Y)">↷</button>
                </div>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" @click="zoomIn()" title="Zoom in">+</button>
                    <button type="button" class="btn btn-outline-secondary" @click="zoomOut()" title="Zoom out">−</button>
                    <button type="button" class="btn btn-outline-secondary" @click="resetZoom()" title="Reset">⟲</button>
                    <button type="button" class="btn btn-outline-primary" @click="autoLayout()" title="Auto-layout">Layout</button>
                </div>
                <button type="button" class="btn btn-sm"
                        :class="connectMode ? 'btn-primary' : 'btn-outline-secondary'"
                        @click="toggleConnectMode()" x-show="!readOnly" title="Draw connections between nodes">
                    Connect
                </button>
                <form action="{{ route('c4.systems.sync', $system) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-info">Sync APIs</button>
                </form>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#c4ImportModal" x-show="!readOnly">Import</button>
                    <a href="{{ route('c4.systems.export', [$system, 'format' => 'json']) }}" class="btn btn-outline-success" target="_blank">JSON</a>
                    <a href="{{ route('c4.systems.export', [$system, 'format' => 'structurizr']) }}" class="btn btn-outline-success">DSL</a>
                    <a href="{{ route('c4.systems.export', [$system, 'format' => 'drawio']) }}" class="btn btn-outline-success">Draw.io</a>
                    <button type="button" class="btn btn-outline-success" @click="exportSvg()">SVG</button>
                    <button type="button" class="btn btn-outline-success" @click="exportPng()">PNG</button>
                </div>
                <a href="{{ route('c4.adrs.index') }}" class="btn btn-sm btn-outline-secondary">ADRs</a>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="paletteOpen = !paletteOpen" x-show="!readOnly">Palette</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="panelOpen = !panelOpen">Properties</button>
            </div>
        </div>

        <div class="c4-workspace">
            <aside class="c4-palette" :class="{ 'collapsed': !paletteOpen }" x-show="!readOnly">
                <h6>Element Palette</h6>
                @if($level === 'container')
                    <p class="small text-muted">Drag containers onto the canvas</p>
                    @foreach($containerTypes as $type)
                        <div class="c4-palette-item" draggable="true"
                             @dragstart="paletteDrag($event, 'container', '{{ $type }}')">
                            <span class="dot dot-container"></span>
                            {{ \App\Support\C4ContainerTypes::label($type) }}
                        </div>
                    @endforeach
                @elseif($level === 'component')
                    <p class="small text-muted">Drag components onto the canvas</p>
                    @foreach($componentTypes as $type)
                        <div class="c4-palette-item" draggable="true"
                             @dragstart="paletteDrag($event, 'component', '{{ $type }}')">
                            <span class="dot dot-component"></span>
                            {{ \App\Support\C4ComponentTypes::label($type) }}
                        </div>
                    @endforeach
                @else
                    <p class="small text-muted">Context level — drag from a node's <strong>●</strong> port to another to connect. Double-click to drill down.</p>
                @endif
                <p class="small text-muted mt-2" x-show="connectMode && !readOnly">
                    <span class="badge badge-primary">Connect mode</span> — drag from port to port
                </p>
            </aside>

            <main class="c4-canvas-wrap" @dragover.prevent @drop="canvasDrop($event)">
                <div id="c4-diagram-container">
                    <svg id="c4-diagram-svg"></svg>
                    <div class="c4-minimap" id="c4-minimap"></div>
                </div>
            </main>

            <aside class="c4-properties" :class="{ 'collapsed': !panelOpen }">
                <template x-if="!selectedNode && !selectedEdge">
                    <div class="c4-properties-empty">
                        <h6>Properties</h6>
                        <p class="text-muted small">Click a node or connection to edit. Drag between <strong>●</strong> ports to connect. Double-click a node to drill down.</p>
                    </div>
                </template>
                <template x-if="selectedEdge">
                    <div class="c4-properties-form">
                        <h6>Connection</h6>
                        <span class="badge badge-secondary mb-2" x-text="selectedEdge.protocol || 'No protocol'"></span>
                        <div class="mb-2">
                            <label class="form-label small">Protocol</label>
                            <select class="form-control form-control-sm" x-model="edgeForm.protocol">
                                @foreach($protocols as $protocol)
                                    <option value="{{ $protocol }}">{{ $protocol }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Description</label>
                            <textarea class="form-control form-control-sm" rows="3" x-model="edgeForm.description"></textarea>
                        </div>
                        <div class="mb-2 form-check">
                            <input type="checkbox" class="form-check-input" id="edge-sync" x-model="edgeForm.sync">
                            <label class="form-check-label small" for="edge-sync">Synchronous</label>
                        </div>
                        <div class="mb-3" x-show="!readOnly">
                            <button type="button" class="btn btn-primary btn-sm w-100" @click="saveEdge()">Save</button>
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-2" @click="deleteEdge()">Delete Connection</button>
                        </div>
                    </div>
                </template>
                <template x-if="selectedNode && !selectedEdge">
                    <div class="c4-properties-form">
                        <h6 x-text="selectedNode.name"></h6>
                        <span class="badge mb-2" :class="'badge-' + selectedNode.type" x-text="selectedNode.type"></span>
                        <div class="mb-2" x-show="selectedNode.deprecated">
                            <span class="badge badge-warning">Deprecated</span>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Name</label>
                            <input type="text" class="form-control form-control-sm" x-model="editForm.name">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Description</label>
                            <textarea class="form-control form-control-sm" rows="4" x-model="editForm.description"></textarea>
                        </div>
                        <div class="mb-2" x-show="editForm.technology !== undefined">
                            <label class="form-label small">Technology</label>
                            <input type="text" class="form-control form-control-sm" x-model="editForm.technology">
                        </div>
                        <div class="mb-2" x-show="selectedNode.container_type || selectedNode.component_type">
                            <label class="form-label small">Type</label>
                            <input type="text" class="form-control form-control-sm" readonly
                                   :value="selectedNode.container_type || selectedNode.component_type">
                        </div>
                        <div class="mb-3" x-show="!readOnly">
                            <button type="button" class="btn btn-primary btn-sm w-100" @click="saveProperties()">Save</button>
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-2" @click="deleteSelected()" x-show="canDelete()">Delete</button>
                        </div>
                        <div x-show="selectedNode.drill_down">
                            <a :href="selectedNode.drill_down" class="btn btn-outline-primary btn-sm w-100">Drill Down →</a>
                        </div>
                        <div x-show="selectedNode.drill_up" class="mt-2">
                            <a :href="selectedNode.drill_up" class="btn btn-outline-secondary btn-sm w-100">← Back Up</a>
                        </div>
                    </div>
                </template>

                @include('c4._collaboration_panel')
            </aside>
        </div>
    </div>
</div>

@include('c4._import_modal')

<script>
    window.c4DiagramData = @json($diagramData);
    window.c4DiagramConfig = {
        level: @json($level),
        systemId: {{ $system->id }},
        containerId: @json($container->id ?? null),
        readOnly: false,
        routes: {
            diagramData: '{{ route('c4.diagram.data') }}',
            contextUpdate: '{{ route('c4.systems.context.update', $system) }}',
            containerStore: '{{ route('c4.containers.store', $system) }}',
            containerUpdate: '{{ url('/c4/containers') }}',
            componentStore: @json(isset($container) ? route('c4.components.store', $container) : null),
            componentUpdate: '{{ url('/c4/components') }}',
            search: '{{ route('c4.systems.search', $system) }}',
            relationshipStore: '{{ route('c4.relationships.store') }}',
            relationshipUpdate: '{{ url('/c4/relationships') }}',
            import: '{{ route('c4.systems.import', $system) }}',
        },
        protocols: @json($protocols),
        users: @json($users),
        contextId: @json($contextId),
        collaboration: {
            comments: '{{ route('c4.comments.index') }}',
            commentStore: '{{ route('c4.comments.store') }}',
            commentResolve: '{{ url('/c4/comments') }}',
            changeRequests: '{{ route('c4.systems.change-requests', $system) }}',
            changeRequestStore: '{{ route('c4.systems.change-requests.store', $system) }}',
            changeRequestReview: '{{ url('/c4/change-requests') }}',
        },
        csrf: @json(csrf_token()),
    };
</script>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    <script src="{{ asset('js/c4-diagram.js') }}"></script>
    <script src="{{ asset('js/c4-import.js') }}"></script>
    <script src="{{ asset('js/c4-collaboration.js') }}"></script>
@endpush
