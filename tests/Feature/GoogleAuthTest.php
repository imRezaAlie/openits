<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\GoogleAuthService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    public function test_status_endpoint_reports_disabled_by_default(): void
    {
        $this->getJson(route('auth.google.status'))
            ->assertOk()
            ->assertJson([
                'enabled' => false,
                'credentials_configured' => true,
                'redirect_url' => null,
            ]);
    }

    public function test_status_endpoint_reports_enabled_when_setting_is_on(): void
    {
        app(SettingsService::class)->setGoogleLoginEnabled(true);

        $this->getJson(route('auth.google.status'))
            ->assertOk()
            ->assertJson([
                'enabled' => true,
                'credentials_configured' => true,
            ])
            ->assertJsonPath('redirect_url', route('auth.google.redirect'));
    }

    public function test_google_redirect_is_blocked_when_disabled(): void
    {
        $this->get(route('auth.google.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', __('google.errors.disabled'));
    }

    public function test_google_redirect_is_blocked_with_json_when_disabled(): void
    {
        $this->getJson(route('auth.google.redirect'))
            ->assertNotFound()
            ->assertJson([
                'enabled' => false,
                'message' => __('google.errors.disabled'),
            ]);
    }

    public function test_google_redirect_works_when_enabled(): void
    {
        app(SettingsService::class)->setGoogleLoginEnabled(true);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturnSelf();

        Socialite::shouldReceive('scopes')
            ->once()
            ->with(['openid', 'profile', 'email'])
            ->andReturnSelf();

        Socialite::shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        $this->get(route('auth.google.redirect'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_login_page_hides_google_button_when_disabled(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee(__('google.button.sign_in_with_google'), false);
    }

    public function test_login_page_shows_google_button_when_enabled(): void
    {
        app(SettingsService::class)->setGoogleLoginEnabled(true);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(__('google.button.sign_in_with_google'), false);
    }

    public function test_admin_can_toggle_google_login_setting(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->putJson(route('admin.settings.google.update'), [
                'google_login_enabled' => true,
            ])
            ->assertOk()
            ->assertJson([
                'enabled' => true,
            ]);

        $this->assertTrue(app(SettingsService::class)->isGoogleLoginEnabled());
    }

    public function test_admin_cannot_enable_google_login_without_credentials(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->putJson(route('admin.settings.google.update'), [
                'google_login_enabled' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['google_login_enabled']);
    }

    public function test_settings_page_requires_authentication(): void
    {
        $this->get(route('admin.settings.index'))
            ->assertRedirect(route('login'));
    }

    public function test_google_callback_creates_user_when_enabled(): void
    {
        config(['services.google.auto_provision' => true]);

        app(SettingsService::class)->setGoogleLoginEnabled(true);

        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('google-123');
        $googleUser->shouldReceive('getName')->andReturn('Google Tester');
        $googleUser->shouldReceive('getEmail')->andReturn('google@example.com');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.png');

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturnSelf();

        Socialite::shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'google_id' => 'google-123',
        ]);

        $this->assertAuthenticated();
    }

    public function test_google_callback_links_existing_user_by_email(): void
    {
        config(['services.google.allow_email_linking' => true]);

        app(SettingsService::class)->setGoogleLoginEnabled(true);

        $existing = User::factory()->create([
            'email' => 'existing@example.com',
            'google_id' => null,
        ]);

        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('google-link-1');
        $googleUser->shouldReceive('getName')->andReturn('Linked User');
        $googleUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/linked.png');

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturnSelf();

        Socialite::shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('home'));

        $existing->refresh();

        $this->assertSame('google-link-1', $existing->google_id);
        $this->assertAuthenticatedAs($existing);
    }

    public function test_google_auth_service_registers_new_user(): void
    {
        config(['services.google.auto_provision' => true]);

        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('svc-google-1');
        $googleUser->shouldReceive('getName')->andReturn('Service User');
        $googleUser->shouldReceive('getEmail')->andReturn('service@example.com');
        $googleUser->shouldReceive('getAvatar')->andReturn(null);

        $user = app(GoogleAuthService::class)->findOrCreateUser($googleUser);

        $this->assertSame('service@example.com', $user->email);
        $this->assertSame('svc-google-1', $user->google_id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
