@extends('master')

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">{{ $canonicalEntity->name }}</h4>
                    <small class="text-muted">Canonical entity · {{ $canonicalEntity->attributes->count() }} attributes</small>
                </div>
                <a href="{{ route('data-dictionary.entities.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Attributes</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($canonicalEntity->attributes as $attr)
                                        <tr>
                                            <td><code>{{ $attr->name }}</code></td>
                                            <td><span class="badge badge-light">{{ $attr->data_type }}</span></td>
                                            <td>{{ $attr->is_required ? 'Yes' : 'No' }}</td>
                                            <td>{{ $attr->description ?? '—' }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('data-dictionary.entities.attributes.destroy', [$canonicalEntity, $attr]) }}" class="d-inline" onsubmit="return confirm('Remove attribute?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-muted text-center py-4">No attributes defined</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Edit Entity</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('data-dictionary.entities.update', $canonicalEntity) }}">
                            @csrf @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $canonicalEntity->name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Domain</label>
                                    <select name="domain_id" class="form-control">
                                        <option value="">Global</option>
                                        @foreach(\App\Models\Domain::orderBy('name')->get() as $domain)
                                            <option value="{{ $domain->id }}" @selected($canonicalEntity->domain_id == $domain->id)>{{ $domain->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="2">{{ $canonicalEntity->description }}</textarea>
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
                    <div class="card-header"><h5 class="mb-0">Add Attribute</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('data-dictionary.entities.attributes.store', $canonicalEntity) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="email">
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
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_required" value="1" class="form-check-input" id="is_required">
                                <label class="form-check-label" for="is_required">Required</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Add Attribute</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
