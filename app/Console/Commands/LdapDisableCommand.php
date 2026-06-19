<?php

namespace App\Console\Commands;

use App\Services\SettingsService;
use Illuminate\Console\Command;

class LdapDisableCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ldap:disable';

    /**
     * @var string
     */
    protected $description = 'Disable LDAP login';

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
        $this->settings->setLdapLoginEnabled(false);
        $this->info(__('ldap.messages.disabled'));

        return self::SUCCESS;
    }
}
