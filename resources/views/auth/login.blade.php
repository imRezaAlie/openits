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

            @if (session('error'))
                <div class="auth-alert auth-alert-error" role="alert">{{ session('error') }}</div>
            @endif

            @if($googleLoginEnabled ?? false)
                <a href="{{ route('auth.google.redirect') }}" class="btn-openits btn-openits-google btn-openits-block auth-google-btn">
                    <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.083 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C33.64 6.053 29.082 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                        <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 16.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C33.64 6.053 29.082 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                        <path fill="#4CAF50" d="M24 44c5.016 0 9.574-1.917 13.047-5.043l-6.019-4.915C29.083 36 24.514 32.657 22.098 28H6.306v8.069C9.656 39.663 16.318 44 24 44z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l6.019 4.915C42.002 35.952 44 30.138 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                    </svg>
                    @lang('google.button.sign_in_with_google')
                </a>
            @endif

            @if($ldapLoginEnabled ?? false)
                <form method="POST" action="{{ route('auth.ldap.login') }}" class="auth-ldap-form">
                    @csrf

                    <div class="form-group">
                        <label for="ldap_username">@lang('ldap.form.username')</label>
                        <input
                            id="ldap_username"
                            type="text"
                            class="form-control-openits @error('username') is-invalid @enderror"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            autocomplete="username"
                            placeholder="jdoe"
                        >
                        @error('username')
                            <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="ldap_password">@lang('ldap.form.password')</label>
                        <div class="password-toggle">
                            <input
                                id="ldap_password"
                                type="password"
                                class="form-control-openits @error('password') is-invalid @enderror"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your LDAP password"
                            >
                            <button type="button" class="toggle-btn" data-toggle-password="ldap_password">Show</button>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    @if(count($ldapDomains ?? []) > 1)
                        <div class="form-group">
                            <label for="ldap_domain">@lang('ldap.form.domain')</label>
                            <select id="ldap_domain" name="domain" class="form-control-openits" required>
                                <option value="">@lang('ldap.form.select_domain')</option>
                                @foreach($ldapDomains as $domain)
                                    <option value="{{ $domain }}" {{ old('domain') === $domain ? 'selected' : '' }}>{{ $domain }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif(count($ldapDomains ?? []) === 1)
                        <input type="hidden" name="domain" value="{{ $ldapDomains[0] }}">
                    @endif

                    <button type="submit" class="btn-openits btn-openits-block auth-ldap-btn">
                        @lang('ldap.button.sign_in_with_ldap')
                    </button>
                </form>
            @endif

            @if(($googleLoginEnabled ?? false) || ($ldapLoginEnabled ?? false))
                <div class="auth-divider"><span>or</span></div>
            @endif

            @if(config('login.show_demo_credentials'))
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
            @endif

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

            @if (Route::has('register'))
            <p class="auth-footer-text">
                Don't have an account?
                <a href="{{ route('register') }}">Create one</a>
            </p>
            @endif
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

    .auth-alert {
        margin-bottom: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
    }

    .auth-alert-error {
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #b91c1c;
    }

    .auth-google-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        margin-bottom: 1rem;
        background: #fff;
        color: var(--text);
        border: 1px solid var(--border);
    }

    .auth-google-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .auth-ldap-form {
        margin-bottom: 1rem;
    }

    .auth-ldap-btn {
        margin-top: 0.5rem;
        background: #1e3a5f;
        color: #fff;
    }

    .auth-ldap-btn:hover {
        opacity: 0.92;
        color: #fff;
    }

    .auth-divider {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0 0 1.25rem;
        color: var(--text-muted);
        font-size: 0.85rem;
    }

    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
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
