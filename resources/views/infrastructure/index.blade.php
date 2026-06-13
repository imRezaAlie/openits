@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .ssl-expiring { color: #e65100; }
        .ssl-expired { color: #c62828; }
    </style>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">Infrastructure</h4>
                    <small class="text-muted">Database, application, web, and other servers across all systems</small>
                </div>
                <a href="{{ route('systems.index') }}" class="btn btn-outline-primary btn-sm">Manage by System</a>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card mb-0">
                    <div class="card-body py-3">
                        <small class="text-muted d-block">Total Servers</small>
                        <strong class="fs-4">{{ $stats['total'] }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card mb-0">
                    <div class="card-body py-3">
                        <small class="text-muted d-block">SSL Expiring (30d)</small>
                        <strong class="fs-4 ssl-expiring">{{ $stats['ssl_expiring'] }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card mb-0">
                    <div class="card-body py-3">
                        <small class="text-muted d-block">SSL Expired</small>
                        <strong class="fs-4 ssl-expired">{{ $stats['ssl_expired'] }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card mb-0">
                    <div class="card-body py-3">
                        <small class="text-muted d-block">Server Types</small>
                        <strong class="fs-4">{{ $stats['by_type']->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body py-3">
                        <form method="GET" action="{{ route('infrastructure.index') }}" class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Search</label>
                                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Name, hostname, IP, location">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">System</label>
                                <select name="system_id" class="form-select form-select-sm">
                                    <option value="">All systems</option>
                                    @foreach($systems as $system)
                                        <option value="{{ $system->id }}" @selected(request('system_id') == $system->id)>{{ $system->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Type</label>
                                <select name="server_type" class="form-select form-select-sm">
                                    <option value="">All types</option>
                                    @foreach($serverTypes as $type)
                                        <option value="{{ $type }}" @selected(request('server_type') === $type)>{{ \App\Support\ServerTypes::label($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('infrastructure.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">All Servers</h5>
                        <span class="badge badge-primary">{{ $servers->count() }} server(s)</span>
                    </div>
                    <div class="card-body p-0">
                        @if($servers->isEmpty())
                            <div class="text-center py-5">
                                <p class="text-muted mb-3">No servers registered yet.</p>
                                <a href="{{ route('systems.index') }}" class="btn btn-primary btn-sm">Go to Systems to add servers</a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Name / Host</th>
                                            <th>System</th>
                                            <th>IP</th>
                                            <th>Port</th>
                                            <th>Location</th>
                                            <th>RAM</th>
                                            <th>CPU</th>
                                            <th>NIC</th>
                                            <th>SSL Issued</th>
                                            <th>SSL Expires</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($servers as $server)
                                            @php
                                                $sslClass = '';
                                                if ($server->ssl_expires_at) {
                                                    if ($server->ssl_expires_at->isPast()) {
                                                        $sslClass = 'ssl-expired';
                                                    } elseif ($server->ssl_expires_at->lte(now()->addDays(30))) {
                                                        $sslClass = 'ssl-expiring';
                                                    }
                                                }
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="badge badge-light">
                                                        <i class="{{ \App\Support\ServerTypes::icon($server->server_type) }} me-1"></i>
                                                        {{ \App\Support\ServerTypes::label($server->server_type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ $server->displayName() }}</div>
                                                    @if($server->hostname && $server->name)
                                                        <small class="text-muted">{{ $server->hostname }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($server->system)
                                                        <a href="{{ route('systems.servers', $server->system) }}">{{ $server->system->name }}</a>
                                                        @if($server->system->vendor)
                                                            <br><small class="text-muted">{{ $server->system->vendor->name }}</small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td><code>{{ $server->ip_address ?? '—' }}</code></td>
                                                <td>{{ $server->port ?? '—' }}</td>
                                                <td>{{ $server->location ?? '—' }}</td>
                                                <td>{{ $server->ram ?? '—' }}</td>
                                                <td>{{ $server->cpu ?? '—' }}</td>
                                                <td>{{ $server->nic ?? '—' }}</td>
                                                <td>
                                                    @if($server->ssl_issued_at)
                                                        <small>{{ $server->ssl_issued_at->format('M j, Y') }}</small>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="{{ $sslClass }}">
                                                    @if($server->ssl_expires_at)
                                                        <small>{{ $server->ssl_expires_at->format('M j, Y') }}</small>
                                                    @elseif($server->ssl_certificate)
                                                        <small>Configured</small>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($server->system)
                                                        <a href="{{ route('systems.servers', $server->system) }}" class="btn btn-primary btn-sm">Manage</a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
@endpush
