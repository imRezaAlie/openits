@extends('master')
@push('head-src')

    <link href="../../../vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="../../../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../../../vendor/datatables/css/buttons.dataTables.min.css" rel="stylesheet">
    <!-- Style css -->
    <link class="main-css" href="../../../css/style.css" rel="stylesheet">
@endpush

@section('body')
    <div class="content-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!--Tab slider End-->
                                <div class="col-xl-9 col-lg-6 col-md-6 col-sm-12">
                                    <div class="product-detail-content">
                                        <!--Product details-->
                                        <div class="new-arrival-content pr">
                                            <h2>{{$project->name}}</h2>

                                            <div class="d-table mb-2">
                                                <p class="price float-start d-block">
                                                    Vendor: {{$project->vendor->name}}</p>
                                            </div>

                                            <p class="text-content">project description will be added</p>
                                            <div class="d-flex align-items-end flex-wrap mt-4">
                                                <div class="filtaring-area me-3">
                                                    <div class="size-filter">
                                                        <h4 class="m-b-15">Select size <label
                                                                class="btn btn-outline-primary sharp sharp-lg"
                                                                for="btnradio1">XS</label></h4>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- review -->

                <!-- BPMN processes are managed per system -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="fs-20 font-w600 my-4">Processes</h4>
                            <p class="text-muted mb-0">
                                BPMN processes are linked to systems.
                                <a href="{{ route('systems.index') }}">Manage processes from the Systems page</a>.
                            </p>
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
    <script src="../../../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../../../vendor/datatables/js/buttons.html5.min.js"></script>
    <script src="../../../js/plugins-init/datatables.init.js"></script>
    <script src="../../../js/custom.min.js"></script>
    <script src="../../../js/deznav-init.js"></script>
@endpush
