@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">Supporting Documents</h4>
                    <small class="text-muted">Manuals, specifications, and reference files across all systems</small>
                </div>
                <a href="{{ route('systems.index') }}" class="btn btn-outline-primary btn-sm">Manage by System</a>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card mb-0">
                    <div class="card-body py-3">
                        <small class="text-muted d-block">Total Documents</small>
                        <strong class="fs-4">{{ $stats['total'] }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card mb-0">
                    <div class="card-body py-3">
                        <small class="text-muted d-block">Systems with Documents</small>
                        <strong class="fs-4">{{ $stats['systems_with_docs'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body py-3">
                        <form method="GET" action="{{ route('documents.index') }}" class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Search</label>
                                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Name, version, file name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">System</label>
                                <select name="system_id" class="form-select form-select-sm">
                                    <option value="">All systems</option>
                                    @foreach($systems as $system)
                                        <option value="{{ $system->id }}" @selected(request('system_id') == $system->id)>{{ $system->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">All Documents</h5>
                        <span class="badge badge-primary">{{ $documents->count() }} document(s)</span>
                    </div>
                    <div class="card-body p-0">
                        @if($documents->isEmpty())
                            <div class="text-center py-5">
                                <p class="text-muted mb-3">No supporting documents uploaded yet.</p>
                                <a href="{{ route('systems.index') }}" class="btn btn-primary btn-sm">Go to Systems to add documents</a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Version</th>
                                            <th>System</th>
                                            <th>Attachment</th>
                                            <th>Uploaded</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documents as $document)
                                            <tr>
                                                <td class="fw-semibold">{{ $document->name }}</td>
                                                <td>
                                                    @if($document->version)
                                                        <span class="badge badge-light">{{ $document->version }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('systems.documents', $document->system) }}" class="text-decoration-none">
                                                        {{ $document->system->name }}
                                                    </a>
                                                    @if($document->system->vendor)
                                                        <br><small class="text-muted">{{ $document->system->vendor->name }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('systems.documents.download', [$document->system, $document]) }}" class="text-decoration-none">
                                                        <i class="fa-solid fa-paperclip me-1"></i>{{ $document->attachment_original_name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $document->created_at->format('Y-m-d H:i') }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        @if($document->isMarkdown())
                                                            <a href="{{ route('systems.documents.view', [$document->system, $document]) }}" class="btn btn-outline-info" title="View">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('systems.documents.edit-markdown', [$document->system, $document]) }}" class="btn btn-outline-success" title="Edit live">
                                                                <i class="fa-solid fa-pen-to-square"></i>
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('systems.documents.download', [$document->system, $document]) }}" class="btn btn-outline-primary" title="Download">
                                                            <i class="fa-solid fa-download"></i>
                                                        </a>
                                                        <a href="{{ route('systems.documents', $document->system) }}" class="btn btn-outline-secondary" title="Manage">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </a>
                                                    </div>
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
