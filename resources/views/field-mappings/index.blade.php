@extends('master')

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">Field Mappings</h4>
                    <small class="text-muted">Cross-platform field-to-canonical attribute mappings with transform rules</small>
                </div>
                <a href="{{ route('data-stack.index') }}" class="btn btn-outline-secondary btn-sm">← Data Stack</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
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
                        <label class="form-label small">Canonical entity</label>
                        <select name="entity_id" class="form-control form-control-sm">
                            <option value="">All entities</option>
                            @foreach($attributes->pluck('entity')->unique('id') as $entity)
                                @if($entity)
                                    <option value="{{ $entity->id }}" @selected(request('entity_id') == $entity->id)>{{ $entity->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="{{ route('field-mappings.index') }}" class="btn btn-light btn-sm">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Mappings ({{ $mappings->count() }})</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Platform field</th>
                                        <th>System</th>
                                        <th>→</th>
                                        <th>Canonical</th>
                                        <th>Direction</th>
                                        <th>Transform</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($mappings as $mapping)
                                        <tr>
                                            <td>
                                                <code>{{ $mapping->native_name }}</code>
                                                <br><small class="text-muted">{{ $mapping->schema_name }}</small>
                                            </td>
                                            <td>{{ $mapping->system_name }}</td>
                                            <td class="text-muted">→</td>
                                            <td>
                                                <code>{{ $mapping->attribute_name }}</code>
                                                <br><small class="text-muted">{{ $mapping->entity_name }}</small>
                                            </td>
                                            <td><span class="badge badge-light">{{ $mapping->direction }}</span></td>
                                            <td><small>{{ Str::limit($mapping->transform_rule, 40) ?? '—' }}</small></td>
                                            <td>
                                                <form method="POST" action="{{ route('field-mappings.destroy', $mapping->id) }}" class="d-inline" onsubmit="return confirm('Delete mapping?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-muted text-center py-4">No field mappings defined</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Create Mapping</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('field-mappings.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Platform field</label>
                                <select name="platform_field_id" class="form-control" required>
                                    <option value="">Select field…</option>
                                    @foreach(\App\Models\PlatformField::with('schema.system')->orderBy('native_name')->get() as $field)
                                        <option value="{{ $field->id }}">
                                            {{ $field->schema?->system?->name }} / {{ $field->schema?->name }} / {{ $field->native_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Canonical attribute</label>
                                <select name="canonical_attribute_id" class="form-control" required>
                                    <option value="">Select attribute…</option>
                                    @foreach($attributes as $attr)
                                        <option value="{{ $attr->id }}">{{ $attr->entity?->name }} / {{ $attr->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Direction</label>
                                <select name="direction" class="form-control">
                                    <option value="bidirectional">Bidirectional</option>
                                    <option value="inbound">Inbound</option>
                                    <option value="outbound">Outbound</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Transform rule</label>
                                <input type="text" name="transform_rule" class="form-control" placeholder="UPPER(value) or padLeft(id, 10, '0')">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Mapping</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
