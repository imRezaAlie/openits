@extends('layouts.guest')

@section('title', 'OpenITS | Log In')

@section('content')
<div class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('landing/assets/img/logo-color.png') }}" alt="OpenITS">
                </a>
            </div>

            <h1>Welcome back</h1>
            <p class="auth-subtitle">Sign in to your account to continue</p>

            <div class="auth-demo-credentials">
                <p class="auth-demo-title">Default login</p>
                <div class="auth-demo-row">
                    <span class="auth-demo-label">Email</span>
                    <code class="auth-demo-value" id="demo-email">admin@openits.local</code>
                    <button type="button" class="auth-demo-copy" data-copy-target="demo-email" aria-label="Copy email">Copy</button>
                </div>
                <div class="auth-demo-row">
                    <span class="auth-demo-label">Password</span>
                    <code class="auth-demo-value" id="demo-password">password</code>
                    <button type="button" class="auth-demo-copy" data-copy-target="demo-password" aria-label="Copy password">Copy</button>
                </div>
                <button type="button" class="auth-demo-fill" id="fill-demo-credentials">
                    Fill form
                </button>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

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
                        autofocus
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
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        >
                        <button type="button" class="toggle-btn" data-toggle-password="password">Show</button>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row-auth">
                    <label class="form-check-auth">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    @else
                        <a href="{{ url('/forgetpasswd') }}">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-openits btn-openits-primary btn-openits-block">
                    Sign In
                </button>
            </form>

            <p class="auth-footer-text">
                Don't have an account?
                <a href="{{ route('register') }}">Create one</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
    .auth-demo-credentials {
        margin-bottom: 1.5rem;
        padding: 1rem;
        border: 1px dashed var(--border);
        border-radius: 10px;
        background: rgba(79, 70, 229, 0.04);
    }

    .auth-demo-title {
        margin: 0 0 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
    }

    .auth-demo-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .auth-demo-row:last-of-type {
        margin-bottom: 0.75rem;
    }

    .auth-demo-label {
        flex: 0 0 4.5rem;
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .auth-demo-value {
        flex: 1;
        min-width: 0;
        padding: 0.35rem 0.5rem;
        border-radius: 6px;
        background: var(--surface);
        border: 1px solid var(--border);
        font-size: 0.8rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .auth-demo-copy {
        flex-shrink: 0;
        padding: 0.35rem 0.65rem;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: var(--surface);
        color: var(--text);
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }

    .auth-demo-copy:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .auth-demo-copy.is-copied {
        border-color: #10b981;
        color: #10b981;
    }

    .auth-demo-fill {
        width: 100%;
        padding: 0.55rem 0.75rem;
        border: none;
        border-radius: 8px;
        background: var(--primary);
        color: #fff;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .auth-demo-fill:hover {
        opacity: 0.92;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        function copyText(text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }

            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'absolute';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            return Promise.resolve();
        }

        document.querySelectorAll('[data-copy-target]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = document.getElementById(btn.getAttribute('data-copy-target'));
                if (!target) return;

                copyText(target.textContent.trim()).then(function () {
                    var original = btn.textContent;
                    btn.textContent = 'Copied';
                    btn.classList.add('is-copied');
                    setTimeout(function () {
                        btn.textContent = original;
                        btn.classList.remove('is-copied');
                    }, 1500);
                });
            });
        });

        var fillBtn = document.getElementById('fill-demo-credentials');
        if (fillBtn) {
            fillBtn.addEventListener('click', function () {
                var email = document.getElementById('demo-email');
                var password = document.getElementById('demo-password');
                var emailInput = document.getElementById('email');
                var passwordInput = document.getElementById('password');

                if (email && emailInput) {
                    emailInput.value = email.textContent.trim();
                    emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                if (password && passwordInput) {
                    passwordInput.value = password.textContent.trim();
                    passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }
    })();
</script>
@endpush
