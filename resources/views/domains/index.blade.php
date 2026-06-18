@extends('master')

@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <link href="{{ asset('css/api-types.css') }}" rel="stylesheet">
    <style>
        .domains-hero {
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
            border: none;
        }
        .domains-hero, .domains-hero h4, .domains-hero p, .domains-hero small { color: #fff; }
        .domain-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            font-size: 0.78rem;
            font-weight: 600;
            color: #fff;
        }
        .domain-stat .icon-box {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            flex-shrink: 0;
        }
        .distribution-bar {
            display: flex;
            height: 12px;
            border-radius: 999px;
            overflow: hidden;
            background: #e9ecef;
        }
        .distribution-segment {
            transition: width 0.3s ease;
            min-width: 2px;
        }
        .domain-overview-card {
            border: 1px solid #eee;
            border-radius: 14px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .domain-overview-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08);
        }
        .domain-overview-card .card-accent {
            height: 4px;
        }
        .share-bar {
            height: 6px;
            border-radius: 999px;
            background: #f1f3f5;
            overflow: hidden;
        }
        .share-bar-fill {
            height: 100%;
            border-radius: 999px;
        }
        .cross-domain-row {
            border-left: 3px solid var(--primary);
            padding-left: 0.85rem;
        }
        .domain-legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
    </style>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Hero --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card domains-hero overflow-hidden mb-0">
                    <div class="card-body py-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h4 class="heading mb-1">Domain Landscape</h4>
                                <p class="mb-3 opacity-90">
                                    At-a-glance view of how systems and APIs are distributed across business domains.
                                    Every system belongs to exactly one domain — Enterprise, Marketing, Network, or Infrastructure.
                                </p>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @foreach($domains as $domain)
                                        <span class="domain-chip">
                                            @if($domain->icon)<i class="{{ $domain->icon }}"></i>@endif
                                            {{ $domain->name }}
                                            <span class="opacity-75">({{ $domain->systems_count }})</span>
                                        </span>
                                    @endforeach
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#domainModal" data-action="create">Add Domain</button>
                                    <a href="{{ route('systems.index') }}" class="btn btn-outline-light btn-sm">Manage Systems</a>
                                    <a href="{{ route('integrations.tree') }}" class="btn btn-outline-light btn-sm">Integration Tree</a>
                                    <a href="{{ route('integrations.catalog') }}" class="btn btn-outline-light btn-sm">Integration Catalog</a>
                                </div>
                            </div>
                            <div class="col-lg-4 d-none d-lg-block text-end">
                                <i class="fa-solid fa-shield-halved fa-5x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top stats --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
                <div class="card domain-stat h-100 mb-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted small">Domains</p>
                            <h3 class="mb-0">{{ $overview['domains'] }}</h3>
                            <small class="text-muted">Business / IT boundaries</small>
                        </div>
                        <div class="icon-box bg-secondary-light">
                            <i class="fa-solid fa-shield-halved text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
                <div class="card domain-stat h-100 mb-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted small">Systems</p>
                            <h3 class="mb-0">{{ number_format($overview['systems']) }}</h3>
                            <small class="text-muted">
                                @if($overview['unassigned_systems'])
                                    <span class="text-warning">{{ $overview['unassigned_systems'] }} unassigned</span>
                                @else
                                    All assigned to a domain
                                @endif
                            </small>
                        </div>
                        <div class="icon-box bg-primary-light">
                            <i class="fa-solid fa-server text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
                <div class="card domain-stat h-100 mb-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted small">APIs</p>
                            <h3 class="mb-0">{{ number_format($overview['apis']) }}</h3>
                            <small class="text-muted">{{ number_format($overview['integrations']) }} integration links</small>
                        </div>
                        <div class="icon-box bg-info-light">
                            <i class="fa-solid fa-code text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card domain-stat h-100 mb-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted small">Cross-Domain</p>
                            <h3 class="mb-0">{{ number_format($overview['cross_domain_integrations']) }}</h3>
                            <small class="text-muted">Integrations spanning domains</small>
                        </div>
                        <div class="icon-box bg-success-light">
                            <i class="fa-solid fa-arrows-left-right text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Distribution bar --}}
        @if($overview['systems'] > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <h6 class="mb-0">System distribution by domain</h6>
                                <small class="text-muted">{{ $overview['systems'] }} systems total</small>
                            </div>
                            <div class="distribution-bar mb-3">
                                @foreach($domains as $domain)
                                    @if($domain->systems_count > 0)
                                        <div class="distribution-segment"
                                             style="width: {{ $domain->systems_pct }}%; background: {{ $domain->color ?? '#64748b' }};"
                                             title="{{ $domain->name }}: {{ $domain->systems_count }} ({{ $domain->systems_pct }}%)"></div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($domains as $domain)
                                    <div class="d-flex align-items-center gap-2 small">
                                        <span class="domain-legend-dot" style="background: {{ $domain->color ?? '#64748b' }};"></span>
                                        <span>{{ $domain->name }}</span>
                                        <strong>{{ $domain->systems_count }}</strong>
                                        <span class="text-muted">({{ $domain->systems_pct }}%)</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            {{-- Domain cards --}}
            <div class="col-xl-8 mb-4 mb-xl-0">
                <div class="row">
                    @foreach($domains as $domain)
                        <div class="col-md-6 mb-4">
                            <div class="card domain-overview-card mb-0">
                                <div class="card-accent" style="background: {{ $domain->color ?? '#64748b' }};"></div>
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width: 44px; height: 44px; background: {{ $domain->color ?? '#64748b' }}18;">
                                            <i class="{{ $domain->icon ?? 'fa-solid fa-layer-group' }}"
                                               style="color: {{ $domain->color ?? '#64748b' }};"></i>
                                        </div>
                                        <div class="min-w-0 flex-grow-1">
                                            <h5 class="mb-0">
                                                <a href="{{ route('domains.show', $domain) }}" class="text-body text-decoration-none">{{ $domain->name }}</a>
                                            </h5>
                                            <small class="text-muted">{{ Str::limit($domain->description, 70) }}</small>
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-3 text-center">
                                        <div class="col-3">
                                            <div class="border rounded py-2">
                                                <strong class="d-block">{{ $domain->systems_count }}</strong>
                                                <small class="text-muted">Systems</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="border rounded py-2">
                                                <strong class="d-block">{{ $domain->apis_count }}</strong>
                                                <small class="text-muted">APIs</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="border rounded py-2">
                                                <strong class="d-block">{{ $domain->vendors_count }}</strong>
                                                <small class="text-muted">Vendors</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="border rounded py-2">
                                                <strong class="d-block">{{ $domain->integrations_count }}</strong>
                                                <small class="text-muted">Links</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Systems share</span>
                                            <span>{{ $domain->systems_pct }}%</span>
                                        </div>
                                        <div class="share-bar">
                                            <div class="share-bar-fill" style="width: {{ $domain->systems_pct }}%; background: {{ $domain->color ?? '#64748b' }};"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">APIs share</span>
                                            <span>{{ $domain->apis_pct }}%</span>
                                        </div>
                                        <div class="share-bar">
                                            <div class="share-bar-fill" style="width: {{ $domain->apis_pct }}%; background: {{ $domain->color ?? '#64748b' }}; opacity: 0.65;"></div>
                                        </div>
                                    </div>

                                    @if($domain->by_type->isNotEmpty())
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1">API types</small>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($domain->by_type as $type => $count)
                                                    <span class="badge badge-{{ \App\Support\ApiTypes::badgeClass($type) }}">
                                                        {{ \App\Support\ApiTypes::label($type) }} {{ $count }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($domain->top_systems->isNotEmpty())
                                        <small class="text-muted d-block mb-1">Top systems</small>
                                        <ul class="list-unstyled mb-3 small">
                                            @foreach($domain->top_systems as $system)
                                                <li class="d-flex justify-content-between py-1 border-bottom border-light">
                                                    <a href="{{ route('integrations.system', $system) }}" class="text-truncate me-2">{{ $system->name }}</a>
                                                    <span class="badge badge-primary flex-shrink-0">{{ $system->owned_apis_count }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if($domain->cross_domain_count > 0)
                                        <p class="small text-muted mb-2">
                                            <i class="fa-solid fa-arrows-left-right me-1"></i>
                                            {{ $domain->cross_domain_count }} cross-domain {{ Str::plural('integration', $domain->cross_domain_count) }}
                                        </p>
                                    @endif

                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('domains.show', $domain) }}" class="btn btn-primary btn-sm">Open</a>
                                        <a href="{{ route('systems.index', ['domain_id' => $domain->id]) }}" class="btn btn-outline-secondary btn-sm">Systems</a>
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm edit-domain"
                                                data-name="{{ $domain->name }}"
                                                data-description="{{ $domain->description }}"
                                                data-icon="{{ $domain->icon }}"
                                                data-color="{{ $domain->color }}"
                                                data-update-url="{{ route('domains.update', $domain) }}">
                                            Edit
                                        </button>
                                        @if($domain->systems_count === 0)
                                            <form action="{{ route('domains.destroy', $domain) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete domain {{ $domain->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Side panel: comparison + cross-domain --}}
            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header border-0 pb-0">
                        <h5 class="heading mb-0">Domain comparison</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Domain</th>
                                        <th class="text-end">Sys</th>
                                        <th class="text-end">APIs</th>
                                        <th class="text-end">X-dom</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($domains as $domain)
                                        <tr style="cursor: pointer;" onclick="window.location='{{ route('domains.show', $domain) }}'">
                                            <td>
                                                <span class="domain-legend-dot d-inline-block me-1" style="background: {{ $domain->color ?? '#64748b' }};"></span>
                                                {{ $domain->name }}
                                            </td>
                                            <td class="text-end">{{ $domain->systems_count }}</td>
                                            <td class="text-end">{{ $domain->apis_count }}</td>
                                            <td class="text-end">{{ $domain->cross_domain_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-0">
                    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="heading mb-0">Cross-domain integrations</h5>
                        <a href="{{ route('integrations.tree') }}" class="btn btn-sm btn-outline-primary">Tree</a>
                    </div>
                    <div class="card-body">
                        @forelse($crossDomainLinks as $link)
                            <div class="cross-domain-row mb-3 pb-3 border-bottom">
                                <a href="{{ route('apis.show', $link->api_id) }}" class="fw-semibold small d-block mb-1">{{ $link->api_name }}</a>
                                <div class="small text-muted mb-1">
                                    <span class="badge text-white" style="background: {{ $link->owner_domain_color ?? '#64748b' }};">{{ $link->owner_domain }}</span>
                                    <i class="fa-solid fa-arrow-right mx-1"></i>
                                    <span class="badge text-white" style="background: {{ $link->consumer_domain_color ?? '#64748b' }};">{{ $link->consumer_domain }}</span>
                                </div>
                                <small class="text-muted">
                                    {{ $link->owner_system }} → {{ $link->consumer_system }}
                                    · {{ \App\Support\ApiTypes::label($link->api_type) }}
                                </small>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 mb-0 small">
                                No cross-domain integrations yet. Link systems across domains via API integrations.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="domainModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="domainModalTitle">Add Domain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="domainForm" method="POST" action="{{ route('domains.store') }}">
                @csrf
                <div id="domainMethodField"></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" id="domain-name" class="form-control" required placeholder="e.g. Security, Data & Analytics" value="{{ old('name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="domain-description" class="form-control" rows="3" placeholder="Purpose and scope of this business domain">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Font Awesome class)</label>
                        <input type="text" name="icon" id="domain-icon" class="form-control" placeholder="e.g. fa-solid fa-shield-halved" value="{{ old('icon') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" name="color" id="domain-color" class="form-control form-control-color w-100" value="{{ old('color', '#4f46e5') }}">
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
            $('#domainModalTitle').text('Add Domain');
            $('#domainForm').attr('action', '{{ route("domains.store") }}');
            $('#domainMethodField').html('');
            $('#domainForm')[0].reset();
            $('#domain-color').val('#4f46e5');
        });

        $('.edit-domain').on('click', function() {
            const el = $(this);
            $('#domainModalTitle').text('Edit Domain');
            $('#domainForm').attr('action', el.data('update-url'));
            $('#domainMethodField').html('<input type="hidden" name="_method" value="PUT">');
            $('#domain-name').val(el.data('name'));
            $('#domain-description').val(el.data('description') || '');
            $('#domain-icon').val(el.data('icon') || '');
            $('#domain-color').val(el.data('color') || '#4f46e5');
            new bootstrap.Modal(document.getElementById('domainModal')).show();
        });

        @if($errors->any())
            new bootstrap.Modal(document.getElementById('domainModal')).show();
        @endif
    </script>
@endpush
