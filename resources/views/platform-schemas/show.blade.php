@extends('master')

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">{{ $platformSchema->name }}</h4>
                    <small class="text-muted">
                        {{ $platformSchema->system?->name }} ·
                        <span class="badge badge-{{ \App\Support\DataLayers::badgeClass($platformSchema->data_layer) }}">{{ \App\Support\DataLayers::label($platformSchema->data_layer) }}</span>
                    </small>
                </div>
                <a href="{{ route('platform-schemas.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Fields ({{ $platformSchema->fields->count() }})</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Native name</th>
                                        <th>Path</th>
                                        <th>Type</th>
                                        <th>PK</th>
                                        <th>Mappings</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($platformSchema->fields as $field)
                                        <tr>
                                            <td><code>{{ $field->native_name }}</code></td>
                                            <td><small class="text-muted">{{ $field->native_path ?? '—' }}</small></td>
                                            <td>{{ $field->data_type }}</td>
                                            <td>{{ $field->is_primary_key ? '✓' : '' }}</td>
                                            <td>
                                                @foreach($field->mappings as $mapping)
                                                    <span class="badge badge-success">{{ $mapping->canonicalAttribute?->name }}</span>
                                                @endforeach
                                                @if($field->mappings->isEmpty())—@endif
                                            </td>
                                            <td>
                                                <form method="POST" action="{{ route('platform-schemas.fields.destroy', [$platformSchema, $field]) }}" class="d-inline" onsubmit="return confirm('Remove field?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-muted text-center py-4">No fields defined</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Edit Schema</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('platform-schemas.update', $platformSchema) }}">
                            @csrf @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $platformSchema->name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Data layer</label>
                                    <select name="data_layer" class="form-control">
                                        @foreach(\App\Support\DataLayers::all() as $layer)
                                            <option value="{{ $layer }}" @selected($platformSchema->data_layer === $layer)>{{ \App\Support\DataLayers::label($layer) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Source type</label>
                                    <input type="text" name="source_type" class="form-control" value="{{ $platformSchema->source_type }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Version</label>
                                    <input type="text" name="version" class="form-control" value="{{ $platformSchema->version }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="2">{{ $platformSchema->description }}</textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Add Field</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('platform-schemas.fields.store', $platformSchema) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Native name</label>
                                <input type="text" name="native_name" class="form-control" required placeholder="AccountId">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">JSON path</label>
                                <input type="text" name="native_path" class="form-control" placeholder="$.Account.Id">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Data type</label>
                                <select name="data_type" class="form-control">
                                    @foreach(['string', 'integer', 'decimal', 'boolean', 'date', 'datetime', 'uuid', 'object', 'array'] as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sample value</label>
                                <input type="text" name="sample_value" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_primary_key" value="1" class="form-check-input" id="is_pk">
                                <label class="form-check-label" for="is_pk">Primary key</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Add Field</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
