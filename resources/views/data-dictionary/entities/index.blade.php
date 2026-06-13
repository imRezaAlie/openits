@extends('master')

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">Canonical Data Dictionary</h4>
                    <small class="text-muted">Gold-layer business entities and attributes shared across all platforms</small>
                </div>
                <a href="{{ route('data-stack.index') }}" class="btn btn-outline-secondary btn-sm">← Data Stack</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Entities</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Domain</th>
                                        <th>Attributes</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($entities as $entity)
                                        <tr>
                                            <td>
                                                <a href="{{ route('data-dictionary.entities.show', $entity) }}">{{ $entity->name }}</a>
                                                @if($entity->description)
                                                    <br><small class="text-muted">{{ Str::limit($entity->description, 60) }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $entity->domain?->name ?? '—' }}</td>
                                            <td>{{ $entity->attributes_count }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('data-dictionary.entities.destroy', $entity) }}" class="d-inline" onsubmit="return confirm('Delete this entity?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-muted text-center py-4">No canonical entities defined</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Add Entity</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('data-dictionary.entities.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="Customer">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Domain</label>
                                <select name="domain_id" class="form-control">
                                    <option value="">Global</option>
                                    @foreach($domains as $domain)
                                        <option value="{{ $domain->id }}">{{ $domain->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Entity</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
