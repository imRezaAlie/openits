<?php

namespace App\Services\C4Import;

use App\Models\C4Component;
use App\Models\C4Container;
use App\Models\C4Context;
use App\Models\C4Import;
use App\Models\System;
use App\Services\C4SyncService;
use App\Services\C4VersionService;

class C4JsonBackupImportService
{
    public function __construct(
        private C4SyncService $syncService,
        private C4VersionService $versionService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function import(System $system, string $content, C4Import $import): array
    {
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON backup: '.json_last_error_msg());
        }

        $import->updateProgress(15);
        $this->syncService->enableC4ForSystem($system);
        $system->refresh();

        if (isset($data['context']) && is_array($data['context'])) {
            $ctx = $data['context'];
            if ($system->c4Context) {
                $system->c4Context->update(collect($ctx)->only([
                    'name', 'description', 'external_systems', 'users', 'position', 'metadata',
                ])->filter()->all());
            } else {
                $context = C4Context::create(collect($ctx)->only([
                    'name', 'description', 'external_systems', 'users', 'position', 'metadata',
                ])->all());
                $system->update(['c4_context_id' => $context->id]);
            }
        }

        $import->updateProgress(35);

        $containerCount = 0;
        $componentCount = 0;

        foreach ($data['containers'] ?? [] as $containerData) {
            $components = $containerData['components'] ?? [];
            unset($containerData['components']);

            $container = $system->c4Containers()->updateOrCreate(
                ['id' => $containerData['id'] ?? null],
                collect($containerData)->only([
                    'name', 'type', 'technology', 'description', 'position', 'metadata', 'sunset_date',
                ])->filter(fn ($v) => $v !== null)->all(),
            );
            $containerCount++;

            foreach ($components as $componentData) {
                $container->components()->updateOrCreate(
                    ['id' => $componentData['id'] ?? null],
                    collect($componentData)->only([
                        'name', 'type', 'technology', 'description', 'dependencies', 'position', 'metadata', 'sunset_date',
                    ])->filter(fn ($v) => $v !== null)->all(),
                );
                $componentCount++;
            }

            $import->updateProgress(min(90, 35 + $containerCount * 5));
        }

        $import->updateProgress(95);
        $this->versionService->snapshot($system, 'Restored from JSON backup: '.($import->original_filename));

        return [
            'containers' => $containerCount,
            'components' => $componentCount,
        ];
    }
}
