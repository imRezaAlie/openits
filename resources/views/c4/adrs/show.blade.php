@extends('master')

@push('head-src')
    <style>
        .adr-markdown { line-height: 1.7; white-space: pre-wrap; }
        .adr-section { margin-bottom: 1.5rem; }
    </style>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="mb-1">{{ $adr->title }}</h4>
                    <span class="badge badge-{{ \App\Support\AdrStatuses::badgeClass($adr->status) }}">{{ \App\Support\AdrStatuses::label($adr->status) }}</span>
                    @if($adr->system)
                        <span class="badge badge-info">{{ $adr->system->name }}</span>
                    @endif
                </div>
                <div class="btn-group">
                    <a href="{{ route('c4.adrs.edit', $adr) }}" class="btn btn-primary btn-sm">Edit</a>
                    <a href="{{ route('c4.adrs.index') }}" class="btn btn-outline-secondary btn-sm">All ADRs</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body adr-section">
                        <h6>Context</h6>
                        <div class="adr-markdown text-muted">{{ $adr->context ?: '—' }}</div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body adr-section">
                        <h6>Decision</h6>
                        <div class="adr-markdown">{{ $adr->decision ?: '—' }}</div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body adr-section">
                        <h6>Consequences</h6>
                        <div class="adr-markdown text-muted">{{ $adr->consequences ?: '—' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header"><h6 class="mb-0">Metadata</h6></div>
                    <div class="card-body small">
                        <p class="mb-1"><strong>Author:</strong> {{ $adr->author?->name }}</p>
                        <p class="mb-1"><strong>Decided:</strong> {{ $adr->decided_at?->format('M j, Y') ?? 'Not yet' }}</p>
                        <p class="mb-0"><strong>Created:</strong> {{ $adr->created_at->format('M j, Y') }}</p>
                    </div>
                </div>
                @if(count($linkedElements))
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Affected C4 Elements</h6></div>
                        <ul class="list-group list-group-flush">
                            @foreach($linkedElements as $el)
                                <li class="list-group-item small d-flex justify-content-between">
                                    <span>{{ $el['name'] }}</span>
                                    <span class="badge badge-light">{{ $el['element_type'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
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
