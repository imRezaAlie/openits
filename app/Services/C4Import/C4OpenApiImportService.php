<?php

namespace App\Services\C4Import;

use App\Models\C4Component;
use App\Models\C4Container;
use App\Models\C4Import;
use App\Models\C4Relationship;
use App\Models\System;
use App\Services\C4SyncService;
use App\Services\C4VersionService;
use App\Support\C4ComponentTypes;
use App\Support\C4ContainerTypes;
use App\Support\C4ElementTypes;
use App\Support\C4Protocols;
use Illuminate\Support\Str;

class C4OpenApiImportService
{
    use ParsesSpecFiles;

    public function __construct(
        private C4SyncService $syncService,
        private C4VersionService $versionService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function import(System $system, string $content, C4Import $import): array
    {
        $spec = $this->parseSpecContent($content);
        $import->updateProgress(15);

        $this->syncService->enableC4ForSystem($system);
        $system->refresh();

        $title = $spec['info']['title'] ?? $system->name;
        $import->updateProgress(25);

        $gateway = $this->upsertContainer($system, C4ContainerTypes::API_GATEWAY, 'API Gateway', 'Kong / nginx');
        $backend = $this->upsertContainer($system, C4ContainerTypes::BACKEND, $title.' API', $this->detectTechnology($spec));
        $import->updateProgress(35);

        $this->ensureRelationship($gateway->id, $backend->id, C4ElementTypes::CONTAINER, C4ElementTypes::CONTAINER, C4Protocols::REST);

        $paths = $spec['paths'] ?? [];
        $total = max(1, $this->countOperations($paths));
        $created = 0;
        $components = 0;

        foreach ($paths as $path => $methods) {
            foreach ($methods as $method => $operation) {
                if (! is_array($operation) || ! in_array(strtolower($method), ['get', 'post', 'put', 'patch', 'delete', 'head', 'options'], true)) {
                    continue;
                }

                $name = $operation['operationId']
                    ?? $operation['summary']
                    ?? Str::studly(trim($path, '/')).ucfirst($method);

                $componentName = Str::endsWith($name, 'Controller') ? $name : Str::studly($name).'Controller';

                C4Component::updateOrCreate(
                    ['c4_container_id' => $backend->id, 'name' => $componentName],
                    [
                        'type' => C4ComponentTypes::CONTROLLER,
                        'technology' => strtoupper($method).' '.$path,
                        'description' => $operation['description'] ?? $operation['summary'] ?? null,
                        'metadata' => [
                            'openapi_path' => $path,
                            'http_method' => strtoupper($method),
                            'imported_at' => now()->toIso8601String(),
                        ],
                    ],
                );

                $components++;
                $created++;
                $import->updateProgress(35 + (int) (($created / $total) * 55));
            }
        }

        $import->updateProgress(95);
        $this->versionService->snapshot($system, 'Imported from OpenAPI: '.($import->original_filename));

        return [
            'containers' => 2,
            'components' => $components,
            'operations' => $created,
            'spec_title' => $title,
            'spec_version' => $spec['info']['version'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $paths
     */
    private function countOperations(array $paths): int
    {
        $count = 0;
        foreach ($paths as $methods) {
            foreach ($methods as $method => $operation) {
                if (is_array($operation) && in_array(strtolower($method), ['get', 'post', 'put', 'patch', 'delete', 'head', 'options'], true)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    private function detectTechnology(array $spec): string
    {
        $servers = $spec['servers'] ?? [];

        return $servers[0]['description'] ?? 'REST API';
    }

    private function upsertContainer(System $system, string $type, string $name, ?string $technology): C4Container
    {
        return C4Container::updateOrCreate(
            ['system_id' => $system->id, 'type' => $type, 'name' => $name],
            ['technology' => $technology, 'metadata' => ['imported' => true]],
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
                'description' => 'Imported from OpenAPI',
            ],
        );
    }
}
