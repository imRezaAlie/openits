@extends('master')
@push('head-src')
    <link href="{{ asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/css/buttons.dataTables.min.css') }}" rel="stylesheet">
    <link class="main-css" href="{{ asset('css/style.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        .status-badge { text-transform: capitalize; }
    </style>
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

            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="heading mb-1">Projects</h4>
                        <p class="text-muted mb-0 small">Track vendor initiatives and their lifecycle status.</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#projectModal" data-action="create">
                        Add Project
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body px-0">
                            <div class="table-responsive active-projects user-tbl dt-filter">
                                <table id="user-tbl" class="table shorting">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Vendor</th>
                                        <th>Status</th>
                                        <th class="text-center">Processes</th>
                                        <th>Updated</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($projects as $project)
                                        <tr>
                                            <td>
                                                <a href="{{ route('project.show', $project) }}" class="fw-semibold text-body text-decoration-none">
                                                    {{ $project->name }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($project->vendor)
                                                    {{ $project->vendor->name }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @include('projects._status-badge', ['status' => $project->status])
                                            </td>
                                            <td class="text-center">
                                                @if($project->bpmns_count)
                                                    <span class="badge badge-info">{{ $project->bpmns_count }}</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ $project->updated_at?->format('M j, Y') }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('project.show', $project) }}" class="btn btn-outline-primary" title="View">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-secondary edit-project" title="Edit"
                                                            data-name="{{ $project->name }}"
                                                            data-vendor="{{ $project->vendor_id }}"
                                                            data-status="{{ $project->status }}"
                                                            data-update-url="{{ route('project.update', $project) }}">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <form action="{{ route('project.destroy', $project) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Delete this project?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                No projects yet. Click <strong>Add Project</strong> to create one.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectModalLabel">Add Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="projectForm" method="POST" action="{{ route('project.store') }}">
                    @csrf
                    <div id="projectMethodField"></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="projectName" class="form-label">Project Name</label>
                            <input type="text" class="form-control" id="projectName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="projectVendor" class="form-label">Vendor</label>
                            <select class="form-control" id="projectVendor" name="vendor_id" required>
                                <option value="">— Select vendor —</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="projectStatus" class="form-label">Status</label>
                            <select class="form-control" id="projectStatus" name="status" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected($status === 'review')>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveProjectBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('footer-src')
     <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/jszip.min.js') }}"></script>
    <script src="{{ asset('js/plugins-init/datatables.init.js') }}"></script>
    <script src="{{ asset('js/custom.min.js') }}"></script>
    <script src="{{ asset('js/deznav-init.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('[data-action="create"]').on('click', function () {
            $('#projectModalLabel').text('Add Project');
            $('#projectForm').attr('action', '{{ route("project.store") }}');
            $('#projectMethodField').html('');
            $('#projectForm')[0].reset();
            $('#projectStatus').val('review');
        });

        $(document).on('click', '.edit-project', function (e) {
            e.preventDefault();
            const el = $(this);

            $('#projectModalLabel').text('Edit Project');
            $('#projectForm').attr('action', el.data('update-url'));
            $('#projectMethodField').html('<input type="hidden" name="_method" value="PUT">');
            $('#projectName').val(el.data('name'));
            $('#projectVendor').val(el.data('vendor'));
            $('#projectStatus').val(el.data('status'));

            new bootstrap.Modal(document.getElementById('projectModal')).show();
        });

        $('#projectForm').on('submit', function (e) {
            const isCreate = !$('#projectMethodField input[name="_method"]').length;

            if (!isCreate) {
                return;
            }

            e.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    if (response.success) {
                        bootstrap.Modal.getInstance(document.getElementById('projectModal'))?.hide();
                        Toastify({
                            text: response.message,
                            duration: 2000,
                            close: true,
                            gravity: 'top',
                            position: 'right',
                            style: {
                                background: 'linear-gradient(to right, #4CAF50, #8BC34A)',
                                color: '#fff',
                                borderRadius: '8px',
                            },
                        }).showToast();
                        setTimeout(function () { location.reload(); }, 600);
                    }
                },
                error: function () {
                    alert('An error occurred. Please check the form and try again.');
                }
            });
        });
    </script>
@endpush
