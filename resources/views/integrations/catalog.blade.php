@extends('master')
@push('head-src')
    <link href="{{ asset('css/api-types.css') }}" rel="stylesheet">
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">Integration Catalog</h4>
                    <small class="text-muted">Tabular view of all cross-system integration links across the enterprise landscape</small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('integrations.tree') }}" class="btn btn-outline-secondary btn-sm">Tree View</a>
                    <a href="{{ route('integrations.catalog.export', array_merge($filters, ['format' => 'csv'])) }}" class="btn btn-outline-primary btn-sm">Export CSV</a>
                    <a href="{{ route('integrations.catalog.export', $filters) }}" class="btn btn-outline-primary btn-sm" target="_blank">Export JSON</a>
                    <a href="{{ route('integrations.export') }}" class="btn btn-primary btn-sm" target="_blank">Full Landscape JSON</a>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total links</p>
                        <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Cross-domain</p>
                        <h3 class="mb-0">{{ number_format($stats['cross_domain']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-2">By protocol</p>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse($stats['by_type'] as $type => $count)
                                <span class="badge badge-{{ \App\Support\ApiTypes::badgeClass($type) }}">
                                    {{ \App\Support\ApiTypes::label($type) }} {{ $count }}
                                </span>
                            @empty
                                <span class="text-muted small">No integrations</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('integrations.catalog') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Domain</label>
                        <select name="domain_id" class="form-control form-control-sm">
                            <option value="">All domains</option>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id }}" @selected($filters['domain_id'] == $domain->id)>{{ $domain->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Vendor</label>
                        <select name="vendor_id" class="form-control form-control-sm">
                            <option value="">All vendors</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @selected($filters['vendor_id'] == $vendor->id)>{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Protocol</label>
                        <select name="api_type" class="form-control form-control-sm">
                            <option value="">All protocols</option>
                            @foreach($apiTypes as $type)
                                <option value="{{ $type }}" @selected($filters['api_type'] === $type)>{{ \App\Support\ApiTypes::label($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="{{ route('integrations.catalog') }}" class="btn btn-light btn-sm">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>API / Integration</th>
                                <th>Provider (Owner)</th>
                                <th>Consumer</th>
                                <th>Scope</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($links as $link)
                                <tr>
                                    <td>
                                        <a href="{{ route('apis.show', $link->api_id) }}" class="fw-semibold text-body text-decoration-none">{{ $link->api_name }}</a>
                                        <div>
                                            <span class="badge badge-{{ \App\Support\ApiTypes::badgeClass($link->api_type) }}">
                                                {{ $link->api_type_label }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('integrations.system', $link->owner_system_id) }}" class="text-body text-decoration-none">{{ $link->owner_system }}</a>
                                        <div class="small text-muted">
                                            {{ $link->owner_vendor ?? '—' }}
                                            @if($link->owner_domain)
                                                · <span class="badge text-white" style="background: {{ $link->owner_domain_color ?? '#64748b' }};">{{ $link->owner_domain }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('integrations.system', $link->consumer_system_id) }}" class="text-body text-decoration-none">{{ $link->consumer_system }}</a>
                                        <div class="small text-muted">
                                            {{ $link->consumer_vendor ?? '—' }}
                                            @if($link->consumer_domain)
                                                · <span class="badge text-white" style="background: {{ $link->consumer_domain_color ?? '#64748b' }};">{{ $link->consumer_domain }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($link->cross_domain)
                                            <span class="badge badge-warning">Cross-domain</span>
                                        @else
                                            <span class="badge badge-light">Same domain</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        No integration links match your filters.
                                        <a href="{{ route('apis.index') }}">Document an API</a> and link consumer systems.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
@endpush
