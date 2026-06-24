<?php

use App\Http\Middleware\EnsureDeploymentAuthorized;
use App\Http\Middleware\EnsureUserCanManageSettings;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\GoogleLoginEnabled;
use App\Http\Middleware\LdapLoginEnabled;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'run-deployment',
        ]);

        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'deployment.auth' => EnsureDeploymentAuthorized::class,
            'google.login.enabled' => GoogleLoginEnabled::class,
            'ldap.login.enabled' => LdapLoginEnabled::class,
            'settings.manage' => EnsureUserCanManageSettings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
