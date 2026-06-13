@extends('master')

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">Platform Schemas</h4>
                    <small class="text-muted">Per-system data dictionaries (bronze/silver/native layers)</small>
                </div>
                <a href="{{ route('data-stack.index') }}" class="btn btn-outline-secondary btn-sm">← Data Stack</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small">System</label>
                        <select name="system_id" class="form-control form-control-sm">
                            <option value="">All systems</option>
                            @foreach($systems as $system)
                                <option value="{{ $system->id }}" @selected(request('system_id') == $system->id)>{{ $system->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Data layer</label>
                        <select name="data_layer" class="form-control form-control-sm">
                            <option value="">All layers</option>
                            @foreach($layers as $layer)
                                <option value="{{ $layer }}" @selected(request('data_layer') === $layer)>{{ \App\Support\DataLayers::label($layer) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="{{ route('platform-schemas.index') }}" class="btn btn-light btn-sm">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Schemas</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>System</th>
                                        <th>Layer</th>
                                        <th>Source</th>
                                        <th>Fields</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($schemas as $schema)
                                        <tr>
                                            <td><a href="{{ route('platform-schemas.show', $schema) }}">{{ $schema->name }}</a></td>
                                            <td>{{ $schema->system?->name }}</td>
                                            <td><span class="badge badge-{{ \App\Support\DataLayers::badgeClass($schema->data_layer) }}">{{ \App\Support\DataLayers::label($schema->data_layer) }}</span></td>
                                            <td>{{ $schema->source_type }}</td>
                                            <td>{{ $schema->fields_count }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('platform-schemas.destroy', $schema) }}" class="d-inline" onsubmit="return confirm('Delete schema?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-muted text-center py-4">No platform schemas</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Create Schema</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('platform-schemas.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">System</label>
                                <select name="system_id" class="form-control" required>
                                    @foreach($systems as $system)
                                        <option value="{{ $system->id }}" @selected(request('system_id') == $system->id)>{{ $system->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="Customer API v2">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Data layer</label>
                                <select name="data_layer" class="form-control">
                                    @foreach($layers as $layer)
                                        <option value="{{ $layer }}">{{ \App\Support\DataLayers::label($layer) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Source type</label>
                                <select name="source_type" class="form-control">
                                    @foreach(['manual', 'openapi', 'graphql', 'wsdl', 'csv', 'json'] as $src)
                                        <option value="{{ $src }}">{{ $src }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Version</label>
                                <input type="text" name="version" class="form-control" placeholder="1.0.0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Schema</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Import from APIs</h5></div>
                    <div class="card-body">
                        <p class="small text-muted">Extract fields from REST API OpenAPI specs into bronze-layer schemas.</p>
                        @foreach($systems as $system)
                            <form method="POST" action="{{ route('platform-schemas.import', $system) }}" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">{{ $system->name }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
