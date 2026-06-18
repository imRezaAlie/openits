@extends('master')
@push('head-src')
    <link href="{{ asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
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
                                <div>
                                    <h2 class="mb-2">{{ $project->name }}</h2>
                                    @include('projects._status-badge', ['status' => $project->status])
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('project.edit', $project) }}" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-pen me-1"></i> Edit
                                    </a>
                                    <form action="{{ route('project.destroy', $project) }}" method="POST"
                                          onsubmit="return confirm('Delete this project?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fa-solid fa-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <dl class="row mb-0">
                                <dt class="col-sm-3 text-muted">Vendor</dt>
                                <dd class="col-sm-9">
                                    @if($project->vendor)
                                        <a href="{{ route('supplier.show', $project->vendor) }}">{{ $project->vendor->name }}</a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-3 text-muted">Created</dt>
                                <dd class="col-sm-9">{{ $project->created_at?->format('M j, Y g:i A') ?? '—' }}</dd>

                                <dt class="col-sm-3 text-muted">Last updated</dt>
                                <dd class="col-sm-9">{{ $project->updated_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="fs-20 font-w600 mb-3">Linked Processes</h4>
                            @if($project->bpmns->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Updated</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($project->bpmns as $bpmn)
                                            <tr>
                                                <td>{{ $bpmn->name }}</td>
                                                <td class="text-capitalize">{{ $bpmn->diagram_type ?? 'bpmn' }}</td>
                                                <td class="text-muted small">{{ $bpmn->updated_at?->format('M j, Y') }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">
                                    No processes are linked to this project.
                                    <a href="{{ route('systems.index') }}">Manage processes from the Systems page</a>.
                                </p>
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
