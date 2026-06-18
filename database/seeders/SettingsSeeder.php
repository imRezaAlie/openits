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
    }
}
