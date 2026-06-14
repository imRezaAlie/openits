<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeploymentAuthorized
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('deployment.enabled')) {
            abort(404);
        }

        $token = config('deployment.token');

        if (! is_string($token) || $token === '') {
            abort(404);
        }

        $provided = $request->header('X-Deployment-Token');

        if (! is_string($provided) || ! hash_equals($token, $provided)) {
            abort(404);
        }

        return $next($request);
    }
}
