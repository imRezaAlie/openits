@extends('master')

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between">
                <h4 class="mb-0">Architecture Decision Timeline</h4>
                <a href="{{ route('c4.adrs.index') }}" class="btn btn-outline-secondary btn-sm">← ADR List</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @forelse($adrs as $adr)
                    <div class="d-flex gap-3 mb-4 pb-3 border-bottom">
                        <div class="text-muted small text-end" style="min-width: 90px;">
                            {{ $adr->decided_at?->format('Y-m-d') ?? $adr->created_at->format('Y-m-d') }}
                        </div>
                        <div class="flex-grow-1">
                            <a href="{{ route('c4.adrs.show', $adr) }}" class="fw-semibold">{{ $adr->title }}</a>
                            <span class="badge badge-{{ \App\Support\AdrStatuses::badgeClass($adr->status) }} ms-1">{{ \App\Support\AdrStatuses::label($adr->status) }}</span>
                            @if($adr->system)<span class="badge badge-light">{{ $adr->system->name }}</span>@endif
                            <p class="text-muted small mb-0 mt-1">{{ Str::limit($adr->decision, 160) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No decisions with dates recorded yet.</p>
                @endforelse
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
