<?php

namespace App\Services;

use App\Models\C4Component;
use App\Models\C4Container;
use App\Models\C4Context;
use App\Models\C4Relationship;
use App\Models\System;
use App\Support\C4ComponentTypes;
use App\Support\C4ContainerTypes;
use App\Support\C4ElementTypes;
use App\Support\C4Protocols;
use Illuminate\Support\Str;

class C4SyncService
{
    public function __construct(
        private C4VersionService $versionService,
    ) {}

    public function enableC4ForSystem(System $system): C4Context
    {
        if ($system->c4Context) {
            $system->update(['c4_enabled' => true]);

            return $system->c4Context;
        }

        $context = C4Context::create([
            'name' => $system->name.' Context',
            'description' => $system->description,
            'external_systems' => [],
            'users' => [
                [
                    'id' => (string) Str::uuid(),
                    'name' => 'End User',
                    'role' => 'User',
                    'description' => 'Uses the system',
                ],
            ],
            'metadata' => ['auto_generated' => true],
        ]);

        $system->update([
            'c4_enabled' => true,
            'c4_context_id' => $context->id,
        ]);

        return $context;
    }

    public function syncFromApis(System $system): void
    {
        $system->loadMissing(['ownedApis.defaultVersion.restDetail', 'c4Containers.components']);

        if (! $system->c4_enabled) {
            $this->enableC4ForSystem($system);
            $system->refresh();
        }

        $gateway = $this->findOrCreateContainer($system, C4ContainerTypes::API_GATEWAY, 'API Gateway', 'nginx / Kong');
        $backend = $this->findOrCreateContainer($system, C4ContainerTypes::BACKEND, 'Backend API', 'Laravel');

        $this->ensureRelationship($gateway->id, $backend->id, C4ElementTypes::CONTAINER, C4ElementTypes::CONTAINER, C4Protocols::REST);

        foreach ($system->ownedApis as $api) {
            $controllerName = Str::studly($api->name).'Controller';
            $component = C4Component::updateOrCreate(
                [
                    'c4_container_id' => $backend->id,
                    'name' => $controllerName,
                ],
                [
                    'type' => C4ComponentTypes::CONTROLLER,
                    'technology' => $api->type,
                    'description' => $api->description,
                    'metadata' => ['api_id' => $api->id, 'synced_at' => now()->toIso8601String()],
                ],
            );

            $protocol = C4Protocols::fromApiType($api->type);
            $this->ensureRelationship($gateway->id, $component->id, C4ElementTypes::CONTAINER, C4ElementTypes::COMPONENT, $protocol);
        }

        $this->syncTechnologies($system);
        $this->versionService->snapshot($system, 'Auto-sync from API documentation');
    }

    public function syncTechnologies(System $system): void
    {
        $system->loadMissing(['technologies', 'c4Containers']);

        foreach ($system->technologies as $technology) {
            $containerType = $this->mapTechnologyToContainer($technology->category);
            if (! $containerType) {
                continue;
            }

            $container = $system->c4Containers->firstWhere('type', $containerType)
                ?? C4Container::create([
                    'system_id' => $system->id,
                    'name' => $technology->name,
                    'type' => $containerType,
                    'technology' => $technology->name,
                    'metadata' => ['synced_from_technology' => $technology->id],
                ]);

            if ($container->technology !== $technology->name) {
                $container->update(['technology' => $technology->name]);
            }
        }
    }

    private function findOrCreateContainer(System $system, string $type, string $name, ?string $technology = null): C4Container
    {
        return C4Container::firstOrCreate(
            ['system_id' => $system->id, 'type' => $type, 'name' => $name],
            ['technology' => $technology, 'metadata' => ['auto_generated' => true]],
        );
    }

    private function ensureRelationship(
        string $sourceId,
        string $targetId,
        string $sourceType,
        string $targetType,
        string $protocol,
    ): void {
        C4Relationship::firstOrCreate(
            [
                'source_id' => $sourceId,
                'target_id' => $targetId,
                'source_type' => $sourceType,
                'target_type' => $targetType,
            ],
            [
                'protocol' => $protocol,
                'sync' => true,
                'description' => 'Auto-synced relationship',
            ],
        );
    }

    private function mapTechnologyToContainer(?string $category): ?string
    {
        return match ($category) {
            'database' => C4ContainerTypes::DATABASE,
            'cache' => C4ContainerTypes::CACHE,
            'messaging' => C4ContainerTypes::EVENT_BUS,
            'frontend' => C4ContainerTypes::FRONTEND,
            default => null,
        };
    }
}
