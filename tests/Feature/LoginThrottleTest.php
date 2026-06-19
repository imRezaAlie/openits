<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $settings = app(SettingsService::class);

        $settings->set(Setting::KEY_LDAP_SERVER, 'ldap.example.com');
        $settings->set(Setting::KEY_LDAP_PORT, 389, Setting::TYPE_INTEGER);
        $settings->set(Setting::KEY_LDAP_BASE_DN, 'DC=example,DC=com');
        $settings->set(Setting::KEY_LDAP_DOMAIN, 'example.com');
    }

    public function test_local_login_is_rate_limited_per_email_and_ip(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('login'))
                ->post(route('login'), [
                    'email' => 'user@example.com',
                    'password' => 'wrong-password',
                ])
                ->assertSessionHasErrors('email');
        }

        $response = $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'user@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Too many login attempts',
            (string) session('errors')->first('email')
        );
    }

    public function test_local_login_clears_credential_limit_after_success(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('login'), [
                'email' => 'user@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'correct-password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
    }

    public function test_ldap_login_is_rate_limited_after_repeated_failures(): void
    {
        config(['ldap.auto_provision' => true]);
        app(SettingsService::class)->setLdapLoginEnabled(true);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('login'))
                ->post(route('auth.ldap.login'), [
                    'username' => 'jdoe',
                    'password' => 'wrong',
                    'domain' => 'example.com',
                ])
                ->assertRedirect(route('login'));
        }

        $this->from(route('login'))
            ->post(route('auth.ldap.login'), [
                'username' => 'jdoe',
                'password' => 'wrong',
                'domain' => 'example.com',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertStringContainsString(
            'Too many login attempts',
            (string) session('error')
        );
    }
}
