<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\LdapService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LdapServiceTest extends TestCase
{
    use RefreshDatabase;

    private LdapService $ldap;

    private SettingsService $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settings = app(SettingsService::class);
        $this->settings->set('ldap_server', 'ldap.example.com');
        $this->settings->set('ldap_port', 389, Setting::TYPE_INTEGER);
        $this->settings->set('ldap_base_dn', 'DC=example,DC=com');
        $this->settings->set('ldap_domain', 'example.com');

        config(['ldap.type' => 'ad']);

        $this->ldap = app(LdapService::class);
    }

    public function test_build_bind_identifier_formats_active_directory_upn(): void
    {
        $this->assertSame(
            'jdoe@example.com',
            $this->ldap->buildBindIdentifier('jdoe', 'example.com')
        );
    }

    public function test_build_bind_identifier_preserves_existing_upn(): void
    {
        $this->assertSame(
            'jdoe@example.com',
            $this->ldap->buildBindIdentifier('jdoe@example.com', 'example.com')
        );
    }

    public function test_normalize_entry_maps_active_directory_attributes(): void
    {
        $entry = [
            'dn' => 'CN=John Doe,DC=example,DC=com',
            'samaccountname' => ['jdoe'],
            'displayname' => ['John Doe'],
            'mail' => ['jdoe@example.com'],
            'memberof' => [
                'count' => 1,
                0 => 'CN=Users,DC=example,DC=com',
            ],
        ];

        $normalized = $this->ldap->normalizeEntry($entry, 'jdoe', 'example.com');

        $this->assertSame('jdoe', $normalized['ldap_samaccountname']);
        $this->assertSame('John Doe', $normalized['name']);
        $this->assertSame('jdoe@example.com', $normalized['email']);
        $this->assertSame('CN=John Doe,DC=example,DC=com', $normalized['ldap_distinguished_name']);
        $this->assertSame(['CN=Users,DC=example,DC=com'], $normalized['ldap_groups']);
    }

    public function test_normalize_entry_generates_fallback_email_when_missing(): void
    {
        $entry = [
            'dn' => 'uid=jdoe,ou=people,dc=example,dc=com',
            'uid' => ['jdoe'],
            'cn' => ['John Doe'],
        ];

        config(['ldap.type' => 'openldap']);

        $normalized = $this->ldap->normalizeEntry($entry, 'jdoe', 'example.com');

        $this->assertSame('jdoe@example.com', $normalized['email']);
    }

    public function test_is_active_directory_reflects_config_type(): void
    {
        config(['ldap.type' => 'ad']);
        $this->assertTrue($this->ldap->isActiveDirectory());

        config(['ldap.type' => 'openldap']);
        $this->assertFalse($this->ldap->isActiveDirectory());
    }
}
