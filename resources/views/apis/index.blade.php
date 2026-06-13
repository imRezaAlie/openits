@extends('master')
@push('head-src')
    <link href="{{ asset('vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/api-types.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        .system-badge { font-size: 0.75rem; margin-right: 4px; margin-bottom: 4px; }
        #apis-table td.cell-wrap {
            white-space: normal !important;
            min-width: 8rem;
        }
        #apis-table td.actions-cell {
            white-space: nowrap;
        }
    </style>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0">API Documentation</h4>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('integrations.tree') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-sitemap me-1"></i> Tree View
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                            Import Swagger/WSDL
                        </button>
                        <a href="{{ route('apis.create') }}" class="btn btn-primary btn-sm">Add Integration</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('apis.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="vendor_id" class="form-control">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="type" class="form-control">
                                    <option value="">All Types</option>
                                    <optgroup label="{{ \App\Support\ApiTypes::GROUP_API }}">
                                        @foreach(\App\Support\ApiTypes::API_PROTOCOLS as $apiType)
                                            <option value="{{ $apiType }}" @selected(request('type') === $apiType)>{{ \App\Support\ApiTypes::label($apiType) }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="{{ \App\Support\ApiTypes::GROUP_NON_API }}">
                                        @foreach(\App\Support\ApiTypes::NON_API_INTEGRATIONS as $apiType)
                                            <option value="{{ $apiType }}" @selected(request('type') === $apiType)>{{ \App\Support\ApiTypes::label($apiType) }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="system_id" class="form-control">
                                    <option value="">All Systems</option>
                                    @foreach($systems as $system)
                                        <option value="{{ $system->id }}" @selected(request('system_id') == $system->id)>
                                            {{ $system->name }}@if($system->vendor) ({{ $system->vendor->name }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                @if(request('system_id'))
                                    <a href="{{ route('integrations.system', request('system_id')) }}" class="small d-block mt-1">View integration tree →</a>
                                @endif
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body px-0">
                        <div class="table-responsive active-projects user-tbl dt-filter">
                            <table id="apis-table" class="table shorting">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Vendor</th>
                                        <th>Type</th>
                                        <th>Versions</th>
                                        <th>Owner System</th>
                                        <th>Integrations</th>
                                        <th>Current TPS</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($apis as $api)
                                        @php
                                            $ownerSystem = $api->resolvedOwnerSystem();
                                            $additionalSystems = $api->additionalSystems();
                                        @endphp
                                        <tr>
                                            <td class="cell-wrap">
                                                <a href="{{ route('apis.show', $api) }}">{{ $api->name }}</a>
                                                @if($api->defaultVersion?->endpoint_url)
                                                    <br><small class="text-muted">{{ Str::limit($api->defaultVersion->endpoint_url, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($ownerSystem?->vendor)
                                                    <a href="{{ route('integrations.tree', ['vendor_id' => $ownerSystem->vendor_id]) }}" class="badge badge-info system-badge text-decoration-none">{{ $ownerSystem->vendor->name }}</a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $api->type_badge_class }}">
                                                    {{ $api->type_label }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($api->versions->isNotEmpty())
                                                    <span class="badge badge-light">{{ $api->versions->count() }} version{{ $api->versions->count() === 1 ? '' : 's' }}</span>
                                                    @if($api->defaultVersion)
                                                        <br><small class="text-muted">v{{ $api->defaultVersion->version }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($ownerSystem)
                                                    <a href="{{ route('integrations.system', $ownerSystem) }}" class="badge badge-primary system-badge text-decoration-none">{{ $ownerSystem->name }}</a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="cell-wrap">
                                                @forelse($additionalSystems as $system)
                                                    <a href="{{ route('integrations.system', $system) }}" class="badge badge-light system-badge text-decoration-none" title="{{ $system->vendor?->name }}">{{ $system->name }}</a>
                                                @empty
                                                    <span class="text-muted">—</span>
                                                @endforelse
                                            </td>
                                            <td>
                                                @if($api->latestTps)
                                                    <span class="badge badge-primary">{{ number_format($api->latestTps->tps_value, 0) }} TPS</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="actions-cell">
                                                <div class="dropdown position-static">
                                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="{{ route('apis.show', $api) }}">View</a>
                                                        <a class="dropdown-item" href="{{ route('apis.edit', $api) }}">Edit</a>
                                                        <form action="{{ route('apis.destroy', $api) }}" method="POST" onsubmit="return confirm('Delete this API?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">No APIs found. Import or create one to get started.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('apis._import_modal')
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    <script src="{{ asset('js/api-import.js') }}"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        $(document).ready(function () {
            if ($('#apis-table tbody tr').length > 0 && !$('#apis-table tbody tr td[colspan]').length) {
                $('#apis-table').DataTable({
                    order: [[0, 'asc']],
                    pageLength: 25,
                    searching: false,
                    lengthChange: false,
                    dom: 'rtip',
                    columnDefs: [
                        { orderable: false, targets: -1 },
                        { className: 'cell-wrap', targets: [0, 4] },
                    ],
                    language: {
                        paginate: {
                            next: '<i class="fa-solid fa-angle-right"></i>',
                            previous: '<i class="fa-solid fa-angle-left"></i>',
                        },
                    },
                });
            }
        });
    </script>
@endpush
