<?php

namespace App\Services;

use App\Models\C4ModelVersion;
use App\Models\System;
use Illuminate\Support\Facades\Auth;

class C4VersionService
{
    public function snapshot(System $system, string $commitMessage, string $branch = 'main'): C4ModelVersion
    {
        $system->loadMissing(['c4Context', 'c4Containers.components']);

        $lastVersion = C4ModelVersion::query()
            ->where('system_id', $system->id)
            ->where('branch', $branch)
            ->max('version_number') ?? 0;

        return C4ModelVersion::create([
            'system_id' => $system->id,
            'user_id' => Auth::id(),
            'commit_message' => $commitMessage,
            'branch' => $branch,
            'version_number' => $lastVersion + 1,
            'snapshot' => [
                'context' => $system->c4Context?->toArray(),
                'containers' => $system->c4Containers->map(fn ($c) => [
                    ...$c->toArray(),
                    'components' => $c->components->toArray(),
                ])->values()->all(),
                'exported_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function rollback(System $system, C4ModelVersion $version): void
    {
        $snapshot = $version->snapshot;

        if ($system->c4Context && isset($snapshot['context'])) {
            $system->c4Context->update(collect($snapshot['context'])->only([
                'name', 'description', 'external_systems', 'users', 'position', 'metadata',
            ])->all());
        }

        foreach ($snapshot['containers'] ?? [] as $containerData) {
            $components = $containerData['components'] ?? [];
            unset($containerData['components'], $containerData['created_at'], $containerData['updated_at']);

            $container = $system->c4Containers()->updateOrCreate(
                ['id' => $containerData['id']],
                collect($containerData)->only([
                    'name', 'type', 'technology', 'description', 'position', 'metadata', 'sunset_date',
                ])->all(),
            );

            foreach ($components as $componentData) {
                $container->components()->updateOrCreate(
                    ['id' => $componentData['id']],
                    collect($componentData)->only([
                        'name', 'type', 'technology', 'description', 'dependencies', 'position', 'metadata', 'sunset_date',
                    ])->all(),
                );
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function diff(C4ModelVersion $from, C4ModelVersion $to): array
    {
        $fromSnapshot = json_encode($from->snapshot);
        $toSnapshot = json_encode($to->snapshot);

        return [
            'from_version' => $from->version_number,
            'to_version' => $to->version_number,
            'changed' => $fromSnapshot !== $toSnapshot,
            'from' => $from->snapshot,
            'to' => $to->snapshot,
        ];
    }
}
