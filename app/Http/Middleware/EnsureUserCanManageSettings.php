<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict settings management to authenticated users.
 *
 * OpenITS currently treats all authenticated users as administrators.
 */
class EnsureUserCanManageSettings
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            abort(403, __('google.errors.unauthorized'));
        }

        return $next($request);
    }
}
