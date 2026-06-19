<?php

namespace App\Listeners;

use App\Events\LdapSyncCompleted;
use App\Models\LdapLog;
use App\Services\LdapService;
use Illuminate\Support\Facades\Log;

class LogLdapSyncCompleted
{
    public function __construct(
        protected LdapService $ldap
    ) {}

    /**
     * Handle LDAP sync completion.
     */
    public function handle(LdapSyncCompleted $event): void
    {
        $message = $event->message ?? __('ldap.messages.sync_completed', [
            'count' => $event->usersProcessed,
        ]);

        $this->ldap->logAttempt(
            LdapLog::ACTION_SYNC,
            $event->success ? LdapLog::STATUS_SUCCESS : LdapLog::STATUS_FAILURE,
            null,
            null,
            $message,
            null,
            ['users_processed' => $event->usersProcessed]
        );

        Log::info('LDAP sync completed', [
            'users_processed' => $event->usersProcessed,
            'success' => $event->success,
        ]);
    }
}
