<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Cached application settings with environment fallbacks.
 */
class SettingsService
{
    public const CACHE_KEY_PREFIX = 'settings.';

    public const CACHE_TTL_SECONDS = 3600;

    /**
     * Retrieve a setting value, using cache and env fallbacks when needed.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            self::CACHE_KEY_PREFIX.$key,
            self::CACHE_TTL_SECONDS,
            fn () => $this->resolveFromDatabaseOrEnv($key, $default)
        );
    }

    /**
     * Persist a setting and refresh its cache entry.
     */
    public function set(string $key, mixed $value, string $type = 'string'): Setting
    {
        $storedValue = $this->serializeValue($value, $type);

        $setting = DB::transaction(function () use ($key, $storedValue, $type) {
            return Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $storedValue,
                    'type' => $type,
                ]
            );
        });

        $this->forgetCache($key);

        return $setting;
    }

    /**
     * Determine whether Google OAuth login is enabled.
     */
    public function isGoogleLoginEnabled(): bool
    {
        return (bool) $this->get(Setting::KEY_GOOGLE_LOGIN_ENABLED, false);
    }

    /**
     * Update the Google login enabled flag.
     */
    public function setGoogleLoginEnabled(bool $enabled): Setting
    {
        return $this->set(
            Setting::KEY_GOOGLE_LOGIN_ENABLED,
            $enabled,
            Setting::TYPE_BOOLEAN
        );
    }

    /**
     * Verify Google OAuth credentials are configured in the environment.
     */
    public function googleCredentialsConfigured(): bool
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirect = config('services.google.redirect');

        return filled($clientId) && filled($clientSecret) && filled($redirect);
    }

    /**
     * Clear cached value for a setting key.
     */
    public function forgetCache(string $key): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX.$key);
    }

    /**
     * Clear all settings cache entries.
     */
    public function flushCache(): void
    {
        $keys = Setting::query()->pluck('key');

        foreach ($keys as $key) {
            $this->forgetCache($key);
        }
    }

    /**
     * Resolve a setting from the database or fall back to config/env defaults.
     */
    protected function resolveFromDatabaseOrEnv(string $key, mixed $default): mixed
    {
        $setting = Setting::query()->where('key', $key)->first();

        if ($setting !== null) {
            return $setting->getCastValue();
        }

        if ($key === Setting::KEY_GOOGLE_LOGIN_ENABLED) {
            return filter_var(config('settings.google_login_enabled', $default), FILTER_VALIDATE_BOOLEAN);
        }

        return $default;
    }

    /**
     * Serialize a value for database storage.
     */
    protected function serializeValue(mixed $value, string $type): string
    {
        return match ($type) {
            Setting::TYPE_BOOLEAN => $value ? '1' : '0',
            default => (string) $value,
        };
    }
}
