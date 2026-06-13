@extends('master')
@push('head-src')
    <link href="{{ asset('css/api-types.css') }}" rel="stylesheet">
    <style>
        .stat-card .icon-box { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
        .welcome-card { background: linear-gradient(135deg, var(--primary) 0%, #4a6cf7 100%); }
        .welcome-card, .welcome-card h4, .welcome-card p, .welcome-card span { color: #fff; }
        .welcome-card .btn-light { color: var(--primary); }
        .dashboard-pair .card { height: 100%; }
        .vendor-item {
            display: block;
            color: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .vendor-item:hover {
            border-color: var(--primary) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            color: inherit;
        }
        .vendor-item .icon-box {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            flex-shrink: 0;
        }
        .protocol-chip {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #fff;
        }
    </style>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card welcome-card overflow-hidden">
                    <div class="card-body py-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h4 class="heading mb-1">Welcome back, <strong>{{ auth()->user()->name }}</strong></h4>
                                <p class="mb-2 opacity-90">
                                    REST, GraphQL, gRPC, WebSocket, SSE, Socket.IO, SOAP — design, debug, test, and document
                                    all your APIs in a single workspace. No more switching between tools for each protocol.
                                </p>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @foreach (['REST', 'GraphQL', 'gRPC', 'WebSocket', 'SSE', 'Socket.IO', 'SOAP'] as $protocol)
                                        <span class="protocol-chip">{{ $protocol }}</span>
                                    @endforeach
                                </div>
                                <p class="mb-3 opacity-75 small">
                                    {{ $stats['apis'] }} documented APIs across {{ $stats['systems'] }} systems
                                    from {{ $stats['vendors'] }} vendors.
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('apis.create') }}" class="btn btn-light btn-sm">Add API</a>
                                    <a href="{{ route('integrations.tree') }}" class="btn btn-outline-light btn-sm">Integration Tree</a>
                                    <a href="{{ route('apis.index') }}" class="btn btn-outline-light btn-sm">Browse APIs</a>
                                </div>
                            </div>
                            <div class="col-lg-4 d-none d-lg-block text-end">
                                <i class="fa-solid fa-diagram-project fa-5x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">Total APIs</p>
                            <h3 class="mb-0">{{ $stats['apis'] }}</h3>
                            <small class="text-muted">
                                @forelse($stats['by_type'] as $type => $count)
                                    {{ $count }} {{ \App\Support\ApiTypes::label($type) }}@if(!$loop->last) · @endif
                                @empty
                                    No APIs yet
                                @endforelse
                            </small>
                        </div>
                        <div class="icon-box bg-primary-light">
                            <i class="fa-solid fa-code text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">Systems</p>
                            <h3 class="mb-0">{{ $stats['systems'] }}</h3>
                            <small class="text-muted">{{ $stats['integrations'] }} integration links</small>
                        </div>
                        <div class="icon-box bg-info-light">
                            <i class="fa-solid fa-server text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">Vendors</p>
                            <h3 class="mb-0">{{ $stats['vendors'] }}</h3>
                            <small class="text-muted">{{ $stats['projects'] }} projects</small>
                        </div>
                        <div class="icon-box bg-secondary-light">
                            <i class="fa-solid fa-building text-secondary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">BPMN Processes</p>
                            <h3 class="mb-0">{{ $stats['bpmns'] }}</h3>
                            <small class="text-muted">Process diagrams</small>
                        </div>
                        <div class="icon-box bg-success-light">
                            <i class="fa-solid fa-sitemap text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="heading mb-0">Recent APIs</h4>
                        <a href="{{ route('apis.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Owner System</th>
                                        <th>Vendor</th>
                                        <th>TPS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentApis as $api)
                                        <tr>
                                            <td>
                                                <a href="{{ route('apis.show', $api) }}">{{ $api->name }}</a>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $api->type_badge_class }}">
                                                    {{ $api->type_label }}
                                                </span>
                                            </td>
                                            <td>{{ $api->ownerSystem?->name ?? '—' }}</td>
                                            <td>{{ $api->ownerSystem?->vendor?->name ?? '—' }}</td>
                                            <td>
                                                @if($api->current_tps)
                                                    {{ number_format($api->current_tps) }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No APIs documented yet.
                                                <a href="{{ route('apis.create') }}">Add your first API</a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="heading mb-0">Top APIs by TPS</h4>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($topTpsApis as $index => $api)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge badge-primary me-2">{{ $index + 1 }}</span>
                                        <a href="{{ route('apis.show', $api) }}">{{ $api->name }}</a>
                                        <br>
                                        <small class="text-muted ms-4 ps-2">{{ $api->ownerSystem?->name }}</small>
                                    </div>
                                    <strong>{{ number_format($api->current_tps) }}</strong>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center py-4">No TPS metrics recorded yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row dashboard-pair">
            <div class="col-xl-6 mb-4 mb-xl-0">
                <div class="card">
                    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="heading mb-0">Systems</h4>
                        <a href="{{ route('systems.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($systems as $system)
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    @if($system->icon)
                                                        <i class="fa-solid {{ $system->icon }} text-primary me-1"></i>
                                                    @endif
                                                    {{ $system->name }}
                                                </h6>
                                                @if($system->vendor)
                                                    <small class="text-muted">{{ $system->vendor->name }}</small>
                                                @endif
                                                @if($system->system_type)
                                                    <span class="badge badge-light ms-1">{{ $system->system_type }}</span>
                                                @endif
                                            </div>
                                            <span class="badge badge-primary">{{ $system->owned_apis_count }} APIs</span>
                                        </div>
                                        <div class="mt-2">
                                            <a href="{{ route('integrations.system', $system) }}" class="small">View integrations →</a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted py-4">
                                    No systems configured.
                                    <a href="{{ route('systems.index') }}">Add a system</a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="heading mb-0">Vendors Overview</h4>
                            <small class="text-muted">Systems grouped by vendor</small>
                        </div>
                        <a href="{{ route('supplier.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                    <div class="card-body">
                        @forelse($vendors as $vendor)
                            <a href="{{ route('integrations.tree', ['vendor_id' => $vendor->id]) }}"
                               class="vendor-item border rounded p-3 mb-3 text-decoration-none d-block">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center min-w-0">
                                        <div class="icon-box bg-secondary-light">
                                            <i class="fa-solid fa-building text-secondary"></i>
                                        </div>
                                        <div class="ms-3 min-w-0">
                                            <h6 class="mb-1 text-truncate">{{ $vendor->name }}</h6>
                                            <small class="text-muted">
                                                {{ $vendor->systems_count }} {{ Str::plural('system', $vendor->systems_count) }}
                                                · {{ $vendor->apis_count }} {{ Str::plural('API', $vendor->apis_count) }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center flex-shrink-0 ms-2">
                                        <span class="badge badge-primary me-2">{{ $vendor->systems_count }}</span>
                                        <i class="fa-solid fa-chevron-right text-muted small"></i>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-center text-muted py-4">
                                No vendors yet.
                                <a href="{{ route('supplier.index') }}">Add a vendor</a>
                            </div>
                        @endforelse
                        @if($vendors->isNotEmpty())
                            <div class="pt-1">
                                <a href="{{ route('integrations.tree') }}" class="btn btn-primary btn-sm">Full Tree View</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="{{ URL::asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ URL::asset('js/custom.min.js') }}"></script>
    <script src="{{ URL::asset('js/deznav-init.js') }}"></script>
@endpush
