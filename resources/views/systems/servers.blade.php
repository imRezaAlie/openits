@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <style>
        .server-type-badge {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .ssl-expiring {
            color: #e65100;
        }
        .ssl-expired {
            color: #c62828;
        }
    </style>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
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

        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0 py-0">
                            <li class="breadcrumb-item"><a href="{{ route('systems.index') }}">Systems</a></li>
                            @if($system->vendor)
                                <li class="breadcrumb-item">
                                    <a href="{{ route('systems.index', ['vendor_id' => $system->vendor_id]) }}">{{ $system->vendor->name }}</a>
                                </li>
                            @endif
                            <li class="breadcrumb-item active">{{ $system->name }} — Servers</li>
                        </ol>
                    </nav>
                    <h4 class="mb-0">{{ $system->name }} — Infrastructure Servers</h4>
                    <small class="text-muted">Database, application, web, and other servers with network and SSL details</small>
                    <div class="mt-1">
                        @if($system->vendor)
                            <span class="badge badge-info">{{ $system->vendor->name }}</span>
                        @endif
                        @if($system->system_type)
                            <span class="badge badge-light">{{ $system->system_type }}</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('infrastructure.index') }}" class="btn btn-outline-secondary btn-sm">All Infrastructure</a>
                    <a href="{{ route('systems.index') }}" class="btn btn-outline-secondary btn-sm">Back to Systems</a>
                    <a href="{{ route('systems.technologies', $system) }}" class="btn btn-outline-secondary btn-sm">Tech Stack</a>
                    <a href="{{ route('systems.processes', $system) }}" class="btn btn-outline-info btn-sm">Processes</a>
                    <a href="{{ route('integrations.system', $system) }}" class="btn btn-outline-primary btn-sm">Integrations</a>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#serverModal" id="addServerBtn">
                        Add Server
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Server Inventory</h5>
                        <span class="badge badge-primary">{{ $system->servers->count() }} server(s)</span>
                    </div>
                    <div class="card-body p-0">
                        @if($system->servers->isEmpty())
                            <div class="text-center py-5">
                                <p class="text-muted mb-3">No servers registered for this system yet.</p>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#serverModal">Add first server</button>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Name / Host</th>
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
                                        @foreach($system->servers as $server)
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
                                                    <span class="badge badge-light server-type-badge">
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
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button"
                                                                class="btn btn-outline-primary edit-server"
                                                                title="Edit"
                                                                data-id="{{ $server->id }}"
                                                                data-name="{{ $server->name }}"
                                                                data-server-type="{{ $server->server_type }}"
                                                                data-hostname="{{ $server->hostname }}"
                                                                data-ip="{{ $server->ip_address }}"
                                                                data-port="{{ $server->port }}"
                                                                data-location="{{ $server->location }}"
                                                                data-ram="{{ $server->ram }}"
                                                                data-cpu="{{ $server->cpu }}"
                                                                data-nic="{{ $server->nic }}"
                                                                data-ssl-certificate="{{ $server->ssl_certificate }}"
                                                                data-ssl-issued="{{ $server->ssl_issued_at?->format('Y-m-d') }}"
                                                                data-ssl-expires="{{ $server->ssl_expires_at?->format('Y-m-d') }}"
                                                                data-notes="{{ $server->notes }}">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                        <form action="{{ route('systems.servers.destroy', [$system, $server]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this server?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @if($server->ssl_certificate || $server->notes)
                                                <tr class="table-light">
                                                    <td colspan="11" class="py-2 px-3">
                                                        @if($server->ssl_certificate)
                                                            <small class="text-muted d-block"><strong>SSL:</strong> {{ Str::limit($server->ssl_certificate, 200) }}</small>
                                                        @endif
                                                        @if($server->notes)
                                                            <small class="text-muted d-block"><strong>Notes:</strong> {{ $server->notes }}</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
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

<div class="modal fade" id="serverModal" tabindex="-1" aria-labelledby="serverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="serverForm" method="POST" action="{{ route('systems.servers.store', $system) }}">
                @csrf
                <input type="hidden" name="_method" id="serverFormMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="serverModalLabel">Add Server</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Server type <span class="text-danger">*</span></label>
                            <select name="server_type" id="server_type" class="form-select" required>
                                @foreach($serverTypes as $type)
                                    <option value="{{ $type }}">{{ \App\Support\ServerTypes::label($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Display name</label>
                            <input type="text" name="name" id="server_name" class="form-control" placeholder="e.g. Primary DB">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hostname</label>
                            <input type="text" name="hostname" id="server_hostname" class="form-control" placeholder="db01.example.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">IP address</label>
                            <input type="text" name="ip_address" id="server_ip" class="form-control" placeholder="10.0.1.15">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Port</label>
                            <input type="number" name="port" id="server_port" class="form-control" min="1" max="65535" placeholder="5432">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" id="server_location" class="form-control" placeholder="DC-East / us-east-1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">RAM</label>
                            <input type="text" name="ram" id="server_ram" class="form-control" placeholder="32 GB">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">CPU</label>
                            <input type="text" name="cpu" id="server_cpu" class="form-control" placeholder="8 vCPU">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">NIC (network interface)</label>
                            <input type="text" name="nic" id="server_nic" class="form-control" placeholder="eth0 — 1 Gbps">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">SSL certificate</label>
                            <textarea name="ssl_certificate" id="server_ssl_certificate" class="form-control" rows="2" placeholder="CN, issuer, or certificate reference"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SSL issue date</label>
                            <input type="date" name="ssl_issued_at" id="server_ssl_issued" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SSL expiry date</label>
                            <input type="date" name="ssl_expires_at" id="server_ssl_expires" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="server_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="serverSubmitBtn">Save Server</button>
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
        const storeUrl = @json(route('systems.servers.store', $system));
        const updateUrlTemplate = @json(route('systems.servers.update', [$system, '__SERVER__']));
        const modal = document.getElementById('serverModal');
        const form = document.getElementById('serverForm');
        const methodInput = document.getElementById('serverFormMethod');
        const modalTitle = document.getElementById('serverModalLabel');

        function resetServerForm() {
            form.action = storeUrl;
            methodInput.value = 'POST';
            modalTitle.textContent = 'Add Server';
            form.reset();
        }

        document.getElementById('addServerBtn')?.addEventListener('click', resetServerForm);
        modal?.addEventListener('hidden.bs.modal', resetServerForm);

        document.querySelectorAll('.edit-server').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                form.action = updateUrlTemplate.replace('__SERVER__', id);
                methodInput.value = 'PUT';
                modalTitle.textContent = 'Edit Server';

                document.getElementById('server_type').value = this.dataset.serverType || 'other';
                document.getElementById('server_name').value = this.dataset.name || '';
                document.getElementById('server_hostname').value = this.dataset.hostname || '';
                document.getElementById('server_ip').value = this.dataset.ip || '';
                document.getElementById('server_port').value = this.dataset.port || '';
                document.getElementById('server_location').value = this.dataset.location || '';
                document.getElementById('server_ram').value = this.dataset.ram || '';
                document.getElementById('server_cpu').value = this.dataset.cpu || '';
                document.getElementById('server_nic').value = this.dataset.nic || '';
                document.getElementById('server_ssl_certificate').value = this.dataset.sslCertificate || '';
                document.getElementById('server_ssl_issued').value = this.dataset.sslIssued || '';
                document.getElementById('server_ssl_expires').value = this.dataset.sslExpires || '';
                document.getElementById('server_notes').value = this.dataset.notes || '';

                bootstrap.Modal.getOrCreateInstance(modal).show();
            });
        });
    </script>
@endpush
