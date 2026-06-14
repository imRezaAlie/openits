@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="{{ asset('css/swagger-spec.css') }}" rel="stylesheet">
    <link href="{{ asset('css/api-types.css') }}" rel="stylesheet">
    @if($api->isRest())
        <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css">
    @endif
@endpush

@section('body')
<div class="content-body swagger-spec-page">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-1">{{ $api->name }}</h4>
                    <span class="badge badge-{{ $api->type_badge_class }}">{{ $api->type_label }}</span>
                    <span class="badge badge-{{ $activeVersion->status_badge_class }} ms-1">v{{ $activeVersion->version }}</span>
                    @if($activeVersion->is_default)
                        <span class="badge badge-primary ms-1">Default</span>
                    @endif
                    <span class="badge badge-light ms-1">{{ ucfirst($activeVersion->status) }}</span>
                    @if($api->isNonApiIntegration())
                        <span class="badge badge-light ms-1">Non-API</span>
                    @endif
                    @if($api->ownerSystem)
                        <span class="badge badge-primary ms-1">{{ $api->ownerSystem->name }}</span>
                        @if($api->ownerSystem->vendor)
                            <a href="{{ route('integrations.tree', ['vendor_id' => $api->ownerSystem->vendor_id]) }}" class="badge badge-info ms-1 text-decoration-none">{{ $api->ownerSystem->vendor->name }}</a>
                        @endif
                    @endif
                    @if($api->latestTps)
                        <span class="badge badge-primary ms-1">{{ number_format($api->latestTps->tps_value, 0) }} TPS</span>
                    @endif
                </div>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    @if($api->versions->count() > 1)
                        <select id="version-switcher" class="form-control form-control-sm" style="width: auto; min-width: 8rem;">
                            @foreach($api->versions as $v)
                                <option value="{{ route('apis.show', ['api' => $api, 'version' => $v->id]) }}"
                                    @selected((int) $v->id === (int) $activeVersion->id)>
                                    v{{ $v->version }}@if($v->is_default) (default)@endif
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <a href="{{ route('apis.spec', ['api' => $api, 'version' => $activeVersion->id]) }}" class="btn btn-outline-info btn-sm" target="_blank">Raw Spec JSON</a>
                    <a href="{{ route('apis.edit', ['api' => $api, 'version' => $activeVersion->id]) }}" class="btn btn-primary btn-sm">Edit</a>
                    <a href="{{ route('apis.index') }}" class="btn btn-outline-secondary btn-sm">Back to List</a>
                </div>
            </div>
        </div>

        @if($activeVersion->endpoint_url)
            <div class="row mb-3">
                <div class="col-12">
                    <small class="text-muted">Endpoint:</small>
                    <code class="ms-1">{{ $activeVersion->endpoint_url }}</code>
                </div>
            </div>
        @endif

        <ul class="nav nav-tabs api-spec-tabs mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-spec" role="tab">
                    {{ $api->isNonApiIntegration() ? 'Connection Details' : 'API Specification' }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-versions" role="tab">
                    Versions ({{ $api->versions->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-metrics" role="tab">
                    TPS &amp; Integrations
                </a>
            </li>
        </ul>

        <div class="tab-content">
            {{-- Swagger UI spec tab --}}
            <div class="tab-pane fade show active" id="tab-spec" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        @if($api->isRest())
                            <div class="card swagger-ui-container spec-light-panel">
                                <div class="card-body p-0">
                                    <div id="swagger-ui"></div>
                                </div>
                            </div>
                        @elseif($api->isSoap())
                            <div class="card spec-light-panel">
                                <div class="card-body p-0">
                                    @include('apis._soap_spec', ['soapSpec' => $soapSpec])
                                </div>
                            </div>
                        @else
                            <div class="card spec-light-panel">
                                <div class="card-body p-0">
                                    @include('apis._protocol_spec', ['protocolSpec' => $protocolSpec])
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Versions tab --}}
            <div class="tab-pane fade" id="tab-versions" role="tabpanel">
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">All Versions</h5></div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Version</th>
                                                <th>Status</th>
                                                <th>Endpoint</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($api->versions as $v)
                                                <tr @if((int) $v->id === (int) $activeVersion->id) class="table-active" @endif>
                                                    <td>
                                                        <strong>v{{ $v->version }}</strong>
                                                        @if($v->is_default)
                                                            <span class="badge badge-primary ms-1">Default</span>
                                                        @endif
                                                    </td>
                                                    <td><span class="badge badge-{{ $v->status_badge_class }}">{{ ucfirst($v->status) }}</span></td>
                                                    <td><small class="text-muted">{{ Str::limit($v->endpoint_url, 60) ?: '—' }}</small></td>
                                                    <td class="text-nowrap">
                                                        <a href="{{ route('apis.show', ['api' => $api, 'version' => $v->id]) }}" class="btn btn-xs btn-outline-primary btn-sm">View</a>
                                                        <a href="{{ route('apis.edit', ['api' => $api, 'version' => $v->id]) }}" class="btn btn-xs btn-outline-secondary btn-sm">Edit</a>
                                                        @if(! $v->is_default)
                                                            <form action="{{ route('apis.versions.setDefault', [$api, $v]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-xs btn-outline-info btn-sm">Set Default</button>
                                                            </form>
                                                        @endif
                                                        @if($api->versions->count() > 1)
                                                            <form action="{{ route('apis.versions.destroy', [$api, $v]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete version {{ $v->version }}?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-xs btn-outline-danger btn-sm">Delete</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Add Version</h5></div>
                            <div class="card-body">
                                <form action="{{ route('apis.versions.store', $api) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Version Label</label>
                                        <input type="text" name="version" class="form-control" required placeholder="e.g. 2.0.0">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Endpoint URL</label>
                                        <input type="text" name="endpoint_url" class="form-control" placeholder="https://api.example.com/v2/...">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-control">
                                            <option value="active">Active</option>
                                            <option value="draft">Draft</option>
                                            <option value="deprecated">Deprecated</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" name="is_default" value="1" class="form-check-input" id="new-version-default">
                                        <label class="form-check-label" for="new-version-default">Set as default version</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Create Version</button>
                                </form>
                                <p class="text-muted small mt-3 mb-0">New versions start with connection metadata only. Edit the version to add protocol-specific details.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Metrics tab --}}
            <div class="tab-pane fade" id="tab-metrics" role="tabpanel">
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">TPS History</h5></div>
                            <div class="card-body">
                                <canvas id="tpsChart" height="100"></canvas>
                                <div class="table-responsive mt-4">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr><th>Recorded At</th><th>TPS Value</th><th>Notes</th></tr>
                                        </thead>
                                        <tbody>
                                            @forelse($api->tpsMetrics as $metric)
                                                <tr>
                                                    <td>{{ $metric->recorded_at->format('M d, Y H:i') }}</td>
                                                    <td><strong>{{ number_format($metric->tps_value, 2) }}</strong></td>
                                                    <td>{{ $metric->notes ?? '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-muted text-center">No TPS records yet</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">Owner &amp; Integrations</h5></div>
                            <div class="card-body">
                                @if($api->ownerSystem)
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Owner System</small>
                                        <strong>{{ $api->ownerSystem->name }}</strong>
                                        @if($api->ownerSystem->vendor)
                                            <span class="badge badge-info ms-1">{{ $api->ownerSystem->vendor->name }}</span>
                                        @endif
                                    </div>
                                @endif
                                <small class="text-muted d-block mb-2">Integrated Systems</small>
                                <div id="linked-systems" class="mb-3">
                                    @forelse($api->integratedSystems() as $system)
                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                            <span>
                                                {{ $system->name }}
                                                @if($system->vendor)
                                                    <small class="text-muted">({{ $system->vendor->name }})</small>
                                                @endif
                                            </span>
                                            <button type="button" class="btn btn-xs btn-outline-danger btn-sm detach-system" data-system-id="{{ $system->id }}">Remove</button>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">No cross-system integrations</p>
                                    @endforelse
                                </div>
                                <form id="attachSystemForm">
                                    <select id="attach-system-select" class="form-control mb-2">
                                        <option value="">Select system to integrate...</option>
                                        @foreach($systems as $system)
                                            @if((int) $system->id !== (int) $api->owner_system_id && !$api->systems->contains($system))
                                                <option value="{{ $system->id }}">{{ $system->name }}@if($system->vendor) — {{ $system->vendor->name }}@endif</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Add Integration</button>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Record TPS</h5></div>
                            <div class="card-body">
                                <form action="{{ route('apis.addTps', $api) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">TPS Value</label>
                                        <input type="number" name="tps_value" class="form-control" step="0.01" min="0" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Recorded At (optional)</label>
                                        <input type="datetime-local" name="recorded_at" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes" class="form-control" rows="2"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Save TPS</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('vendor/chart-js/chart.bundle.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>

    @if($api->isRest())
        <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>
        <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-standalone-preset.js"></script>
        <script src="{{ asset('js/swagger-spec-init.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                initSwaggerSpec(@json($openApiSpec));
            });
        </script>
    @endif

    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('#version-switcher').on('change', function () {
            window.location.href = $(this).val();
        });

        const chartData = @json($chartData);
        if (chartData.labels.length > 0) {
            new Chart(document.getElementById('tpsChart'), {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'TPS',
                        data: chartData.values,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        $('#attachSystemForm').on('submit', function(e) {
            e.preventDefault();
            const systemId = $('#attach-system-select').val();
            if (!systemId) return;
            $.post('{{ route("apis.attachSystem", $api) }}', { system_id: systemId })
                .done(function() { location.reload(); })
                .fail(function(xhr) { alert(xhr.responseJSON?.message || 'Failed to attach system'); });
        });

        $('.detach-system').on('click', function() {
            const systemId = $(this).data('system-id');
            if (!confirm('Remove this system link?')) return;
            $.ajax({
                url: '{{ url("apis/".$api->id."/systems") }}/' + systemId,
                type: 'DELETE',
                success: function() { location.reload(); }
            });
        });
    </script>
@endpush
