<?php

namespace App\Listeners;

use App\Events\ApiDocumentationUpdated;
use App\Services\C4SyncService;

class SyncC4FromApiDocumentation
{
    public function __construct(
        private C4SyncService $syncService,
    ) {}

    public function handle(ApiDocumentationUpdated $event): void
    {
        $system = $event->api->ownerSystem;

        if (! $system || ! $system->c4_enabled) {
            return;
        }

        $this->syncService->syncFromApis($system);
    }
}
