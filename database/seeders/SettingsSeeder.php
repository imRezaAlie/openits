<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed default application settings.
     */
    public function run(): void
    {
        /** @var SettingsService $settings */
        $settings = app(SettingsService::class);

        $settings->set(
            Setting::KEY_GOOGLE_LOGIN_ENABLED,
            filter_var(config('settings.google_login_enabled'), FILTER_VALIDATE_BOOLEAN),
            Setting::TYPE_BOOLEAN
        );

        $settings->set(
            Setting::KEY_LDAP_LOGIN_ENABLED,
            filter_var(config('settings.ldap_login_enabled'), FILTER_VALIDATE_BOOLEAN),
            Setting::TYPE_BOOLEAN
        );

        if (filled(config('settings.ldap_server'))) {
            $settings->set(Setting::KEY_LDAP_SERVER, (string) config('settings.ldap_server'));
        }

        if (filled(config('settings.ldap_port'))) {
            $settings->set(Setting::KEY_LDAP_PORT, (int) config('settings.ldap_port'), Setting::TYPE_INTEGER);
        }

        if (filled(config('settings.ldap_base_dn'))) {
            $settings->set(Setting::KEY_LDAP_BASE_DN, (string) config('settings.ldap_base_dn'));
        }

        if (filled(config('settings.ldap_domain'))) {
            $settings->set(Setting::KEY_LDAP_DOMAIN, (string) config('settings.ldap_domain'));
        }
    }
}
