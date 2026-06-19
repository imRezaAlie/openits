<?php

namespace App\Console\Commands;

use App\Services\SettingsService;
use Illuminate\Console\Command;

class LdapStatusCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ldap:status';

    /**
     * @var string
     */
    protected $description = 'Show the LDAP login enabled status and configuration';

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
        $this->table(
            ['Setting', 'Value'],
            [
                ['LDAP login enabled', $this->settings->isLdapLoginEnabled() ? 'Yes' : 'No'],
                ['Credentials configured', $this->settings->ldapCredentialsConfigured() ? 'Yes' : 'No'],
                ['Server', $this->settings->getLdapServer() ?? '—'],
                ['Port', (string) $this->settings->getLdapPort()],
                ['Base DN', $this->settings->getLdapBaseDn() ?? '—'],
                ['Domain', $this->settings->getLdapDomain() ?? '—'],
                ['Use SSL', config('ldap.use_ssl') ? 'Yes' : 'No'],
                ['Use STARTTLS', config('ldap.use_starttls') ? 'Yes' : 'No'],
            ]
        );

        return self::SUCCESS;
    }
}
