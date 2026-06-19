<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGoogleSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * Display the admin settings page.
     */
    public function index(): View
    {
        return view('admin.settings.index', [
            'googleLoginEnabled' => $this->settings->isGoogleLoginEnabled(),
            'credentialsConfigured' => $this->settings->googleCredentialsConfigured(),
            'googleClientId' => config('services.google.client_id'),
            'googleRedirectUri' => config('services.google.redirect'),
        ]);
    }

    /**
     * Toggle Google login and refresh cached settings.
     */
    public function updateGoogleSettings(UpdateGoogleSettingsRequest $request): RedirectResponse|JsonResponse
    {
        $enabled = $request->boolean('google_login_enabled');

        $this->settings->setGoogleLoginEnabled($enabled);

        $message = $enabled
            ? __('google.messages.enabled')
            : __('google.messages.disabled');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'enabled' => $enabled,
                'credentials_configured' => $this->settings->googleCredentialsConfigured(),
            ]);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', $message);
    }
}
