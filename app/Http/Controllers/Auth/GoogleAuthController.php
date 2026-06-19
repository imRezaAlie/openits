<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthService;
use App\Services\LoginThrottleService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class GoogleAuthController extends Controller
{
    public function __construct(
        protected SettingsService $settings,
        protected GoogleAuthService $googleAuth,
        protected LoginThrottleService $loginThrottle
    ) {}

    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function redirectToGoogle(): RedirectResponse|Response
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Handle the OAuth callback from Google.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            if (empty($googleUser->getEmail())) {
                return redirect()
                    ->route('login')
                    ->with('error', __('google.errors.email_missing'));
            }

            $user = $this->googleAuth->findOrCreateUser($googleUser);

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        } catch (\Throwable $exception) {
            Log::warning('Google OAuth callback failed', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->with('error', __('google.errors.oauth_failed'));
        }
    }

    /**
     * Alternative POST endpoint for API-driven Google login flows.
     */
    public function loginWithGoogle(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'access_token' => ['required', 'string'],
        ]);

        $throttleKey = strtolower((string) $validated['access_token']);

        if ($this->loginThrottle->tooManyAttempts($request, 'google', $throttleKey)) {
            return $this->oauthErrorResponse(
                $this->loginThrottle->lockoutMessage($request, 'google', $throttleKey),
                429
            );
        }

        try {
            $googleUser = Socialite::driver('google')->userFromToken($validated['access_token']);

            if (empty($googleUser->getEmail())) {
                return $this->oauthErrorResponse(__('google.errors.email_missing'), 422);
            }

            $user = $this->googleAuth->findOrCreateUser($googleUser);

            Auth::login($user, true);
            $request->session()->regenerate();

            $this->loginThrottle->clearCredential($request, 'google', $throttleKey);

            if ($request->expectsJson()) {
                $token = $user->createToken('google-login')->plainTextToken;

                return response()->json([
                    'message' => __('google.messages.login_success'),
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                    ],
                ]);
            }

            return redirect()->intended(route('home'));
        } catch (\Throwable $exception) {
            $this->loginThrottle->hitFailure($request, 'google', $throttleKey);

            Log::warning('Google token login failed', [
                'message' => $exception->getMessage(),
            ]);

            return $this->oauthErrorResponse(__('google.errors.oauth_failed'));
        }
    }

    /**
     * Public status endpoint for web and API clients.
     */
    public function status(Request $request): JsonResponse
    {
        $enabled = $this->settings->isGoogleLoginEnabled();
        $credentialsConfigured = $this->settings->googleCredentialsConfigured();

        return response()->json([
            'enabled' => $enabled,
            'credentials_configured' => $credentialsConfigured,
            'redirect_url' => $enabled ? route('auth.google.redirect') : null,
        ]);
    }

    /**
     * Return a consistent OAuth error response for web and API callers.
     */
    protected function oauthErrorResponse(string $message, int $status = 401): JsonResponse|RedirectResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()
            ->route('login')
            ->with('error', $message);
    }
}
