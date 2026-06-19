<?php

namespace App\Console\Commands;

use App\Jobs\SyncLdapUsersJob;
use App\Services\LdapService;
use App\Services\SettingsService;
use Illuminate\Console\Command;

class LdapSyncCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ldap:sync
                            {--queue : Queue the sync job instead of running synchronously}';

    /**
     * @var string
     */
    protected $description = 'Synchronize all LDAP users into the local database';

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

        if ($this->option('queue')) {
            SyncLdapUsersJob::dispatch();
            $this->info(__('ldap.messages.sync_started'));

            return self::SUCCESS;
        }

        try {
            $count = $this->ldap->syncAllUsers();
            $this->info(__('ldap.messages.sync_completed', ['count' => $count]));

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
