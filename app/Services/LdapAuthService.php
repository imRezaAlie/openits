<?php

namespace App\Services;

use App\Exceptions\LdapAuthenticationException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Handles LDAP user lookup, registration, and account linking.
 */
class LdapAuthService
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * Find or create a local user from LDAP attributes after successful bind.
     *
     * @param  array<string, mixed>  $ldapUser
     */
    public function findOrCreateUser(array $ldapUser): User
    {
        return DB::transaction(function () use ($ldapUser) {
            $this->assertAuthorizedGroups($ldapUser['ldap_groups'] ?? []);

            $existingBySam = null;

            if (filled($ldapUser['ldap_samaccountname'] ?? null)) {
                $existingBySam = User::query()
                    ->where('ldap_samaccountname', $ldapUser['ldap_samaccountname'])
                    ->first();
            }

            if ($existingBySam !== null) {
                return $this->syncUserFromLdap($ldapUser, $existingBySam);
            }

            $existingByUsername = User::query()
                ->where('ldap_username', $ldapUser['ldap_username'])
                ->when(filled($ldapUser['ldap_domain'] ?? null), function ($query) use ($ldapUser) {
                    $query->where('ldap_domain', $ldapUser['ldap_domain']);
                })
                ->first();

            if ($existingByUsername !== null) {
                return $this->syncUserFromLdap($ldapUser, $existingByUsername);
            }

            if (config('ldap.allow_email_linking', false)) {
                $existingByEmail = User::query()
                    ->where('email', $ldapUser['email'])
                    ->first();

                if ($existingByEmail !== null) {
                    return $this->linkLdapAccount($existingByEmail, $ldapUser);
                }
            }

            if (! config('ldap.auto_provision', false)) {
                throw new LdapAuthenticationException(
                    __('ldap.errors.not_provisioned'),
                    'not_provisioned'
                );
            }

            return $this->registerUser($ldapUser);
        });
    }

    /**
     * Sync LDAP attributes onto an existing local user.
     *
     * @param  array<string, mixed>  $ldapUser
     */
    public function syncUserFromLdap(array $ldapUser, ?User $user = null): User
    {
        if ($user === null) {
            $user = $this->findOrCreateUser($ldapUser);
        }

        $user->fill([
            'name' => $ldapUser['name'] ?? $user->name,
            'email' => $ldapUser['email'] ?? $user->email,
            'ldap_username' => $ldapUser['ldap_username'] ?? $user->ldap_username,
            'ldap_domain' => $ldapUser['ldap_domain'] ?? $user->ldap_domain,
            'ldap_samaccountname' => $ldapUser['ldap_samaccountname'] ?? $user->ldap_samaccountname,
            'ldap_distinguished_name' => $ldapUser['ldap_distinguished_name'] ?? $user->ldap_distinguished_name,
            'ldap_groups' => $ldapUser['ldap_groups'] ?? $user->ldap_groups,
            'ldap_last_sync_at' => now(),
        ]);

        if ($user->email_verified_at === null) {
            $user->email_verified_at = now();
        }

        $this->applyGroupRoleMapping($user, $ldapUser['ldap_groups'] ?? []);

        $user->save();

        return $user;
    }

    /**
     * Link LDAP credentials to an existing email-based account.
     *
     * @param  array<string, mixed>  $ldapUser
     */
    public function linkLdapAccount(User $user, array $ldapUser): User
    {
        return $this->syncUserFromLdap($ldapUser, $user);
    }

    /**
     * Register a new user from LDAP directory data.
     *
     * @param  array<string, mixed>  $ldapUser
     */
    public function registerUser(array $ldapUser): User
    {
        $user = User::create([
            'name' => $ldapUser['name'] ?? 'LDAP User',
            'email' => $ldapUser['email'],
            'ldap_username' => $ldapUser['ldap_username'] ?? null,
            'ldap_domain' => $ldapUser['ldap_domain'] ?? null,
            'ldap_samaccountname' => $ldapUser['ldap_samaccountname'] ?? null,
            'ldap_distinguished_name' => $ldapUser['ldap_distinguished_name'] ?? null,
            'ldap_groups' => $ldapUser['ldap_groups'] ?? [],
            'ldap_last_sync_at' => now(),
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(64)),
        ]);

        $this->applyGroupRoleMapping($user, $ldapUser['ldap_groups'] ?? []);
        $user->save();

        return $user;
    }

    /**
     * @param  array<int, string>  $groups
     */
    protected function assertAuthorizedGroups(array $groups): void
    {
        $allowedGroups = config('ldap.allowed_groups', []);

        if ($allowedGroups === []) {
            return;
        }

        foreach ($groups as $groupDn) {
            foreach ($allowedGroups as $allowedGroup) {
                if (strcasecmp($groupDn, $allowedGroup) === 0) {
                    return;
                }
            }
        }

        throw new LdapAuthenticationException(
            __('ldap.errors.not_provisioned'),
            'not_provisioned'
        );
    }

    /**
     * Optionally map LDAP groups to application roles.
     *
     * @param  array<int, string>  $groups
     */
    protected function applyGroupRoleMapping(User $user, array $groups): void
    {
        $mapping = config('ldap.group_role_mapping', []);

        if ($mapping === [] || $groups === []) {
            return;
        }

        foreach ($groups as $groupDn) {
            if (isset($mapping[$groupDn]) && $mapping[$groupDn] === 'admin') {
                $user->is_admin = true;

                break;
            }
        }
    }
}
