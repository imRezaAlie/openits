<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures Google OAuth routes are only accessible when the feature is enabled.
 */
class GoogleLoginEnabled
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->settings->isGoogleLoginEnabled()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'enabled' => false,
                'message' => __('google.errors.disabled'),
            ], 404);
        }

        return redirect()
            ->route('login')
            ->with('error', __('google.errors.disabled'));
    }
}
