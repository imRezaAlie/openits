<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles Google OAuth user lookup, registration, and account linking.
 */
class GoogleAuthService
{
    /**
     * Find or create a local user from a Google Socialite profile.
     */
    public function findOrCreateUser(SocialiteUser $googleUser): User
    {
        $this->assertAllowedDomain($googleUser->getEmail());

        return DB::transaction(function () use ($googleUser) {
            $existingByGoogleId = User::query()
                ->where('google_id', $googleUser->getId())
                ->first();

            if ($existingByGoogleId !== null) {
                return $this->syncProfile($existingByGoogleId, $googleUser);
            }

            if (config('services.google.allow_email_linking', false)) {
                $existingByEmail = User::query()
                    ->where('email', $googleUser->getEmail())
                    ->first();

                if ($existingByEmail !== null) {
                    return $this->linkGoogleAccount($existingByEmail, $googleUser);
                }
            }

            if (! config('services.google.auto_provision', false)) {
                throw new HttpException(403, __('google.errors.not_provisioned'));
            }

            return $this->registerUser($googleUser);
        });
    }

    /**
     * Link Google credentials to an existing email-based account.
     */
    public function linkGoogleAccount(User $user, SocialiteUser $googleUser): User
    {
        $user->google_id = $googleUser->getId();
        $user->name = $googleUser->getName() ?? $user->name;
        $user->avatar = $googleUser->getAvatar() ?? $user->avatar;

        if ($user->email_verified_at === null) {
            $user->email_verified_at = now();
        }

        $user->save();

        return $user;
    }

    /**
     * Register a new user from Google OAuth data.
     */
    public function registerUser(SocialiteUser $googleUser): User
    {
        return User::create([
            'name' => $googleUser->getName() ?? 'Google User',
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(64)),
        ]);
    }

    /**
     * Keep profile fields in sync for returning Google users.
     */
    protected function syncProfile(User $user, SocialiteUser $googleUser): User
    {
        $user->name = $googleUser->getName() ?? $user->name;
        $user->avatar = $googleUser->getAvatar() ?? $user->avatar;

        if ($user->email_verified_at === null) {
            $user->email_verified_at = now();
        }

        $user->save();

        return $user;
    }

    protected function assertAllowedDomain(?string $email): void
    {
        $allowedDomains = config('services.google.allowed_domains', []);

        if ($allowedDomains === [] || $email === null) {
            return;
        }

        $domain = strtolower((string) strrchr($email, '@'));

        if ($domain === '' || ! in_array(ltrim($domain, '@'), $allowedDomains, true)) {
            throw new HttpException(403, __('google.errors.domain_not_allowed'));
        }
    }
}
