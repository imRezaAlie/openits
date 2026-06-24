<?php

namespace App\Services;

use App\Exceptions\LdapAuthenticationException;
use App\Models\LdapLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Native PHP LDAP operations for authentication, search, and sync.
 */
class LdapService
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * Test LDAP connectivity and optional service-account bind.
     *
     * @param  array<string, mixed>|null  $overrides
     * @return array{success: bool, message: string}
     */
    public function testConnection(?array $overrides = null): array
    {
        $this->ensureExtensionLoaded();

        $connection = null;
        $previousSettings = null;

        if ($overrides !== null) {
            $previousSettings = [
                'server' => $this->settings->getLdapServer(),
                'port' => $this->settings->getLdapPort(),
                'base_dn' => $this->settings->getLdapBaseDn(),
                'domain' => $this->settings->getLdapDomain(),
            ];

            $this->settings->setLdapSettings(array_filter([
                'ldap_server' => $overrides['ldap_server'] ?? null,
                'ldap_port' => $overrides['ldap_port'] ?? null,
                'ldap_base_dn' => $overrides['ldap_base_dn'] ?? null,
                'ldap_domain' => $overrides['ldap_domain'] ?? null,
            ], fn ($value) => $value !== null));
        }

        try {
            $connection = $this->connect();
            $this->bindServiceAccount($connection);

            return [
                'success' => true,
                'message' => __('ldap.messages.connection_success'),
            ];
        } catch (LdapAuthenticationException $exception) {
            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        } finally {
            $this->close($connection);

            if ($previousSettings !== null) {
                $this->settings->setLdapSettings([
                    'ldap_server' => $previousSettings['server'],
                    'ldap_port' => $previousSettings['port'],
                    'ldap_base_dn' => $previousSettings['base_dn'],
                    'ldap_domain' => $previousSettings['domain'],
                ]);
            }
        }
    }

    /**
     * Authenticate a user against LDAP and return normalized attributes.
     *
     * @return array<string, mixed>
     */
    public function authenticate(string $username, string $password, ?string $domain = null): array
    {
        $this->ensureExtensionLoaded();

        if ($username === '' || $password === '') {
            throw new LdapAuthenticationException(__('ldap.errors.invalid_credentials'), 'invalid_credentials');
        }

        $domain = $domain ?: $this->settings->getLdapDomain();
        $connection = null;

        try {
            $connection = $this->connect();
            $entry = $this->locateUserEntry($connection, $username, $domain);
            $userDn = $entry['dn'] ?? null;

            if ($userDn === null) {
                throw new LdapAuthenticationException(__('ldap.errors.user_not_found'), 'user_not_found');
            }

            if (! @ldap_bind($connection, $userDn, $password)) {
                throw new LdapAuthenticationException(__('ldap.errors.invalid_credentials'), 'invalid_credentials');
            }

            return $this->normalizeEntry($entry, $username, $domain);
        } catch (LdapAuthenticationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::warning('LDAP authentication failed', [
                'username' => $username,
                'message' => $exception->getMessage(),
            ]);

            throw new LdapAuthenticationException(__('ldap.errors.server_unreachable'), 'server_unreachable', $exception);
        } finally {
            $this->close($connection);
        }
    }

    /**
     * Fetch all directory users for synchronization.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAllUsers(): array
    {
        $this->ensureExtensionLoaded();

        $connection = null;

        try {
            $connection = $this->connect();
            $this->bindServiceAccount($connection);

            $baseDn = (string) $this->settings->getLdapBaseDn();
            $filter = $this->syncFilter();
            $attributes = $this->attributeList();

            $search = @ldap_search($connection, $baseDn, $filter, $attributes, 0, 0, (int) config('ldap.timeout', 5));

            if ($search === false) {
                throw new LdapAuthenticationException(__('ldap.errors.search_failed'), 'search_failed');
            }

            $entries = ldap_get_entries($connection, $search);
            $users = [];

            for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
                $entry = $entries[$i];
                $username = $this->extractUsernameFromEntry($entry);

                if ($username === null) {
                    continue;
                }

                $users[] = $this->normalizeEntry($entry, $username, $this->settings->getLdapDomain());
            }

            return $users;
        } finally {
            $this->close($connection);
        }
    }

    /**
     * Synchronize all LDAP users into the local database.
     */
    public function syncAllUsers(): int
    {
        $users = $this->fetchAllUsers();
        $authService = app(LdapAuthService::class);
        $count = 0;

        foreach ($users as $ldapUser) {
            $authService->syncUserFromLdap($ldapUser);
            $count++;
        }

        return $count;
    }

    /**
     * Record an LDAP audit log entry.
     *
     * @param  array<string, mixed>  $context
     */
    public function logAttempt(
        string $action,
        string $status,
        ?string $username = null,
        ?string $domain = null,
        ?string $message = null,
        ?int $userId = null,
        array $context = []
    ): LdapLog {
        return LdapLog::create([
            'username' => $username,
            'domain' => $domain,
            'action' => $action,
            'status' => $status,
            'ip_address' => request()->ip(),
            'message' => $message,
            'context' => $context === [] ? null : $context,
            'user_id' => $userId,
        ]);
    }

    /**
     * Build the user principal name / bind identifier for Active Directory.
     */
    public function buildBindIdentifier(string $username, ?string $domain = null): string
    {
        if ($this->isActiveDirectory()) {
            $domain = $domain ?: $this->settings->getLdapDomain();

            if (str_contains($username, '@') || str_contains($username, '\\')) {
                return $username;
            }

            return filled($domain) ? "{$username}@{$domain}" : $username;
        }

        return $username;
    }

    /**
     * @return array<int, string>
     */
    public function attributeList(): array
    {
        return config('ldap.attributes', [
            'samaccountname',
            'displayname',
            'mail',
            'distinguishedname',
            'memberof',
            'uid',
            'cn',
        ]);
    }

    public function isActiveDirectory(): bool
    {
        return strtolower((string) config('ldap.type', 'ad')) === 'ad';
    }

    public function connect(): \LDAP\Connection
    {
        $this->ensureExtensionLoaded();
        $this->ensureSecureInProduction();

        $server = (string) $this->settings->getLdapServer();
        $port = $this->settings->getLdapPort();
        $useSsl = (bool) config('ldap.use_ssl');

        $uri = $useSsl
            ? "ldaps://{$server}:{$port}"
            : "ldap://{$server}:{$port}";

        $connection = @ldap_connect($uri);

        if ($connection === false) {
            throw new LdapAuthenticationException(__('ldap.errors.connection_failed'), 'connection_failed');
        }

        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, (int) config('ldap.timeout', 5));

        if (config('ldap.use_starttls') && ! $useSsl) {
            if (! @ldap_start_tls($connection)) {
                throw new LdapAuthenticationException(__('ldap.errors.starttls_failed'), 'starttls_failed');
            }
        }

        return $connection;
    }

    /**
     * @param  \LDAP\Connection|null  $connection
     */
    protected function close($connection): void
    {
        if ($connection instanceof \LDAP\Connection) {
            @ldap_unbind($connection);
        }
    }

    protected function ensureExtensionLoaded(): void
    {
        if (! extension_loaded('ldap')) {
            throw new LdapAuthenticationException(__('ldap.errors.extension_missing'), 'extension_missing');
        }
    }

    protected function ensureSecureInProduction(): void
    {
        if (app()->environment('production')
            && ! config('ldap.use_ssl')
            && ! config('ldap.use_starttls')
            && ! config('ldap.allow_insecure', false)
        ) {
            throw new LdapAuthenticationException(__('ldap.errors.connection_failed'), 'insecure_connection');
        }
    }

    protected function bindServiceAccount(\LDAP\Connection $connection): void
    {
        $bindDn = config('ldap.bind_dn');
        $bindPassword = config('ldap.bind_password');

        if (filled($bindDn) && filled($bindPassword)) {
            if (! @ldap_bind($connection, $bindDn, $bindPassword)) {
                throw new LdapAuthenticationException(__('ldap.errors.bind_failed'), 'bind_failed');
            }

            return;
        }

        if (! @ldap_bind($connection)) {
            throw new LdapAuthenticationException(__('ldap.errors.bind_failed'), 'bind_failed');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function locateUserEntry(\LDAP\Connection $connection, string $username, ?string $domain): array
    {
        $baseDn = (string) $this->settings->getLdapBaseDn();
        $filter = $this->userFilter($username);
        $attributes = $this->attributeList();

        if (filled(config('ldap.bind_dn'))) {
            $this->bindServiceAccount($connection);
        } elseif (! @ldap_bind($connection)) {
            throw new LdapAuthenticationException(__('ldap.errors.bind_failed'), 'bind_failed');
        }

        $search = @ldap_search($connection, $baseDn, $filter, $attributes, 0, 1, (int) config('ldap.timeout', 5));

        if ($search === false) {
            throw new LdapAuthenticationException(__('ldap.errors.search_failed'), 'search_failed');
        }

        $entries = ldap_get_entries($connection, $search);

        if (($entries['count'] ?? 0) < 1) {
            throw new LdapAuthenticationException(__('ldap.errors.user_not_found'), 'user_not_found');
        }

        return $entries[0];
    }

    protected function userFilter(string $username): string
    {
        $custom = config('ldap.user_filter');

        if (filled($custom)) {
            return str_replace(':username', ldap_escape($username, '', LDAP_ESCAPE_FILTER), $custom);
        }

        $escaped = ldap_escape($username, '', LDAP_ESCAPE_FILTER);

        if ($this->isActiveDirectory()) {
            return "(&(objectClass=user)(objectCategory=person)(|(sAMAccountName={$escaped})(userPrincipalName={$escaped})(mail={$escaped})))";
        }

        return "(&(objectClass=inetOrgPerson)(|(uid={$escaped})(mail={$escaped})(cn={$escaped})))";
    }

    protected function syncFilter(): string
    {
        $custom = config('ldap.sync_filter');

        if (filled($custom)) {
            return $custom;
        }

        if ($this->isActiveDirectory()) {
            return '(&(objectClass=user)(objectCategory=person))';
        }

        return '(objectClass=inetOrgPerson)';
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    public function normalizeEntry(array $entry, string $username, ?string $domain): array
    {
        $samAccountName = $this->firstAttribute($entry, ['samaccountname', 'uid', 'cn']) ?? $username;
        $displayName = $this->firstAttribute($entry, ['displayname', 'cn', 'uid']) ?? $samAccountName;
        $email = $this->firstAttribute($entry, ['mail', 'userprincipalname']);
        $distinguishedName = $entry['dn'] ?? $this->firstAttribute($entry, ['distinguishedname']);
        $groups = $this->groupAttributeValues($entry);

        if (! filled($email)) {
            $fallbackDomain = $domain ?: $this->settings->getLdapDomain();
            $email = filled($fallbackDomain)
                ? strtolower("{$samAccountName}@{$fallbackDomain}")
                : strtolower("{$samAccountName}@ldap.local");
        }

        return [
            'ldap_username' => $username,
            'ldap_domain' => $domain,
            'ldap_samaccountname' => $samAccountName,
            'ldap_distinguished_name' => $distinguishedName,
            'ldap_groups' => $groups,
            'name' => $displayName,
            'email' => strtolower((string) $email),
        ];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<int, string>
     */
    protected function groupAttributeValues(array $entry): array
    {
        if (! isset($entry['memberof'])) {
            return [];
        }

        $groups = [];
        $count = (int) ($entry['memberof']['count'] ?? 0);

        for ($i = 0; $i < $count; $i++) {
            $groups[] = (string) $entry['memberof'][$i];
        }

        return $groups;
    }

    /**
     * @param  array<string, mixed>  $entry
     * @param  array<int, string>  $keys
     */
    protected function firstAttribute(array $entry, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($entry[$key][0]) && filled($entry[$key][0])) {
                return (string) $entry[$key][0];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    protected function extractUsernameFromEntry(array $entry): ?string
    {
        return $this->firstAttribute($entry, ['samaccountname', 'uid', 'cn']);
    }
}
