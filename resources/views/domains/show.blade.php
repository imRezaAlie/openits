@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0 py-0">
                            <li class="breadcrumb-item"><a href="{{ route('domains.index') }}">Domains</a></li>
                            <li class="breadcrumb-item active">{{ $domain->name }}</li>
                        </ol>
                    </nav>
                    <h4 class="mb-0 d-flex align-items-center gap-2">
                        @if($domain->icon)
                            <i class="{{ $domain->icon }}" style="color: {{ $domain->color ?? '#64748b' }};"></i>
                        @endif
                        {{ $domain->name }}
                    </h4>
                    <small class="text-muted">{{ $domain->description }}</small>
                    <div class="mt-2 d-flex gap-2">
                        <span class="badge badge-primary">{{ $domain->systems->count() }} system(s)</span>
                        <span class="badge badge-info">{{ $domain->apis_count }} API(s)</span>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('domains.index') }}" class="btn btn-outline-secondary btn-sm">Back to Domains</a>
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#domainEditModal">
                        Edit Domain
                    </button>
                    <a href="{{ route('systems.index', ['domain_id' => $domain->id]) }}" class="btn btn-primary btn-sm">All Systems</a>
                    <a href="{{ route('integrations.catalog', ['domain_id' => $domain->id]) }}" class="btn btn-outline-primary btn-sm">Integrations</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Systems in {{ $domain->name }}</h5>
                        <a href="{{ route('systems.index', ['domain_id' => $domain->id]) }}?view=table" class="btn btn-sm btn-outline-primary">Open in Systems</a>
                    </div>
                    <div class="card-body">
                        @if($domain->systems->isEmpty())
                            <div class="text-center py-5">
                                <p class="text-muted mb-3">No systems in this domain yet.</p>
                                <a href="{{ route('systems.index', ['domain_id' => $domain->id]) }}" class="btn btn-primary btn-sm">Add System</a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>System</th>
                                            <th>Vendor</th>
                                            <th>Type</th>
                                            <th class="text-center">APIs</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($domain->systems as $system)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('integrations.system', $system) }}" class="fw-semibold text-body text-decoration-none">{{ $system->name }}</a>
                                                </td>
                                                <td>{{ $system->vendor?->name ?? '—' }}</td>
                                                <td>{{ $system->system_type ?? '—' }}</td>
                                                <td class="text-center">
                                                    @if($system->owned_apis_count)
                                                        <a href="{{ route('apis.index', ['system_id' => $system->id]) }}" class="badge badge-primary">{{ $system->owned_apis_count }}</a>
                                                    @else
                                                        <span class="text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('integrations.system', $system) }}" class="btn btn-sm btn-outline-primary">Integrations</a>
                                                    <a href="{{ route('apis.create', ['system_id' => $system->id, 'vendor_id' => $system->vendor_id]) }}" class="btn btn-sm btn-primary">Add API</a>
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

<div class="modal fade" id="domainEditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Domain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('domains.update', $domain) }}">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name', $domain->name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $domain->description) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Font Awesome class)</label>
                        <input type="text" name="icon" class="form-control" value="{{ old('icon', $domain->icon) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="color" name="color" class="form-control form-control-color w-100" value="{{ old('color', $domain->color ?? '#4f46e5') }}">
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
@endpush
