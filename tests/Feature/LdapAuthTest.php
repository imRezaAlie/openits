<?php

namespace Tests\Feature;

use App\Exceptions\LdapAuthenticationException;
use App\Models\LdapLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\LdapAuthService;
use App\Services\LdapService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class LdapAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLdapSettings();
    }

    public function test_status_endpoint_reports_disabled_by_default(): void
    {
        $this->getJson(route('auth.ldap.status'))
            ->assertOk()
            ->assertJson([
                'enabled' => false,
                'credentials_configured' => true,
                'login_url' => null,
            ]);
    }

    public function test_status_endpoint_reports_enabled_when_setting_is_on(): void
    {
        app(SettingsService::class)->setLdapLoginEnabled(true);

        $this->getJson(route('auth.ldap.status'))
            ->assertOk()
            ->assertJson([
                'enabled' => true,
                'credentials_configured' => true,
            ])
            ->assertJsonPath('login_url', route('auth.ldap.login'));
    }

    public function test_ldap_login_is_blocked_when_disabled(): void
    {
        $this->post(route('auth.ldap.login'), [
            'username' => 'jdoe',
            'password' => 'secret',
            'domain' => 'example.com',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', __('ldap.errors.disabled'));
    }

    public function test_ldap_login_is_blocked_with_json_when_disabled(): void
    {
        $this->postJson('/api/auth/ldap', [
            'username' => 'jdoe',
            'password' => 'secret',
            'domain' => 'example.com',
        ])
            ->assertForbidden()
            ->assertJson([
                'enabled' => false,
                'message' => __('ldap.errors.disabled'),
            ]);
    }

    public function test_login_page_hides_ldap_form_when_disabled(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee(__('ldap.button.sign_in_with_ldap'), false);
    }

    public function test_login_page_shows_ldap_form_when_enabled(): void
    {
        app(SettingsService::class)->setLdapLoginEnabled(true);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(__('ldap.button.sign_in_with_ldap'), false);
    }

    public function test_admin_can_update_ldap_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->putJson(route('admin.settings.ldap.update'), [
                'ldap_server' => 'ldap.example.com',
                'ldap_port' => 636,
                'ldap_base_dn' => 'DC=example,DC=com',
                'ldap_domain' => 'example.com',
                'ldap_login_enabled' => false,
            ])
            ->assertOk()
            ->assertJson([
                'credentials_configured' => true,
            ]);

        $settings = app(SettingsService::class);
        $this->assertSame('ldap.example.com', $settings->getLdapServer());
        $this->assertSame(636, $settings->getLdapPort());
    }

    public function test_admin_cannot_enable_ldap_without_credentials(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->postJson(route('admin.ldap.toggle'), [
                'ldap_login_enabled' => true,
                'ldap_server' => '',
                'ldap_port' => 389,
                'ldap_base_dn' => '',
                'ldap_domain' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ldap_login_enabled']);
    }

    public function test_ldap_login_creates_user_when_enabled(): void
    {
        config(['ldap.auto_provision' => true]);

        app(SettingsService::class)->setLdapLoginEnabled(true);

        $ldapUser = [
            'ldap_username' => 'jdoe',
            'ldap_domain' => 'example.com',
            'ldap_samaccountname' => 'jdoe',
            'ldap_distinguished_name' => 'CN=John Doe,DC=example,DC=com',
            'ldap_groups' => ['CN=Users,DC=example,DC=com'],
            'name' => 'John Doe',
            'email' => 'jdoe@example.com',
        ];

        $ldap = Mockery::mock(LdapService::class);
        $ldap->shouldReceive('authenticate')
            ->once()
            ->with('jdoe', 'secret', 'example.com')
            ->andReturn($ldapUser);
        $ldap->shouldReceive('logAttempt')
            ->once()
            ->andReturn(new LdapLog);

        $this->app->instance(LdapService::class, $ldap);

        $this->post(route('auth.ldap.login'), [
            'username' => 'jdoe',
            'password' => 'secret',
            'domain' => 'example.com',
        ])
            ->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'jdoe@example.com',
            'ldap_samaccountname' => 'jdoe',
        ]);

        $this->assertAuthenticated();
    }

    public function test_ldap_login_returns_invalid_credentials_error(): void
    {
        app(SettingsService::class)->setLdapLoginEnabled(true);

        $ldap = Mockery::mock(LdapService::class);
        $ldap->shouldReceive('authenticate')
            ->once()
            ->andThrow(new LdapAuthenticationException(__('ldap.errors.invalid_credentials'), 'invalid_credentials'));
        $ldap->shouldReceive('logAttempt')
            ->once()
            ->andReturn(new LdapLog);

        $this->app->instance(LdapService::class, $ldap);

        $this->post(route('auth.ldap.login'), [
            'username' => 'jdoe',
            'password' => 'wrong',
            'domain' => 'example.com',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', __('ldap.errors.invalid_credentials'));
    }

    public function test_ldap_auth_service_registers_new_user(): void
    {
        config(['ldap.auto_provision' => true]);

        $ldapUser = [
            'ldap_username' => 'svc-ldap',
            'ldap_domain' => 'example.com',
            'ldap_samaccountname' => 'svc-ldap',
            'ldap_distinguished_name' => 'CN=Svc LDAP,DC=example,DC=com',
            'ldap_groups' => [],
            'name' => 'Svc LDAP',
            'email' => 'svc-ldap@example.com',
        ];

        $user = app(LdapAuthService::class)->findOrCreateUser($ldapUser);

        $this->assertSame('svc-ldap@example.com', $user->email);
        $this->assertSame('svc-ldap', $user->ldap_samaccountname);
    }

    public function test_ldap_settings_page_requires_authentication(): void
    {
        $this->get(route('admin.settings.ldap'))
            ->assertRedirect(route('login'));
    }

    public function test_ldap_settings_page_requires_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.settings.ldap'))
            ->assertForbidden();
    }

    protected function seedLdapSettings(): void
    {
        $settings = app(SettingsService::class);

        $settings->set(Setting::KEY_LDAP_SERVER, 'ldap.example.com');
        $settings->set(Setting::KEY_LDAP_PORT, 389, Setting::TYPE_INTEGER);
        $settings->set(Setting::KEY_LDAP_BASE_DN, 'DC=example,DC=com');
        $settings->set(Setting::KEY_LDAP_DOMAIN, 'example.com');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
