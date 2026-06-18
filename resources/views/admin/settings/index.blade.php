@extends('master')
@push('head-src')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .settings-page .settings-card .card-body {
            padding: 1.5rem 1.75rem;
        }

        .settings-page .settings-card .card-header {
            padding: 1rem 1.75rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .settings-page .settings-intro {
            font-size: 1rem;
            line-height: 1.6;
        }

        .settings-page .settings-meta dt {
            font-weight: 600;
            color: var(--text-dark, #312a2a);
            margin-bottom: 0.35rem;
        }

        .settings-page .settings-meta dd {
            margin-bottom: 1rem;
        }

        .settings-page .settings-meta dd:last-child {
            margin-bottom: 0;
        }

        .settings-page .settings-meta code {
            display: block;
            width: 100%;
            padding: 0.65rem 0.85rem;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            font-size: 0.95rem;
            word-break: break-all;
            white-space: normal;
        }

        .settings-page .form-check.form-switch {
            padding-left: 2.75em;
        }

        .settings-page .form-check.form-switch .form-check-input {
            width: 2.75em;
            height: 1.4em;
            margin-left: -2.75em;
        }

        .settings-page .form-check.form-switch .form-check-label {
            font-size: 1rem;
            font-weight: 500;
            padding-top: 0.15rem;
        }

        .settings-page #google-settings-save {
            min-width: 120px;
            padding: 0.55rem 1.25rem;
        }

        @media (min-width: 768px) {
            .settings-page .settings-meta dt {
                margin-bottom: 0;
            }

            .settings-page .settings-meta dd {
                margin-bottom: 1.25rem;
            }
        }
    </style>
@endpush

@section('body')
<div class="content-body settings-page">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-0">@lang('google.settings.title')</h4>
                <small class="text-muted">Manage authentication and integration settings</small>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card settings-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">@lang('google.settings.google_login')</h5>
                        <span id="google-login-status-badge" class="badge {{ $googleLoginEnabled ? 'badge-success' : 'badge-secondary' }}">
                            {{ $googleLoginEnabled ? __('google.settings.enabled') : __('google.settings.disabled') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3 settings-intro">@lang('google.settings.google_login_help')</p>

                        <div id="google-credentials-alert">
                            @unless($credentialsConfigured)
                                <div class="alert alert-warning mb-3">
                                    @lang('google.settings.credentials_warning')
                                </div>
                            @else
                                <div class="alert alert-success mb-3">
                                    @lang('google.settings.credentials_ok')
                                </div>
                            @endunless
                        </div>

                        <dl class="row mb-4 settings-meta">
                            <dt class="col-12 col-md-3">@lang('google.settings.client_id')</dt>
                            <dd class="col-12 col-md-9"><code>{{ $googleClientId ?: '—' }}</code></dd>
                            <dt class="col-12 col-md-3">@lang('google.settings.redirect_uri')</dt>
                            <dd class="col-12 col-md-9"><code>{{ $googleRedirectUri ?: '—' }}</code></dd>
                        </dl>

                        <div id="google-settings-feedback" class="alert d-none" role="alert"></div>

                        <form id="google-settings-form">
                            @csrf
                            @method('PUT')

                            <div class="form-check form-switch mb-3">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="google_login_enabled"
                                    name="google_login_enabled"
                                    value="1"
                                    {{ $googleLoginEnabled ? 'checked' : '' }}
                                    @unless($credentialsConfigured)
                                        @if(!$googleLoginEnabled) disabled @endif
                                    @endunless
                                >
                                <label class="form-check-label" for="google_login_enabled">
                                    @lang('google.settings.google_login')
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary" id="google-settings-save">
                                <span class="save-label">@lang('google.settings.save')</span>
                                <span class="saving-label d-none">@lang('google.settings.saving')</span>
                            </button>
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
    <script>
        (function () {
            var form = document.getElementById('google-settings-form');
            var toggle = document.getElementById('google_login_enabled');
            var saveBtn = document.getElementById('google-settings-save');
            var feedback = document.getElementById('google-settings-feedback');
            var badge = document.getElementById('google-login-status-badge');
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var updateUrl = @json(route('admin.settings.google.update'));
            var enabledLabel = @json(__('google.settings.enabled'));
            var disabledLabel = @json(__('google.settings.disabled'));
            var credentialsConfigured = @json($credentialsConfigured);

            function showFeedback(type, message) {
                feedback.classList.remove('d-none', 'alert-success', 'alert-danger');
                feedback.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
                feedback.textContent = message;
            }

            function setSaving(isSaving) {
                saveBtn.disabled = isSaving;
                saveBtn.querySelector('.save-label').classList.toggle('d-none', isSaving);
                saveBtn.querySelector('.saving-label').classList.toggle('d-none', !isSaving);
            }

            function updateBadge(enabled) {
                badge.textContent = enabled ? enabledLabel : disabledLabel;
                badge.classList.toggle('badge-success', enabled);
                badge.classList.toggle('badge-secondary', !enabled);
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                setSaving(true);
                feedback.classList.add('d-none');

                fetch(updateUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        google_login_enabled: toggle.checked,
                    }),
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            var message = result.data.message
                                || (result.data.errors && result.data.errors.google_login_enabled && result.data.errors.google_login_enabled[0])
                                || 'Unable to update settings.';

                            if (result.data.errors && result.data.errors.google_login_enabled) {
                                toggle.checked = false;
                            }

                            showFeedback('error', message);
                            updateBadge(toggle.checked);
                            return;
                        }

                        showFeedback('success', result.data.message);
                        updateBadge(result.data.enabled);
                        toggle.checked = result.data.enabled;

                        if (!credentialsConfigured && !result.data.enabled) {
                            toggle.disabled = true;
                        }
                    })
                    .catch(function () {
                        showFeedback('error', 'Network error while saving settings.');
                    })
                    .finally(function () {
                        setSaving(false);
                    });
            });
        })();
    </script>
@endpush
