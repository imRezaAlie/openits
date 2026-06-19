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
     * Determine whether LDAP login is enabled.
     */
    public function isLdapLoginEnabled(): bool
    {
        return (bool) $this->get(Setting::KEY_LDAP_LOGIN_ENABLED, false);
    }

    /**
     * Update the LDAP login enabled flag.
     */
    public function setLdapLoginEnabled(bool $enabled): Setting
    {
        return $this->set(
            Setting::KEY_LDAP_LOGIN_ENABLED,
            $enabled,
            Setting::TYPE_BOOLEAN
        );
    }

    /**
     * Get the configured LDAP server hostname.
     */
    public function getLdapServer(): ?string
    {
        $value = $this->get(Setting::KEY_LDAP_SERVER);

        return filled($value) ? (string) $value : null;
    }

    /**
     * Get the configured LDAP port.
     */
    public function getLdapPort(): int
    {
        $value = $this->get(Setting::KEY_LDAP_PORT);

        return (int) ($value ?: config('ldap.port', 389));
    }

    /**
     * Get the configured LDAP base DN.
     */
    public function getLdapBaseDn(): ?string
    {
        $value = $this->get(Setting::KEY_LDAP_BASE_DN);

        return filled($value) ? (string) $value : null;
    }

    /**
     * Get the configured LDAP domain.
     */
    public function getLdapDomain(): ?string
    {
        $value = $this->get(Setting::KEY_LDAP_DOMAIN);

        return filled($value) ? (string) $value : null;
    }

    /**
     * Persist LDAP connection settings from the admin panel.
     *
     * @param  array<string, mixed>  $settings
     */
    public function setLdapSettings(array $settings): void
    {
        if (array_key_exists('ldap_server', $settings)) {
            $this->set(Setting::KEY_LDAP_SERVER, (string) $settings['ldap_server']);
        }

        if (array_key_exists('ldap_port', $settings)) {
            $this->set(Setting::KEY_LDAP_PORT, (int) $settings['ldap_port'], Setting::TYPE_INTEGER);
        }

        if (array_key_exists('ldap_base_dn', $settings)) {
            $this->set(Setting::KEY_LDAP_BASE_DN, (string) $settings['ldap_base_dn']);
        }

        if (array_key_exists('ldap_domain', $settings)) {
            $this->set(Setting::KEY_LDAP_DOMAIN, (string) $settings['ldap_domain']);
        }

        if (array_key_exists('ldap_login_enabled', $settings)) {
            $this->setLdapLoginEnabled((bool) $settings['ldap_login_enabled']);
        }
    }

    /**
     * Verify LDAP connection settings are sufficiently configured.
     */
    public function ldapCredentialsConfigured(): bool
    {
        return filled($this->getLdapServer())
            && filled($this->getLdapBaseDn())
            && filled($this->getLdapDomain())
            && $this->getLdapPort() > 0;
    }

    /**
     * Return merged LDAP configuration from database and environment.
     *
     * @return array<string, mixed>
     */
    public function getLdapConfig(): array
    {
        return [
            'enabled' => $this->isLdapLoginEnabled(),
            'server' => $this->getLdapServer() ?? config('ldap.server'),
            'port' => $this->getLdapPort(),
            'base_dn' => $this->getLdapBaseDn() ?? config('ldap.base_dn'),
            'domain' => $this->getLdapDomain() ?? config('ldap.domain'),
            'use_ssl' => (bool) config('ldap.use_ssl'),
            'use_starttls' => (bool) config('ldap.use_starttls'),
            'bind_dn' => config('ldap.bind_dn'),
            'bind_password' => config('ldap.bind_password'),
            'type' => config('ldap.type', 'ad'),
            'timeout' => (int) config('ldap.timeout', 5),
            'domains' => $this->getAvailableLdapDomains(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableLdapDomains(): array
    {
        $domains = config('ldap.domains', []);

        if ($domains !== []) {
            return array_values($domains);
        }

        $domain = $this->getLdapDomain() ?? config('ldap.domain');

        return filled($domain) ? [(string) $domain] : [];
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

        if ($key === Setting::KEY_LDAP_LOGIN_ENABLED) {
            return filter_var(config('settings.ldap_login_enabled', $default), FILTER_VALIDATE_BOOLEAN);
        }

        if ($key === Setting::KEY_LDAP_SERVER) {
            return config('settings.ldap_server', $default);
        }

        if ($key === Setting::KEY_LDAP_PORT) {
            return (int) config('settings.ldap_port', $default ?? 389);
        }

        if ($key === Setting::KEY_LDAP_BASE_DN) {
            return config('settings.ldap_base_dn', $default);
        }

        if ($key === Setting::KEY_LDAP_DOMAIN) {
            return config('settings.ldap_domain', $default);
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
            Setting::TYPE_INTEGER => (string) (int) $value,
            default => (string) $value,
        };
    }
}
