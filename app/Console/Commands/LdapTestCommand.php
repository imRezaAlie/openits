<?php

namespace App\Console\Commands;

use App\Services\LdapService;
use App\Services\SettingsService;
use Illuminate\Console\Command;

class LdapTestCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ldap:test';

    /**
     * @var string
     */
    protected $description = 'Test the LDAP server connection';

    public function __construct(
        protected LdapService $ldap,
        protected SettingsService $settings
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->settings->ldapCredentialsConfigured()) {
            $this->error(__('ldap.errors.credentials_missing'));

            return self::FAILURE;
        }

        $result = $this->ldap->testConnection();

        if ($result['success']) {
            $this->info($result['message']);

            return self::SUCCESS;
        }

        $this->error($result['message']);

        return self::FAILURE;
    }
}
