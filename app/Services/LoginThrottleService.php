<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginThrottleService
{
    public function maxAttempts(): int
    {
        return max(1, (int) config('login.max_attempts', 5));
    }

    public function decaySeconds(): int
    {
        return max(1, (int) config('login.decay_minutes', 1)) * 60;
    }

    public function ipMaxAttempts(): int
    {
        return max(1, (int) config('login.ip_max_attempts', 20));
    }

    public function tooManyAttempts(Request $request, string $scope, ?string $identifier = null): bool
    {
        if ($this->tooManyIpAttempts($request, $scope)) {
            return true;
        }

        if ($identifier === null || $identifier === '') {
            return false;
        }

        return RateLimiter::tooManyAttempts(
            $this->credentialKey($scope, $request, $identifier),
            $this->maxAttempts()
        );
    }

    public function tooManyIpAttempts(Request $request, string $scope): bool
    {
        return RateLimiter::tooManyAttempts(
            $this->ipKey($scope, $request),
            $this->ipMaxAttempts()
        );
    }

    public function hitFailure(Request $request, string $scope, ?string $identifier = null): void
    {
        $this->hitIpFailure($request, $scope);

        if ($identifier !== null && $identifier !== '') {
            RateLimiter::hit(
                $this->credentialKey($scope, $request, $identifier),
                $this->decaySeconds()
            );
        }
    }

    public function hitIpFailure(Request $request, string $scope): void
    {
        RateLimiter::hit($this->ipKey($scope, $request), $this->decaySeconds());
    }

    public function clearCredential(Request $request, string $scope, string $identifier): void
    {
        RateLimiter::clear($this->credentialKey($scope, $request, $identifier));
    }

    public function availableIn(Request $request, string $scope, ?string $identifier = null): int
    {
        $seconds = RateLimiter::availableIn($this->ipKey($scope, $request));

        if ($identifier !== null && $identifier !== '') {
            $seconds = max(
                $seconds,
                RateLimiter::availableIn($this->credentialKey($scope, $request, $identifier))
            );
        }

        return $seconds;
    }

    public function lockoutMessage(Request $request, string $scope, ?string $identifier = null): string
    {
        $seconds = $this->availableIn($request, $scope, $identifier);

        return __('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => (int) ceil($seconds / 60),
        ]);
    }

    protected function credentialKey(string $scope, Request $request, string $identifier): string
    {
        return $scope.'|'.Str::transliterate(Str::lower($identifier)).'|'.$request->ip();
    }

    protected function ipKey(string $scope, Request $request): string
    {
        return $scope.'-ip|'.$request->ip();
    }
}
