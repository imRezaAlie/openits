<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures LDAP authentication routes are only accessible when the feature is enabled.
 */
class LdapLoginEnabled
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->settings->isLdapLoginEnabled()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'enabled' => false,
                'message' => __('ldap.errors.disabled'),
            ], 403);
        }

        return redirect()
            ->route('login')
            ->with('error', __('ldap.errors.disabled'));
    }
}
