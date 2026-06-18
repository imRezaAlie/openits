<?php

namespace App\Jobs;

use App\Events\LdapSyncCompleted;
use App\Services\LdapService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncLdapUsersJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(LdapService $ldap): void
    {
        try {
            $count = $ldap->syncAllUsers();

            LdapSyncCompleted::dispatch($count, true);
        } catch (\Throwable $exception) {
            Log::error('LDAP sync job failed', [
                'message' => $exception->getMessage(),
            ]);

            LdapSyncCompleted::dispatch(0, false, $exception->getMessage());
        }
    }
}
