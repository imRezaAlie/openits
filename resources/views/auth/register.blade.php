@extends('layouts.guest')

@section('title', 'OpenITS | Register')

@section('content')
<div class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('landing/assets/img/logo-color.png') }}" alt="OpenITS">
                </a>
            </div>

            <h1>Create account</h1>
            <p class="auth-subtitle">Get started with OpenITS for free</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Full name</label>
                    <input
                        id="name"
                        type="text"
                        class="form-control-openits @error('name') is-invalid @enderror"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autocomplete="name"
                        autofocus
                        placeholder="John Doe"
                    >
                    @error('name')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input
                        id="email"
                        type="email"
                        class="form-control-openits @error('email') is-invalid @enderror"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        placeholder="you@company.com"
                    >
                    @error('email')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-toggle">
                        <input
                            id="password"
                            type="password"
                            class="form-control-openits @error('password') is-invalid @enderror"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="At least 8 characters"
                        >
                        <button type="button" class="toggle-btn" data-toggle-password="password">Show</button>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password-confirm">Confirm password</label>
                    <div class="password-toggle">
                        <input
                            id="password-confirm"
                            type="password"
                            class="form-control-openits"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Repeat your password"
                        >
                        <button type="button" class="toggle-btn" data-toggle-password="password-confirm">Show</button>
                    </div>
                </div>

                <button type="submit" class="btn-openits btn-openits-primary btn-openits-block">
                    Create Account
                </button>
            </form>

            <p class="auth-footer-text">
                Already have an account?
                <a href="{{ route('login') }}">Sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection
