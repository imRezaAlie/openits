@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">{{ $system->name }} — Processes</h4>
                    <small class="text-muted">BPMN and sequence diagram processes for this system</small>
                    <div class="mt-1">
                        @if($system->vendor)
                            <span class="badge badge-info">{{ $system->vendor->name }}</span>
                        @endif
                        @if($system->system_type)
                            <span class="badge badge-light">{{ $system->system_type }}</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('systems.index') }}" class="btn btn-outline-secondary btn-sm">Back to Systems</a>
                    <a href="{{ route('systems.technologies', $system) }}" class="btn btn-outline-secondary btn-sm">Tech Stack</a>
                    <a href="{{ route('systems.servers', $system) }}" class="btn btn-outline-secondary btn-sm">Servers</a>
                    <a href="{{ route('integrations.system', $system) }}" class="btn btn-outline-primary btn-sm">Integrations</a>
                    <a href="{{ route('systems.create.sequence', $system) }}" class="btn btn-outline-primary btn-sm">Sequence Diagram</a>
                    <a href="{{ route('systems.create.bpmn', $system) }}" class="btn btn-primary btn-sm">BPMN Process</a>
                </div>
            </div>
        </div>

        @if($system->description)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body py-2">
                            <small class="text-muted">{{ $system->description }}</small>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Process List</h5>
                        <span class="badge badge-primary">{{ $system->bpmns->count() }} process(es)</span>
                    </div>
                    <div class="card-body">
                        @if($system->bpmns->isEmpty())
                            <div class="text-center py-5">
                                <p class="text-muted mb-3">No processes defined for this system yet.</p>
                                <a href="{{ route('systems.create.sequence', $system) }}" class="btn btn-outline-primary btn-sm me-1">Sequence Diagram</a>
                                <a href="{{ route('systems.create.bpmn', $system) }}" class="btn btn-primary btn-sm">BPMN Process</a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Created</th>
                                            <th>Updated</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($system->bpmns as $process)
                                            <tr>
                                                <td>
                                                    <a href="{{ $process->editorUrl() }}" class="fw-semibold">{{ $process->name }}</a>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ \App\Support\DiagramTypes::badgeClass($process->diagram_type ?? 'bpmn') }}">
                                                        {{ \App\Support\DiagramTypes::label($process->diagram_type ?? 'bpmn') }}
                                                    </span>
                                                </td>
                                                <td>{{ $process->created_at->format('M j, Y H:i') }}</td>
                                                <td>{{ $process->updated_at->format('M j, Y H:i') }}</td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a href="{{ $process->editorUrl() }}" class="btn btn-primary btn-sm">Edit</a>
                                                        <button type="button"
                                                                class="btn btn-outline-danger btn-sm delete-process"
                                                                data-name="{{ $process->name }}"
                                                                data-destroy-url="{{ route('systems.destroy.bpmn', $process) }}">
                                                            Delete
                                                        </button>
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
    <script>
        $('.delete-process').on('click', function () {
            const btn = $(this);
            const name = btn.data('name');

            if (!confirm('Delete process "' + name + '"?')) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: btn.data('destroy-url'),
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE',
                },
                success: function () {
                    btn.closest('tr').fadeOut(300, function () {
                        $(this).remove();
                        if ($('tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                },
                error: function () {
                    alert('Failed to delete process.');
                }
            });
        });
    </script>
@endpush
