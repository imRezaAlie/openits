<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginThrottleService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        hasTooManyLoginAttempts as traitHasTooManyLoginAttempts;
        incrementLoginAttempts as traitIncrementLoginAttempts;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function maxAttempts(): int
    {
        return app(LoginThrottleService::class)->maxAttempts();
    }

    public function decayMinutes(): int
    {
        return (int) config('login.decay_minutes', 1);
    }

    protected function hasTooManyLoginAttempts(Request $request): bool
    {
        return $this->traitHasTooManyLoginAttempts($request)
            || app(LoginThrottleService::class)->tooManyIpAttempts($request, 'local');
    }

    protected function incrementLoginAttempts(Request $request): void
    {
        $this->traitIncrementLoginAttempts($request);

        app(LoginThrottleService::class)->hitIpFailure($request, 'local');
    }
}
