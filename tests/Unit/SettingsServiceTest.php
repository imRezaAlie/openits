<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    private SettingsService $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settings = app(SettingsService::class);
    }

    public function test_google_login_defaults_to_env_config_when_database_empty(): void
    {
        config(['settings.google_login_enabled' => true]);

        $this->assertTrue($this->settings->isGoogleLoginEnabled());
    }

    public function test_google_login_reads_from_database_and_caches_value(): void
    {
        $this->settings->setGoogleLoginEnabled(true);

        Setting::query()->where('key', Setting::KEY_GOOGLE_LOGIN_ENABLED)->update(['value' => '0']);
        Cache::forget(SettingsService::CACHE_KEY_PREFIX.Setting::KEY_GOOGLE_LOGIN_ENABLED);

        $this->settings->setGoogleLoginEnabled(false);

        $this->assertFalse($this->settings->isGoogleLoginEnabled());

        Setting::query()->where('key', Setting::KEY_GOOGLE_LOGIN_ENABLED)->update(['value' => '1']);
        $this->assertFalse($this->settings->isGoogleLoginEnabled());
    }

    public function test_cache_is_cleared_when_setting_is_updated(): void
    {
        $this->settings->setGoogleLoginEnabled(true);
        $this->assertTrue($this->settings->isGoogleLoginEnabled());

        $this->settings->setGoogleLoginEnabled(false);

        $this->assertFalse($this->settings->isGoogleLoginEnabled());
    }

    public function test_google_credentials_configured_requires_all_values(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => 'secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $this->assertFalse($this->settings->googleCredentialsConfigured());

        config([
            'services.google.client_id' => 'client-id',
            'services.google.client_secret' => 'secret',
        ]);

        $this->assertTrue($this->settings->googleCredentialsConfigured());
    }
}
