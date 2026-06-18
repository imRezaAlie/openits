@extends('master')

@push('head-src')
    <link href="{{ asset('css/c4-index.css') }}" rel="stylesheet">
@endpush

@section('body')
@php
    $c4EnabledCount = $systems->where('c4_enabled', true)->count();
@endphp
<div class="content-body c4-index-page">
    <div class="container-fluid c4-index-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h4 class="mb-1">C4 Architecture Models</h4>
                    <small class="text-muted d-block mb-2">Context → Container → Component diagrams for your systems</small>
                    <div class="c4-index-stats">
                        <span class="c4-index-stat"><strong>{{ $systems->count() }}</strong> systems</span>
                        <span class="c4-index-stat"><strong>{{ $c4EnabledCount }}</strong> C4 enabled</span>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('c4.adrs.index') }}" class="btn btn-outline-secondary btn-sm">ADRs</a>
                    <a href="{{ route('c4.tech-radar.index') }}" class="btn btn-outline-secondary btn-sm">Tech Radar</a>
                    <a href="{{ route('integrations.tree') }}" class="btn btn-outline-secondary btn-sm">Integration Tree</a>
                    <a href="{{ route('systems.index') }}" class="btn btn-outline-primary btn-sm">Manage Systems</a>
                </div>
            </div>
        </div>

        <div class="card mb-4 c4-filters-card">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label small mb-0" for="c4-domain-filter">Domain</label>
                        <select name="domain_id" id="c4-domain-filter" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All domains</option>
                            @foreach($domains as $domain)
                                <option value="{{ $domain->id }}" @selected($filters['domain_id'] === $domain->id)>{{ $domain->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="form-check">
                            <input type="checkbox" name="c4_only" value="1" class="form-check-input" id="c4-only" @checked($filters['c4_only']) onchange="this.form.submit()">
                            <label class="form-check-label small" for="c4-only">C4 enabled only</label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            @forelse($systems as $system)
                <div class="col-xl-4 col-lg-6 mb-4">
                    <div class="card h-100 c4-system-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h5 class="mb-0">{{ $system->name }}</h5>
                                @if($system->c4_enabled)
                                    <span class="badge badge-success">C4 Active</span>
                                @else
                                    <span class="badge badge-light">Not configured</span>
                                @endif
                            </div>
                            <div class="mb-2">
                                @if($system->vendor)
                                    <span class="badge badge-info">{{ $system->vendor->name }}</span>
                                @endif
                                @if($system->domain)
                                    <span class="badge badge-secondary">{{ $system->domain->name }}</span>
                                @endif
                            </div>
                            @if($system->description)
                                <p class="text-muted small mb-0">{{ Str::limit($system->description, 120) }}</p>
                            @else
                                <p class="text-muted small mb-0">No description yet.</p>
                            @endif
                            <div class="c4-system-actions d-flex flex-wrap gap-2">
                                @if($system->c4_enabled)
                                    <a href="{{ route('c4.systems.context', $system) }}" class="btn btn-primary btn-sm">Open Diagram</a>
                                    <a href="{{ route('c4.systems.containers', $system) }}" class="btn btn-outline-primary btn-sm">Containers</a>
                                    <form action="{{ route('c4.systems.sync', $system) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">Sync APIs</button>
                                    </form>
                                @else
                                    <form action="{{ route('c4.systems.enable', $system) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Enable C4</button>
                                    </form>
                                @endif
                                <a href="{{ route('systems.index') }}" class="btn btn-outline-secondary btn-sm">Manage</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border mb-0">
                        No systems found.
                        <a href="{{ route('systems.index') }}">Create a system</a> first, then enable C4 modeling.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
@endpush
