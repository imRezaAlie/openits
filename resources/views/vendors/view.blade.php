@extends('master')
@push('head-src')
    <link class="main-css" href="{{ asset('css/style.css') }}" rel="stylesheet">
@endpush

@section('body')
    <div class="content-body">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <h2 class="mb-0">{{ $vendor->name }}</h2>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('supplier.edit', $vendor) }}" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-pen me-1"></i> Edit
                                    </a>
                                    <form action="{{ route('supplier.destroy', $vendor) }}" method="POST"
                                          onsubmit="return confirm('Delete this vendor?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fa-solid fa-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <dl class="row mb-0">
                                <dt class="col-sm-3 text-muted">Systems</dt>
                                <dd class="col-sm-9">
                                    <a href="{{ route('systems.index', ['vendor_id' => $vendor->id]) }}">
                                        {{ $vendor->systems_count }}
                                    </a>
                                </dd>

                                <dt class="col-sm-3 text-muted">Projects</dt>
                                <dd class="col-sm-9">{{ $vendor->projects_count }}</dd>

                                <dt class="col-sm-3 text-muted">Created</dt>
                                <dd class="col-sm-9">{{ $vendor->created_at?->format('M j, Y g:i A') ?? '—' }}</dd>

                                <dt class="col-sm-3 text-muted">Last updated</dt>
                                <dd class="col-sm-9">{{ $vendor->updated_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            @if($vendor->systems->isNotEmpty())
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="fs-20 font-w600 mb-3">Systems</h4>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($vendor->systems as $system)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('integrations.system', $system) }}">{{ $system->name }}</a>
                                                </td>
                                                <td class="text-muted">{{ $system->system_type ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($vendor->projects->isNotEmpty())
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="fs-20 font-w600 mb-3">Projects</h4>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($vendor->projects as $project)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('project.show', $project) }}">{{ $project->name }}</a>
                                                </td>
                                                <td>@include('projects._status-badge', ['status' => $project->status])</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('footer-src')
    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
@endpush
