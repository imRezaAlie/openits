<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestLdapConnectionRequest;
use App\Http\Requests\Admin\UpdateLdapSettingsRequest;
use App\Jobs\SyncLdapUsersJob;
use App\Models\LdapLog;
use App\Services\LdapService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLdapController extends Controller
{
    public function __construct(
        protected SettingsService $settings,
        protected LdapService $ldap
    ) {}

    /**
     * Display the LDAP settings page.
     */
    public function index(): View
    {
        return view('admin.settings.ldap', [
            'ldapLoginEnabled' => $this->settings->isLdapLoginEnabled(),
            'credentialsConfigured' => $this->settings->ldapCredentialsConfigured(),
            'ldapServer' => $this->settings->getLdapServer(),
            'ldapPort' => $this->settings->getLdapPort(),
            'ldapBaseDn' => $this->settings->getLdapBaseDn(),
            'ldapDomain' => $this->settings->getLdapDomain(),
            'useSsl' => (bool) config('ldap.use_ssl'),
            'useStartTls' => (bool) config('ldap.use_starttls'),
        ]);
    }

    /**
     * Test the LDAP server connection using saved or submitted settings (not persisted).
     */
    public function test(TestLdapConnectionRequest $request): JsonResponse
    {
        $overrides = $request->only([
            'ldap_server',
            'ldap_port',
            'ldap_base_dn',
            'ldap_domain',
        ]);

        $result = $this->ldap->testConnection($overrides !== [] ? $overrides : null);

        $this->ldap->logAttempt(
            LdapLog::ACTION_TEST,
            $result['success'] ? LdapLog::STATUS_SUCCESS : LdapLog::STATUS_FAILURE,
            null,
            $this->settings->getLdapDomain(),
            $result['message']
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Queue a bulk LDAP user synchronization.
     */
    public function sync(Request $request): JsonResponse|RedirectResponse
    {
        if (! $this->settings->ldapCredentialsConfigured()) {
            $message = __('ldap.errors.credentials_missing');

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->route('admin.settings.ldap')
                ->with('error', $message);
        }

        SyncLdapUsersJob::dispatch();

        $message = __('ldap.messages.sync_started');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()
            ->route('admin.settings.ldap')
            ->with('success', $message);
    }

    /**
     * Enable or disable LDAP login.
     */
    public function toggle(UpdateLdapSettingsRequest $request): JsonResponse|RedirectResponse
    {
        $enabled = $request->boolean('ldap_login_enabled');

        if ($request->hasAny(['ldap_server', 'ldap_port', 'ldap_base_dn', 'ldap_domain'])) {
            $this->settings->setLdapSettings($request->only([
                'ldap_server',
                'ldap_port',
                'ldap_base_dn',
                'ldap_domain',
                'ldap_login_enabled',
            ]));
        } else {
            $this->settings->setLdapLoginEnabled($enabled);
        }

        $message = $enabled
            ? __('ldap.messages.enabled')
            : __('ldap.messages.disabled');

        $this->ldap->logAttempt(
            LdapLog::ACTION_TOGGLE,
            LdapLog::STATUS_SUCCESS,
            null,
            $this->settings->getLdapDomain(),
            $message
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'enabled' => $enabled,
                'credentials_configured' => $this->settings->ldapCredentialsConfigured(),
            ]);
        }

        return redirect()
            ->route('admin.settings.ldap')
            ->with('success', $message);
    }

    /**
     * Save LDAP connection settings.
     */
    public function update(UpdateLdapSettingsRequest $request): JsonResponse|RedirectResponse
    {
        $this->settings->setLdapSettings($request->validated());

        $message = __('ldap.messages.settings_saved');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'credentials_configured' => $this->settings->ldapCredentialsConfigured(),
            ]);
        }

        return redirect()
            ->route('admin.settings.ldap')
            ->with('success', $message);
    }
}
