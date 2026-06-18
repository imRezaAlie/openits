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
                        <h4 class="heading mb-1">Vendors</h4>
                        <p class="text-muted mb-0 small">Manage vendor organizations linked to systems and projects.</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#vendorModal" data-action="create">
                        Add Vendor
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
                                        <th class="text-center">Systems</th>
                                        <th class="text-center">Projects</th>
                                        <th>Updated</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($vendors as $vendor)
                                        <tr>
                                            <td>
                                                <a href="{{ route('supplier.show', $vendor) }}" class="fw-semibold text-body text-decoration-none">
                                                    {{ $vendor->name }}
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                @if($vendor->systems_count)
                                                    <a href="{{ route('systems.index', ['vendor_id' => $vendor->id]) }}" class="badge badge-info">
                                                        {{ $vendor->systems_count }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($vendor->projects_count)
                                                    <span class="badge badge-primary">{{ $vendor->projects_count }}</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ $vendor->updated_at?->format('M j, Y') }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('supplier.show', $vendor) }}" class="btn btn-outline-primary" title="View">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-secondary edit-vendor" title="Edit"
                                                            data-id="{{ $vendor->id }}"
                                                            data-name="{{ $vendor->name }}"
                                                            data-update-url="{{ route('supplier.update', $vendor) }}">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <form action="{{ route('supplier.destroy', $vendor) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Delete this vendor?');">
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
                                            <td colspan="5" class="text-center text-muted py-5">
                                                No vendors yet. Click <strong>Add Vendor</strong> to create one.
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

    <div class="modal fade" id="vendorModal" tabindex="-1" aria-labelledby="vendorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vendorModalLabel">Add Vendor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="vendorForm" method="POST" action="{{ route('supplier.store') }}">
                    @csrf
                    <div id="vendorMethodField"></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="vendorName" class="form-label">Vendor Name</label>
                            <input type="text" class="form-control" id="vendorName" name="name" required maxlength="255">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
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
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $('[data-action="create"]').on('click', function () {
            $('#vendorModalLabel').text('Add Vendor');
            $('#vendorForm').attr('action', '{{ route("supplier.store") }}');
            $('#vendorMethodField').html('');
            $('#vendorForm')[0].reset();
        });

        $(document).on('click', '.edit-vendor', function (e) {
            e.preventDefault();
            const el = $(this);

            $('#vendorModalLabel').text('Edit Vendor');
            $('#vendorForm').attr('action', el.data('update-url'));
            $('#vendorMethodField').html('<input type="hidden" name="_method" value="PUT">');
            $('#vendorName').val(el.data('name'));

            new bootstrap.Modal(document.getElementById('vendorModal')).show();
        });

        $('#vendorForm').on('submit', function (e) {
            const isCreate = !$('#vendorMethodField input[name="_method"]').length;

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
                        bootstrap.Modal.getInstance(document.getElementById('vendorModal'))?.hide();
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
                error: function (xhr) {
                    const message = xhr.responseJSON?.message || 'An error occurred. Please check the form and try again.';
                    alert(message);
                }
            });
        });
    </script>
@endpush
