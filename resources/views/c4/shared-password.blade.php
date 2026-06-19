@extends('master')

@section('body')
<div class="content-body">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">Protected C4 Diagram</div>
                    <div class="card-body">
                        <p class="text-muted">This shared diagram requires a password.</p>

                        <form method="POST" action="{{ route('c4.shared.unlock', $token) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    required
                                    autofocus
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">View Diagram</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
