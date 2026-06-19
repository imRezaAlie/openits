<?php

namespace App\Console\Commands;

use App\Services\SettingsService;
use Illuminate\Console\Command;

class LdapEnableCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ldap:enable';

    /**
     * @var string
     */
    protected $description = 'Enable LDAP login';

    public function __construct(
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

        $this->settings->setLdapLoginEnabled(true);
        $this->info(__('ldap.messages.enabled'));

        return self::SUCCESS;
    }
}
