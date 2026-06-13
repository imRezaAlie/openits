@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endpush

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Edit API: {{ $api->name }}</h4>
                <a href="{{ route('apis.show', $api) }}" class="btn btn-outline-secondary btn-sm">Back to Detail</a>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-10">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('apis.update', $api) }}" method="POST">
                            @csrf @method('PUT')
                            @include('apis._form', ['api' => $api, 'activeVersion' => $activeVersion, 'systems' => $systems, 'vendors' => $vendors])
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Update API</button>
                                <a href="{{ route('apis.show', $api) }}" class="btn btn-light">Cancel</a>
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
    <script src="{{ asset('js/api-form.js') }}"></script>
@endpush
