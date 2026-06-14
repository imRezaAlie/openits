@extends('master')
@push('head-src')


    <link href="../../../vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="../../../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../../../vendor/jvmap/jquery-jvectormap.css" rel="stylesheet">
    <link href="../../../vendor/datatables/css/buttons.dataTables.min.css" rel="stylesheet">

    <!-- tagify-css -->

    <!-- Style css -->
    <link class="main-css" href="../../../css/style.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    </script>
@endpush

@section('body')
    <div class="content-body">
        <!-- row -->
        <div class="container-fluid">
            <div class="row">
                <div class="d-flex justify-content-between align-items-center mb-4">

                    <button type="button" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#basicModal">Add Vendor</button>
                    <div class="modal fade" id="basicModal" tabindex="-1" aria-labelledby="basicModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="basicModalLabel">Add Vendor</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="vendorForm">
                                    @csrf <!-- Laravel CSRF Token -->
                                        <div class="mb-3">
                                            <label for="vendorName" class="form-label">Vendor Name</label>
                                            <input type="text" class="form-control" id="vendorName" name="name" required>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                                    <button type="button" id="saveVendorBtn" class="btn btn-primary">Save changes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12 active-p">
                    <div class="tab-content" id="pills-tabContent">

                        <div class="tab-pane fade show active" id="pills-colm" role="tabpanel" aria-labelledby="pills-colm-tab">
                            <div class="card">
                                <div class="card-body px-0">
                                    <div class="table-responsive active-projects user-tbl  dt-filter">
                                        <table id="user-tbl" class="table shorting">
                                            <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($vendors as $vendor)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <p class="mb-0 ms-2">{{$vendor->name}}</p>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <div class="btn-link" data-bs-toggle="dropdown">
                                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M11 12C11 12.5523 11.4477 13 12 13C12.5523 13 13 12.5523 13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12Z" stroke="#737B8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                    <path d="M18 12C18 12.5523 18.4477 13 19 13C19.5523 13 20 12.5523 20 12C20 11.4477 19.5523 11 19 11C18.4477 11 18 11.4477 18 12Z" stroke="#737B8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                    <path d="M4 12C4 12.5523 4.44772 13 5 13C5.55228 13 6 12.5523 6 12C6 11.4477 5.55228 11 5 11C4.44772 11 4 11.4477 4 12Z" stroke="#737B8B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                </svg>
                                                            </div>
                                                            <div class="dropdown-menu dropdown-menu-right" style="">
                                                                <a class="dropdown-item" href="javascript:void(0);">Edit</a>
                                                                <a class="dropdown-item" href="javascript:void(0);">Delete</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('footer-src')

    <script src="../../../vendor/global/global.min.js"></script>
    <script src="../../../vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="../../../vendor/apexchart/apexchart.js"></script>
    <!-- Dashboard 1 -->
    <!-- tagify -->

    <script src="../../../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../../../vendor/datatables/js/dataTables.buttons.min.js"></script>
    <script src="../../../vendor/datatables/js/buttons.html5.min.js"></script>
    <script src="../../../vendor/datatables/js/jszip.min.js"></script>
    <script src="../../../js/plugins-init/datatables.init.js"></script>

    <!-- Apex Chart -->
    <!-- Vectormap -->
    <script src="../../../js/custom.min.js"></script>
    <script src="../../../js/deznav-init.js"></script>
    <script>
        $(document).ready(function() {
            $('#saveVendorBtn').click(function(e) {
                e.preventDefault();

                $.ajax({
                    url: '{{ route("supplier.store") }}',
                    type: 'POST',
                    data: $('#vendorForm').serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#basicModal').modal('hide');
                            showToast(response.message); // Show success toast
                            $('#vendorForm')[0].reset(); // Reset form
                        }
                    },
                    error: function(response) {
                        alert('An error occurred. Please try again.');
                    }
                });
            });

            function showToast(message) {
                Toastify({
                    text: "✅ Your request has been successfully completed!",
                    duration: 2000, // Slightly longQer duration for better visibility
                    close: true, // Allow the user to close the toast manually
                    gravity: "top", // Display at the top
                    position: "right", // Align to the right
                    stopOnFocus: true, // Keep the toast visible on hover
                    style: {
                        background: "linear-gradient(to right, #4CAF50, #8BC34A)", // Professional color scheme (green for success)
                        color: "#fff", // White text color for better contrast
                        borderRadius: "8px", // Rounded corners for a smoother look
                        boxShadow: "0px 4px 15px rgba(0, 0, 0, 0.2)", // Subtle shadow for depth
                        padding: "16px", // Add padding for better spacing
                        fontFamily: "'Roboto', sans-serif", // Modern font for a clean appearance
                        fontSize: "16px", // Slightly larger text for readability
                    },
                    onClick: function() {
                        // Optional: Add any additional action on click, such as redirecting to another page
                    }

                }).showToast();
                location.reload()

            }
        });

    </script>
@endpush
