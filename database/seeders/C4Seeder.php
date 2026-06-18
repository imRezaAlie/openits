<?php

namespace Database\Seeders;

use App\Models\System;
use App\Services\C4SyncService;
use Illuminate\Database\Seeder;

class C4Seeder extends Seeder
{
    public function run(): void
    {
        $syncService = app(C4SyncService::class);

        System::query()
            ->with('ownedApis')
            ->whereHas('ownedApis')
            ->limit(3)
            ->get()
            ->each(function (System $system) use ($syncService) {
                $syncService->enableC4ForSystem($system);
                $syncService->syncFromApis($system);
            });
    }
}
