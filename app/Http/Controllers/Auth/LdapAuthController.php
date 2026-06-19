<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\LdapAuthenticationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LdapLoginRequest;
use App\Models\LdapLog;
use App\Services\LdapAuthService;
use App\Services\LdapService;
use App\Services\LoginThrottleService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LdapAuthController extends Controller
{
    public function __construct(
        protected SettingsService $settings,
        protected LdapService $ldap,
        protected LdapAuthService $ldapAuth,
        protected LoginThrottleService $loginThrottle
    ) {}

    /**
     * Authenticate a user via LDAP credentials.
     */
    public function login(LdapLoginRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $username = strtolower($validated['username']);

        if ($this->loginThrottle->tooManyAttempts($request, 'ldap', $username)) {
            return $this->ldapErrorResponse(
                $this->loginThrottle->lockoutMessage($request, 'ldap', $username),
                429
            );
        }

        try {
            $ldapUser = $this->ldap->authenticate(
                $validated['username'],
                $validated['password'],
                $validated['domain'] ?? null
            );

            $user = $this->ldapAuth->findOrCreateUser($ldapUser);

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            $this->loginThrottle->clearCredential($request, 'ldap', $username);

            $this->ldap->logAttempt(
                LdapLog::ACTION_LOGIN,
                LdapLog::STATUS_SUCCESS,
                $validated['username'],
                $validated['domain'] ?? $this->settings->getLdapDomain(),
                __('ldap.messages.login_success'),
                $user->id
            );

            if ($request->expectsJson()) {
                $token = $user->createToken('ldap-login')->plainTextToken;

                return response()->json([
                    'message' => __('ldap.messages.login_success'),
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ]);
            }

            return redirect()->intended(route('home'));
        } catch (LdapAuthenticationException $exception) {
            $this->loginThrottle->hitFailure($request, 'ldap', $username);

            $this->ldap->logAttempt(
                LdapLog::ACTION_LOGIN,
                LdapLog::STATUS_FAILURE,
                $validated['username'],
                $validated['domain'] ?? $this->settings->getLdapDomain(),
                $exception->getMessage(),
                null,
                ['error_code' => $exception->errorCode]
            );

            return $this->ldapErrorResponse(
                $this->clientAuthErrorMessage($exception->errorCode),
                $this->statusForError($exception->errorCode)
            );
        } catch (\Throwable $exception) {
            $this->loginThrottle->hitFailure($request, 'ldap', $username);

            Log::warning('LDAP login failed', [
                'username' => $validated['username'],
                'message' => $exception->getMessage(),
            ]);

            $message = config('ldap.fallback_to_local', true)
                ? __('ldap.errors.server_unreachable')
                : __('ldap.errors.connection_failed');

            $this->ldap->logAttempt(
                LdapLog::ACTION_LOGIN,
                LdapLog::STATUS_FAILURE,
                $validated['username'],
                $validated['domain'] ?? $this->settings->getLdapDomain(),
                $message
            );

            return $this->ldapErrorResponse($message);
        }
    }

    /**
     * Public status endpoint for web and API clients.
     */
    public function status(Request $request): JsonResponse
    {
        $enabled = $this->settings->isLdapLoginEnabled();
        $credentialsConfigured = $this->settings->ldapCredentialsConfigured();

        return response()->json([
            'enabled' => $enabled,
            'credentials_configured' => $credentialsConfigured,
            'domains' => $enabled ? $this->settings->getAvailableLdapDomains() : [],
            'login_url' => $enabled ? route('auth.ldap.login') : null,
        ]);
    }

    protected function ldapErrorResponse(string $message, int $status = 401): JsonResponse|RedirectResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()
            ->route('login')
            ->with('error', $message);
    }

    protected function statusForError(string $errorCode): int
    {
        return match ($errorCode) {
            'user_not_found', 'invalid_credentials', 'not_provisioned' => 401,
            'server_unreachable', 'connection_failed' => 503,
            default => 422,
        };
    }

    protected function clientAuthErrorMessage(string $errorCode): string
    {
        return match ($errorCode) {
            'server_unreachable', 'connection_failed' => config('ldap.fallback_to_local', true)
                ? __('ldap.errors.server_unreachable')
                : __('ldap.errors.connection_failed'),
            default => __('ldap.errors.invalid_credentials'),
        };
    }
}
