@extends('master')
@push('head-src')
    <link class="main-css" href="{{ asset('css/style.css') }}" rel="stylesheet">
@endpush

@section('body')
    <div class="content-body">
        <div class="container-fluid">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-12">
                    <a href="{{ route('project.show', $project) }}" class="btn btn-sm btn-light">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Project
                    </a>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Edit Project</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('project.update', $project) }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="name" class="form-label">Project Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                           value="{{ old('name', $project->name) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="vendor_id" class="form-label">Vendor</label>
                                    <select class="form-control" id="vendor_id" name="vendor_id" required>
                                        <option value="">— Select vendor —</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" @selected(old('vendor_id', $project->vendor_id) == $vendor->id)>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" @selected(old('status', $project->status) === $status)>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                    <a href="{{ route('project.show', $project) }}" class="btn btn-light">Cancel</a>
                                </div>
                            </form>
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
