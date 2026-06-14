@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('css/integration-tree.css') }}" rel="stylesheet">
    <link href="{{ asset('css/api-types.css') }}" rel="stylesheet">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
@endpush

@section('body')
<div class="content-body integration-tree-page">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        @if($selectedSystem)
                            <nav aria-label="breadcrumb" class="integration-breadcrumb mb-1">
                                <ol class="breadcrumb mb-0 py-0">
                                    <li class="breadcrumb-item"><a href="{{ route('integrations.tree') }}">All Integrations</a></li>
                                    @if($selectedSystem->vendor)
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('integrations.tree', ['vendor_id' => $selectedSystem->vendor_id]) }}">{{ $selectedSystem->vendor->name }}</a>
                                        </li>
                                    @endif
                                    @if($selectedSystem->parent)
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('integrations.system', $selectedSystem->parent) }}">{{ $selectedSystem->parent->name }}</a>
                                        </li>
                                    @endif
                                    <li class="breadcrumb-item active">{{ $selectedSystem->name }}</li>
                                </ol>
                            </nav>
                            <h4 class="mb-0">{{ $selectedSystem->name }} — Integrations</h4>
                            @if($selectedSystem->vendor)
                                <span class="badge badge-info mt-1">{{ $selectedSystem->vendor->name }}</span>
                            @endif
                            @if($selectedSystem->system_type)
                                <span class="badge badge-light mt-1">{{ $selectedSystem->system_type }}</span>
                            @endif
                        @elseif($selectedVendor ?? null)
                            <nav aria-label="breadcrumb" class="integration-breadcrumb mb-1">
                                <ol class="breadcrumb mb-0 py-0">
                                    <li class="breadcrumb-item"><a href="{{ route('integrations.tree') }}">All Integrations</a></li>
                                    <li class="breadcrumb-item active">{{ $selectedVendor->name }}</li>
                                </ol>
                            </nav>
                            <h4 class="mb-0">{{ $selectedVendor->name }} — Systems &amp; APIs</h4>
                        @else
                            <h4 class="mb-0">Integration Tree View</h4>
                            <small class="text-muted">Vendor → System → API → cross-system integrations</small>
                        @endif
                    </div>
                    <div class="btn-group view-toggle" role="group">
                        <a href="{{ route('apis.index') }}" class="btn btn-outline-secondary btn-sm">List View</a>
                        <a href="{{ route('integrations.catalog') }}" class="btn btn-outline-secondary btn-sm">Catalog</a>
                        <a href="{{ route('integrations.tree') }}" class="btn btn-primary btn-sm {{ ($selectedSystem || ($selectedVendor ?? null)) ? '' : 'active' }}">Tree View</a>
                        <a href="{{ route('integrations.export') }}" class="btn btn-outline-secondary btn-sm" target="_blank">Export JSON</a>
                        <a href="{{ route('systems.index') }}" class="btn btn-outline-secondary btn-sm">Systems</a>
                        <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary btn-sm">Vendors</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <div class="card tree-controls-card">
                    <div class="card-body py-2">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="system-filter-wrap">
                                <label for="vendor-filter" class="form-label mb-0 me-2 small text-muted">Vendor</label>
                                <select id="vendor-filter" class="form-control form-control-sm">
                                    <option value="">All vendors</option>
                                    @foreach($allVendors as $vendor)
                                        <option value="{{ $vendor->id }}" @selected(($selectedVendor ?? null) && $selectedVendor->id === $vendor->id)>
                                            {{ $vendor->name }} ({{ $vendor->systems_count }} systems)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="system-filter-wrap">
                                <label for="system-filter" class="form-label mb-0 me-2 small text-muted">System</label>
                                <select id="system-filter" class="form-control form-control-sm">
                                    <option value="">All systems</option>
                                    @foreach($allSystems as $sys)
                                        <option value="{{ $sys->id }}" @selected($selectedSystem && $selectedSystem->id === $sys->id)>
                                            {{ $sys->name }}@if($sys->vendor) ({{ $sys->vendor->name }})@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="text" id="tree-search" class="form-control form-control-sm" style="max-width:220px" placeholder="Search nodes...">
                            <button type="button" id="btn-expand-all" class="btn btn-sm btn-outline-primary">Expand All</button>
                            <button type="button" id="btn-collapse-all" class="btn btn-sm btn-outline-primary">Collapse All</button>
                            <button type="button" id="btn-zoom-in" class="btn btn-sm btn-outline-secondary">Zoom +</button>
                            <button type="button" id="btn-zoom-out" class="btn btn-sm btn-outline-secondary">Zoom −</button>
                            <button type="button" id="btn-reset-zoom" class="btn btn-sm btn-outline-secondary">Reset</button>
                            <div class="btn-group btn-group-sm ms-2">
                                <button type="button" id="layout-vertical" class="btn btn-outline-info active">Vertical</button>
                                <button type="button" id="layout-horizontal" class="btn btn-outline-info">Horizontal</button>
                            </div>
                            <div class="ms-auto d-flex gap-2">
                                <button type="button" id="btn-export-svg" class="btn btn-sm btn-success">Export SVG</button>
                                <button type="button" id="btn-export-png" class="btn btn-sm btn-success">Export PNG</button>
                            </div>
                        </div>
                        <div class="tree-legend mt-2">
                            @foreach(\App\Support\ApiTypes::ALL as $apiType)
                                <span class="legend-item"><span class="legend-dot legend-{{ $apiType === 'socketio' ? 'socketio' : $apiType }}"></span> {{ \App\Support\ApiTypes::label($apiType) }}</span>
                            @endforeach
                            <span class="legend-item"><span class="legend-dot legend-vendor"></span> Vendor</span>
                            <span class="legend-item"><span class="legend-dot legend-system"></span> System</span>
                            <span class="legend-item"><span class="legend-dot legend-integration"></span> Integration</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($selectedSystem && $selectedSystem->description)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-light mb-0 py-2">{{ $selectedSystem->description }}</div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div id="integration-tree-container">
                            <svg id="integration-tree-svg"></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card integration-overview">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">
                            @if($selectedSystem)
                                {{ $selectedSystem->name }} — Owned APIs &amp; Integrations
                            @elseif($selectedVendor ?? null)
                                {{ $selectedVendor->name }} — Systems Overview
                            @else
                                Integration Overview — Vendors, Systems &amp; API TPS
                            @endif
                        </h5>
                        @if($selectedSystem)
                            <a href="{{ route('systems.index') }}" class="btn btn-sm btn-outline-primary">Manage System</a>
                        @endif
                    </div>
                    <div class="card-body">
                        @forelse($systemsOverview as $system)
                            <div class="system-block" id="system-block-{{ $system['id'] }}">
                                <div class="system-header">
                                    @if(!$selectedSystem || $selectedSystem->id !== $system['id'])
                                        <h5>
                                            <a href="{{ $system['integrations_url'] }}">{{ $system['name'] }}</a>
                                        </h5>
                                    @else
                                        <h5>{{ $system['name'] }}</h5>
                                    @endif
                                    @if($system['vendor_name'])
                                        <span class="badge badge-info">{{ $system['vendor_name'] }}</span>
                                    @endif
                                    @if($system['system_type'])
                                        <span class="badge badge-light">{{ $system['system_type'] }}</span>
                                    @endif
                                    <span class="badge badge-secondary">{{ $system['api_count'] }} API{{ $system['api_count'] !== 1 ? 's' : '' }}</span>
                                    @if(!$selectedSystem)
                                        <a href="{{ $system['integrations_url'] }}" class="btn btn-xs btn-outline-primary btn-sm ms-auto">View Tree</a>
                                    @endif
                                </div>
                                @if($system['description'])
                                    <p class="text-muted small mb-2">{{ $system['description'] }}</p>
                                @endif
                                @forelse($system['apis'] as $api)
                                    <div class="api-row type-{{ $api['type_raw'] ?? 'rest' }}">
                                        <a href="{{ $api['url'] }}" class="api-row-name">
                                            {{ $api['name'] }}
                                            <span class="api-type-chip api-type-{{ $api['type_raw'] ?? 'rest' }}">{{ $api['type'] }}</span>
                                        </a>
                                        <div class="api-row-meta">
                                            @if(!empty($api['integrations']))
                                                <span class="small text-muted me-2">→
                                                    @foreach($api['integrations'] as $int)
                                                        <a href="{{ $int['url'] }}" class="text-decoration-none">{{ $int['name'] }}</a>@if(!$loop->last), @endif
                                                    @endforeach
                                                </span>
                                            @endif
                                            <span class="integration-tps-badge {{ $api['tps'] === null ? 'integration-tps-badge--na' : '' }}">
                                                {{ $api['tps'] !== null ? number_format($api['tps'], 0).' TPS' : 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted small mb-0">No APIs owned by this system yet.</p>
                                @endforelse
                            </div>
                        @empty
                            <p class="text-muted mb-0">
                                @if($selectedSystem)
                                    No APIs owned by {{ $selectedSystem->name }} yet.
                                    <a href="{{ route('apis.create', ['system_id' => $selectedSystem->id, 'vendor_id' => $selectedSystem->vendor_id]) }}">Create an API</a>.
                                @else
                                    No systems with APIs yet. Assign vendors to systems and create APIs.
                                @endif
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.integrationTreeData = @json($treeData);
    window.integrationTreeConfig = {
        dataUrl: '{{ route("integrations.tree.data") }}',
        systemId: {{ $selectedSystem ? $selectedSystem->id : 'null' }},
        vendorId: {{ ($selectedVendor ?? null) ? $selectedVendor->id : 'null' }},
        treeBaseUrl: '{{ url("/integrations/systems") }}',
        allTreeUrl: '{{ route("integrations.tree") }}'
    };
</script>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    <script src="{{ asset('js/integration-tree.js') }}"></script>
    <script>
        document.getElementById('system-filter')?.addEventListener('change', function () {
            const id = this.value;
            window.location.href = id
                ? '{{ url("/integrations/systems") }}/' + id
                : '{{ route("integrations.tree") }}';
        });

        document.getElementById('vendor-filter')?.addEventListener('change', function () {
            const id = this.value;
            window.location.href = id
                ? '{{ route("integrations.tree") }}?vendor_id=' + id
                : '{{ route("integrations.tree") }}';
        });
    </script>
@endpush
