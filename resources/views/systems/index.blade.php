@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        .systems-hero {
            background: linear-gradient(135deg, var(--primary) 0%, #4a6cf7 100%);
            border: none;
        }
        .systems-hero, .systems-hero h4, .systems-hero p, .systems-hero small { color: #fff; }
        .systems-hero .hierarchy-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.25);
            font-size: 0.78rem;
            font-weight: 600;
        }
        .systems-stat .icon-box {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            flex-shrink: 0;
        }
        .vendor-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            border: 1px solid #e9ecef;
            background: #fff;
            color: inherit;
            text-decoration: none;
            font-size: 0.85rem;
            transition: border-color 0.2s, box-shadow 0.2s, color 0.2s;
        }
        .vendor-pill:hover, .vendor-pill.active {
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.12);
            color: var(--primary);
        }
        .vendor-pill .count {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            border-radius: 999px;
            padding: 0.1rem 0.45rem;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .system-card {
            border: 1px solid #eee;
            border-radius: 14px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
            height: 100%;
        }
        .system-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08);
            border-color: rgba(79, 70, 229, 0.25);
        }
        .system-card-header {
            padding: 1.1rem 1.25rem 0.85rem;
            background: linear-gradient(180deg, rgba(79, 70, 229, 0.06) 0%, transparent 100%);
            border-bottom: 1px solid #f1f3f5;
        }
        .system-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(79, 70, 229, 0.12);
            color: var(--primary);
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .system-metrics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin: 0.85rem 0;
        }
        .system-metric {
            text-align: center;
            padding: 0.55rem 0.35rem;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .system-metric strong {
            display: block;
            font-size: 1.05rem;
            line-height: 1.2;
        }
        .system-metric span {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #737B8B;
        }
        .system-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.45rem;
        }
        .system-actions a {
            font-size: 0.78rem;
            padding: 0.45rem 0.55rem;
            text-align: center;
            border-radius: 8px;
        }
        .tech-stack-row {
            min-height: 1.75rem;
        }
        .vendor-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #737B8B;
            margin-bottom: 0.85rem;
        }
        .vendor-pills-scroll {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.25rem;
            -webkit-overflow-scrolling: touch;
        }
        .vendor-pills-scroll .vendor-pill { flex-shrink: 0; }
        .view-toggle .btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .systems-table thead th {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #737B8B;
            white-space: nowrap;
        }
        .system-icon-sm {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            flex-shrink: 0;
        }
        .results-bar {
            font-size: 0.85rem;
            color: #737B8B;
        }
    </style>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Hero --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card systems-hero overflow-hidden">
                    <div class="card-body py-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h4 class="heading mb-2">Systems Landscape</h4>
                                <p class="mb-3 opacity-90 small">
                                    Map vendors to systems — each system owns APIs, BPMN processes, and a tech stack.
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="hierarchy-chip"><i class="fa-solid fa-building"></i> Vendor</span>
                                    <span class="hierarchy-chip opacity-75"><i class="fa-solid fa-chevron-right small"></i></span>
                                    <span class="hierarchy-chip"><i class="fa-solid fa-server"></i> System</span>
                                    <span class="hierarchy-chip opacity-75"><i class="fa-solid fa-chevron-right small"></i></span>
                                    <span class="hierarchy-chip"><i class="fa-solid fa-code"></i> APIs</span>
                                    <span class="hierarchy-chip"><i class="fa-solid fa-sitemap"></i> Processes</span>
                                    <span class="hierarchy-chip"><i class="fa-solid fa-layer-group"></i> Tech Stack</span>
                                </div>
                            </div>
                            <div class="col-lg-4 d-flex justify-content-lg-end gap-2 flex-wrap mt-3 mt-lg-0">
                                <a href="{{ route('integrations.tree') }}" class="btn btn-outline-light btn-sm">Integration Tree</a>
                                <a href="{{ route('supplier.index') }}" class="btn btn-outline-light btn-sm">Vendors</a>
                                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#systemModal" data-action="create">
                                    Add System
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
                <div class="card systems-stat h-100 mb-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted small">Systems</p>
                            <h3 class="mb-0">{{ number_format($stats['systems_total']) }}</h3>
                            <small class="text-muted">{{ $vendors->count() }} vendors</small>
                        </div>
                        <div class="icon-box bg-primary-light">
                            <i class="fa-solid fa-server text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
                <div class="card systems-stat h-100 mb-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted small">Owned APIs</p>
                            <h3 class="mb-0">{{ number_format($stats['apis_total']) }}</h3>
                            <small class="text-muted">Across all systems</small>
                        </div>
                        <div class="icon-box bg-info-light">
                            <i class="fa-solid fa-code text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
                <div class="card systems-stat h-100 mb-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted small">Processes</p>
                            <h3 class="mb-0">{{ number_format($stats['processes_total']) }}</h3>
                            <small class="text-muted">BPMN diagrams</small>
                        </div>
                        <div class="icon-box bg-success-light">
                            <i class="fa-solid fa-sitemap text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card systems-stat h-100 mb-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted small">Tech Items</p>
                            <h3 class="mb-0">{{ number_format($stats['technologies_total']) }}</h3>
                            <small class="text-muted">Across stacks</small>
                        </div>
                        <div class="icon-box bg-secondary-light">
                            <i class="fa-solid fa-layer-group text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('systems.index') }}" class="row g-3 align-items-end" id="systems-filter-form">
                            <input type="hidden" name="view" value="{{ $viewMode }}">
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name, type, description..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Domain</label>
                                <select name="domain_id" class="form-control">
                                    <option value="">All domains</option>
                                    @foreach($domains as $domain)
                                        <option value="{{ $domain->id }}" @selected(request('domain_id') == $domain->id)>{{ $domain->name }} ({{ $domain->systems_count }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Vendor</label>
                                <select name="vendor_id" class="form-control">
                                    <option value="">All vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->name }} ({{ $vendor->systems_count }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1">Per page</label>
                                <select name="per_page" class="form-control">
                                    @foreach([15, 30, 50, 100] as $size)
                                        <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">Apply</button>
                                @if(request()->hasAny(['vendor_id', 'domain_id', 'search', 'per_page']))
                                    <a href="{{ route('systems.index', ['view' => $viewMode]) }}" class="btn btn-light">Clear</a>
                                @endif
                            </div>
                            @if(request('vendor_id'))
                                <div class="col-12">
                                    <a href="{{ route('integrations.tree', ['vendor_id' => request('vendor_id')]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-diagram-project me-1"></i> View vendor integration tree
                                    </a>
                                </div>
                            @endif
                        </form>

                        @if($domains->isNotEmpty())
                            <div class="mt-3 pt-3 border-top">
                                <p class="small text-muted mb-2 mb-md-0 d-inline-block me-2">Domains:</p>
                                <div class="vendor-pills-scroll d-inline-flex flex-wrap">
                                    <a href="{{ route('systems.index', array_filter(['search' => request('search'), 'vendor_id' => request('vendor_id'), 'view' => $viewMode, 'per_page' => $perPage])) }}"
                                       class="vendor-pill @if(!request('domain_id')) active @endif">
                                        All <span class="count">{{ $stats['systems_total'] }}</span>
                                    </a>
                                    @foreach($domains as $domain)
                                        <a href="{{ route('systems.index', array_filter(['domain_id' => $domain->id, 'search' => request('search'), 'vendor_id' => request('vendor_id'), 'view' => $viewMode, 'per_page' => $perPage])) }}"
                                           class="vendor-pill @if(request('domain_id') == $domain->id) active @endif"
                                           @if($domain->color) style="--pill-accent: {{ $domain->color }}" @endif>
                                            @if($domain->icon)<i class="{{ $domain->icon }} me-1"></i>@endif
                                            {{ $domain->name }}
                                            <span class="count">{{ $domain->systems_count }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($vendors->isNotEmpty())
                            <div class="mt-3 pt-3 border-top">
                                <p class="small text-muted mb-2 mb-md-0 d-inline-block me-2">Vendors:</p>
                                <div class="vendor-pills-scroll d-inline-flex flex-wrap">
                                    <a href="{{ route('systems.index', array_filter(['search' => request('search'), 'view' => $viewMode, 'per_page' => $perPage])) }}"
                                       class="vendor-pill @if(!request('vendor_id')) active @endif">
                                        All <span class="count">{{ $stats['systems_total'] }}</span>
                                    </a>
                                    @foreach($vendors as $vendor)
                                        <a href="{{ route('systems.index', array_filter(['vendor_id' => $vendor->id, 'search' => request('search'), 'view' => $viewMode, 'per_page' => $perPage])) }}"
                                           class="vendor-pill @if(request('vendor_id') == $vendor->id) active @endif">
                                            {{ $vendor->name }}
                                            <span class="count">{{ $vendor->systems_count }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Results toolbar --}}
        @if($systems->total() > 0)
            <div class="row mb-3 align-items-center">
                <div class="col-md-6 results-bar mb-2 mb-md-0">
                    Showing <strong>{{ $systems->firstItem() }}–{{ $systems->lastItem() }}</strong>
                    of <strong>{{ number_format($systems->total()) }}</strong> systems
                    @if(request('vendor_id') || request('domain_id') || request('search'))
                        <span class="text-muted">(filtered from {{ number_format($stats['systems_total']) }})</span>
                    @endif
                </div>
                <div class="col-md-6 d-flex justify-content-md-end">
                    <div class="btn-group btn-group-sm view-toggle" role="group">
                        @php
                            $toggleParams = array_filter([
                                'search' => request('search'),
                                'vendor_id' => request('vendor_id'),
                                'domain_id' => request('domain_id'),
                                'per_page' => $perPage,
                            ]);
                        @endphp
                        <a href="{{ route('systems.index', array_merge($toggleParams, ['view' => 'table'])) }}"
                           class="btn btn-outline-primary @if($viewMode === 'table') active @endif">
                            <i class="fa-solid fa-table-list me-1"></i> Table
                        </a>
                        <a href="{{ route('systems.index', array_merge($toggleParams, ['view' => 'grid'])) }}"
                           class="btn btn-outline-primary @if($viewMode === 'grid') active @endif">
                            <i class="fa-solid fa-grip me-1"></i> Grid
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Systems list --}}
        @if($systems->count())
            @if($viewMode === 'table')
                @include('systems._table')
            @else
                <div class="row">
                    @foreach($systems as $system)
                        @include('systems._card')
                    @endforeach
                </div>
            @endif

            <div class="row mt-2">
                <div class="col-12 d-flex justify-content-center">
                    {{ $systems->links() }}
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fa-solid fa-server fa-3x text-muted mb-3 opacity-50"></i>
                            <h5 class="mb-2">No systems found</h5>
                            <p class="text-muted mb-3">
                                @if(request()->hasAny(['vendor_id', 'domain_id', 'search']))
                                    Try adjusting your filters or create a new system.
                                @else
                                    Create a vendor first, then add systems to map your integration landscape.
                                @endif
                            </p>
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary btn-sm">Manage Vendors</a>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#systemModal" data-action="create">
                                    Add System
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="systemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="systemModalTitle">Add System</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="systemForm" method="POST" action="{{ route('systems.store') }}">
                @csrf
                <div id="systemMethodField"></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Domain *</label>
                        <select name="domain_id" id="system-domain" class="form-control" required>
                            <option value="">— Select domain —</option>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id }}">{{ $domain->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vendor</label>
                        <select name="vendor_id" id="system-vendor" class="form-control">
                            <option value="">— Select vendor —</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" id="system-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="system-description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">System Type</label>
                        <input type="text" name="system_type" id="system-type" class="form-control" placeholder="e.g. CRM, ERP, Payment Gateway">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Font Awesome class)</label>
                        <input type="text" name="icon" id="system-icon" class="form-control" placeholder="e.g. fa-users">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent System</label>
                        <select name="parent_system_id" id="system-parent" class="form-control">
                            <option value="">None (root level)</option>
                            @foreach($allSystemsForSelect as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}@if($s->vendor) ({{ $s->vendor->name }})@endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    <script>
        $('[data-action="create"]').on('click', function() {
            $('#systemModalTitle').text('Add System');
            $('#systemForm').attr('action', '{{ route("systems.store") }}');
            $('#systemMethodField').html('');
            $('#systemForm')[0].reset();
            @if(request('domain_id'))
                $('#system-domain').val('{{ request('domain_id') }}');
            @endif
            @if(request('vendor_id'))
                $('#system-vendor').val('{{ request('vendor_id') }}');
            @endif
        });

        $(document).on('click', '.edit-system', function(e) {
            e.preventDefault();
            const el = $(this);
            $('#systemModalTitle').text('Edit System');
            $('#systemForm').attr('action', el.data('update-url'));
            $('#systemMethodField').html('<input type="hidden" name="_method" value="PUT">');
            $('#system-vendor').val(el.data('vendor') || '');
            $('#system-domain').val(el.data('domain') || '');
            $('#system-name').val(el.data('name'));
            $('#system-description').val(el.data('description'));
            $('#system-type').val(el.data('system-type'));
            $('#system-icon').val(el.data('icon'));
            $('#system-parent').val(el.data('parent') || '');
            new bootstrap.Modal(document.getElementById('systemModal')).show();
        });
    </script>
@endpush
