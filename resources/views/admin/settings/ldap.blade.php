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

        .settings-page .form-check.form-switch {
            padding-left: 2.75em;
        }

        .settings-page .form-check.form-switch .form-check-input {
            width: 2.75em;
            height: 1.4em;
            margin-left: -2.75em;
        }

        .settings-page .ldap-actions .btn {
            min-width: 140px;
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
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">@lang('ldap.settings.title')</h4>
                    <small class="text-muted">@lang('ldap.settings.ldap_login_help')</small>
                </div>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">
                    @lang('ldap.settings.back_to_settings')
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card settings-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">@lang('ldap.settings.ldap_login')</h5>
                        <span id="ldap-login-status-badge" class="badge {{ $ldapLoginEnabled ? 'badge-success' : 'badge-secondary' }}">
                            {{ $ldapLoginEnabled ? __('ldap.settings.enabled') : __('ldap.settings.disabled') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div id="ldap-credentials-alert">
                            @unless($credentialsConfigured)
                                <div class="alert alert-warning mb-3">
                                    @lang('ldap.settings.credentials_warning')
                                </div>
                            @else
                                <div class="alert alert-success mb-3">
                                    @lang('ldap.settings.credentials_ok')
                                </div>
                            @endunless
                        </div>

                        @if($useSsl || $useStartTls)
                            <div class="alert alert-info mb-3">
                                {{ $useSsl ? 'LDAPS is enabled via LDAP_USE_SSL.' : 'STARTTLS is enabled via LDAP_USE_STARTTLS.' }}
                            </div>
                        @endif

                        <div id="ldap-settings-feedback" class="alert d-none" role="alert"></div>

                        <form id="ldap-settings-form">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="ldap_server" class="form-label">@lang('ldap.settings.server')</label>
                                    <input type="text" class="form-control" id="ldap_server" name="ldap_server" value="{{ old('ldap_server', $ldapServer) }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="ldap_port" class="form-label">@lang('ldap.settings.port')</label>
                                    <input type="number" class="form-control" id="ldap_port" name="ldap_port" value="{{ old('ldap_port', $ldapPort) }}" min="1" max="65535" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ldap_base_dn" class="form-label">@lang('ldap.settings.base_dn')</label>
                                    <input type="text" class="form-control" id="ldap_base_dn" name="ldap_base_dn" value="{{ old('ldap_base_dn', $ldapBaseDn) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ldap_domain" class="form-label">@lang('ldap.settings.domain')</label>
                                    <input type="text" class="form-control" id="ldap_domain" name="ldap_domain" value="{{ old('ldap_domain', $ldapDomain) }}" required>
                                </div>
                            </div>

                            <div class="form-check form-switch mb-4">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="ldap_login_enabled"
                                    name="ldap_login_enabled"
                                    value="1"
                                    {{ $ldapLoginEnabled ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="ldap_login_enabled">
                                    @lang('ldap.settings.ldap_login')
                                </label>
                            </div>

                            <div class="d-flex flex-wrap gap-2 ldap-actions">
                                <button type="submit" class="btn btn-primary" id="ldap-settings-save">
                                    <span class="save-label">@lang('ldap.settings.save')</span>
                                    <span class="saving-label d-none">@lang('ldap.settings.saving')</span>
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="ldap-test-btn">
                                    <span class="test-label">@lang('ldap.button.test_connection')</span>
                                    <span class="testing-label d-none">@lang('ldap.settings.testing')</span>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="ldap-sync-btn">
                                    <span class="sync-label">@lang('ldap.button.sync_users')</span>
                                    <span class="syncing-label d-none">@lang('ldap.settings.syncing')</span>
                                </button>
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
    <script>
        (function () {
            var form = document.getElementById('ldap-settings-form');
            var toggle = document.getElementById('ldap_login_enabled');
            var saveBtn = document.getElementById('ldap-settings-save');
            var testBtn = document.getElementById('ldap-test-btn');
            var syncBtn = document.getElementById('ldap-sync-btn');
            var feedback = document.getElementById('ldap-settings-feedback');
            var badge = document.getElementById('ldap-login-status-badge');
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var updateUrl = @json(route('admin.settings.ldap.update'));
            var testUrl = @json(route('admin.ldap.test'));
            var syncUrl = @json(route('admin.ldap.sync'));
            var enabledLabel = @json(__('ldap.settings.enabled'));
            var disabledLabel = @json(__('ldap.settings.disabled'));

            function formPayload() {
                return {
                    ldap_server: document.getElementById('ldap_server').value,
                    ldap_port: parseInt(document.getElementById('ldap_port').value, 10),
                    ldap_base_dn: document.getElementById('ldap_base_dn').value,
                    ldap_domain: document.getElementById('ldap_domain').value,
                    ldap_login_enabled: toggle.checked,
                };
            }

            function showFeedback(type, message) {
                feedback.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info');
                feedback.classList.add(type === 'success' ? 'alert-success' : (type === 'info' ? 'alert-info' : 'alert-danger'));
                feedback.textContent = message;
            }

            function updateBadge(enabled) {
                badge.textContent = enabled ? enabledLabel : disabledLabel;
                badge.classList.toggle('badge-success', enabled);
                badge.classList.toggle('badge-secondary', !enabled);
            }

            function setButtonLoading(button, isLoading, activeClass, idleClass) {
                button.disabled = isLoading;
                button.querySelector('.' + activeClass).classList.toggle('d-none', isLoading);
                button.querySelector('.' + idleClass).classList.toggle('d-none', !isLoading);
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                setButtonLoading(saveBtn, true, 'saving-label', 'save-label');
                feedback.classList.add('d-none');

                fetch(updateUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(formPayload()),
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            var message = result.data.message
                                || (result.data.errors && Object.values(result.data.errors).flat()[0])
                                || 'Unable to update settings.';
                            showFeedback('error', message);
                            return;
                        }

                        showFeedback('success', result.data.message);
                        updateBadge(toggle.checked);
                    })
                    .catch(function () {
                        showFeedback('error', 'Network error while saving settings.');
                    })
                    .finally(function () {
                        setButtonLoading(saveBtn, false, 'saving-label', 'save-label');
                    });
            });

            testBtn.addEventListener('click', function () {
                setButtonLoading(testBtn, true, 'testing-label', 'test-label');
                feedback.classList.add('d-none');

                fetch(testUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(formPayload()),
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function (result) {
                        showFeedback(result.ok ? 'success' : 'error', result.data.message);
                    })
                    .catch(function () {
                        showFeedback('error', 'Network error while testing connection.');
                    })
                    .finally(function () {
                        setButtonLoading(testBtn, false, 'testing-label', 'test-label');
                    });
            });

            syncBtn.addEventListener('click', function () {
                setButtonLoading(syncBtn, true, 'syncing-label', 'sync-label');
                feedback.classList.add('d-none');

                fetch(syncUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function (result) {
                        showFeedback(result.ok ? 'info' : 'error', result.data.message);
                    })
                    .catch(function () {
                        showFeedback('error', 'Network error while starting sync.');
                    })
                    .finally(function () {
                        setButtonLoading(syncBtn, false, 'syncing-label', 'sync-label');
                    });
            });
        })();
    </script>
@endpush
