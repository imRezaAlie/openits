@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">Processes</h4>
                    <small class="text-muted">BPMN and sequence diagram processes across all systems</small>
                </div>
                <a href="{{ route('systems.index') }}" class="btn btn-outline-primary btn-sm">Manage by System</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">All Processes</h5>
                        <span class="badge badge-primary">{{ $processes->count() }} process(es)</span>
                    </div>
                    <div class="card-body">
                        @if($processes->isEmpty())
                            <div class="text-center py-5">
                                <p class="text-muted mb-3">No processes yet.</p>
                                <a href="{{ route('systems.index') }}" class="btn btn-primary btn-sm">Go to Systems to create one</a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>System</th>
                                            <th>Vendor</th>
                                            <th>Updated</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($processes as $process)
                                            <tr>
                                                <td>
                                                    <a href="{{ $process->editorUrl() }}" class="fw-semibold">{{ $process->name }}</a>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ \App\Support\DiagramTypes::badgeClass($process->diagram_type ?? 'bpmn') }}">
                                                        {{ \App\Support\DiagramTypes::label($process->diagram_type ?? 'bpmn') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($process->system)
                                                        <a href="{{ route('systems.processes', $process->system) }}">{{ $process->system->name }}</a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $process->system?->vendor?->name ?? '—' }}</td>
                                                <td>{{ $process->updated_at->format('M j, Y H:i') }}</td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a href="{{ $process->editorUrl() }}" class="btn btn-primary btn-sm">Edit</a>
                                                        <button type="button"
                                                                class="btn btn-outline-danger btn-sm delete-process"
                                                                data-id="{{ $process->id }}"
                                                                data-name="{{ $process->name }}">
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
                url: '/systems/bpmn/' + btn.data('id'),
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
