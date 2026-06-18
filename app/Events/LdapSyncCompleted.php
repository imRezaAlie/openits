<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LdapSyncCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $usersProcessed,
        public bool $success = true,
        public ?string $message = null,
    ) {}
}
