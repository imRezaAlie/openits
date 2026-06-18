@extends('master')

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">
                        @if($technology->icon)
                            <i class="{{ $technology->icon }} text-primary me-1"></i>
                        @endif
                        {{ $technology->name }}
                    </h4>
                    <small class="text-muted">Systems using this technology</small>
                    <div class="mt-1">
                        <span class="badge badge-light">{{ \App\Support\TechnologyCategories::label($technology->category) }}</span>
                        <span class="badge badge-primary">{{ $technology->systems->count() }} system(s)</span>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('technologies.index') }}" class="btn btn-outline-secondary btn-sm">Back to Catalog</a>
                    <a href="{{ route('systems.index') }}" class="btn btn-outline-primary btn-sm">Manage System Stacks</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Systems</h5>
                        <span class="badge badge-primary">{{ $technology->systems->count() }}</span>
                    </div>
                    <div class="card-body">
                        @if($technology->systems->isEmpty())
                            <div class="text-center py-5">
                                <p class="text-muted mb-3">No systems use {{ $technology->name }} yet.</p>
                                <a href="{{ route('systems.index') }}" class="btn btn-primary btn-sm">Assign to a system</a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>System</th>
                                            <th>Vendor</th>
                                            <th>Type</th>
                                            <th>Version</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($technology->systems as $system)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('systems.technologies', $system) }}" class="fw-semibold">{{ $system->name }}</a>
                                                </td>
                                                <td>{{ $system->vendor?->name ?? '—' }}</td>
                                                <td>{{ $system->system_type ?? '—' }}</td>
                                                <td>
                                                    @if($system->pivot->version)
                                                        <span class="badge badge-light">{{ $system->pivot->version }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('integrations.system', $system) }}" class="btn btn-sm btn-outline-primary">Integrations</a>
                                                    <a href="{{ route('systems.technologies', $system) }}" class="btn btn-sm btn-primary">Tech Stack</a>
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
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
@endpush
