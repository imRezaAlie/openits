@extends('master')

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">Architectural Decision Records</h4>
                    <small class="text-muted">Document and track architecture decisions</small>
                </div>
                <div class="btn-group">
                    <a href="{{ route('c4.adrs.timeline') }}" class="btn btn-outline-secondary btn-sm">Timeline</a>
                    <a href="{{ route('c4.adrs.create') }}" class="btn btn-primary btn-sm">New ADR</a>
                    <a href="{{ route('c4.index') }}" class="btn btn-outline-secondary btn-sm">C4 Models</a>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small mb-0">Status</label>
                        <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All statuses</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ \App\Support\AdrStatuses::label($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-0">System</label>
                        <select name="system_id" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All systems</option>
                            @foreach($systems as $system)
                                <option value="{{ $system->id }}" @selected($filters['system_id'] === $system->id)>{{ $system->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>System</th>
                            <th>Author</th>
                            <th>Decided</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adrs as $adr)
                            <tr>
                                <td><a href="{{ route('c4.adrs.show', $adr) }}">{{ $adr->title }}</a></td>
                                <td><span class="badge badge-{{ \App\Support\AdrStatuses::badgeClass($adr->status) }}">{{ \App\Support\AdrStatuses::label($adr->status) }}</span></td>
                                <td>{{ $adr->system?->name ?? '—' }}</td>
                                <td>{{ $adr->author?->name }}</td>
                                <td>{{ $adr->decided_at?->format('Y-m-d') ?? '—' }}</td>
                                <td><a href="{{ route('c4.adrs.edit', $adr) }}" class="btn btn-xs btn-sm btn-outline-secondary">Edit</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted p-4">No ADRs yet. <a href="{{ route('c4.adrs.create') }}">Create one</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($adrs->hasPages())
                <div class="card-footer">{{ $adrs->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
@endpush
