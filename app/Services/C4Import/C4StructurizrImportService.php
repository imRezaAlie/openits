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

class C4StructurizrImportService
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
        $import->updateProgress(10);

        $this->syncService->enableC4ForSystem($system);
        $system->refresh();

        $elements = $this->parseDsl($content);
        $import->updateProgress(30);

        $slugToId = [];
        $containers = 0;
        $components = 0;
        $relationships = 0;

        foreach ($elements['persons'] as $person) {
            $context = $system->c4Context;
            if ($context) {
                $users = $context->users ?? [];
                $users[] = [
                    'id' => 'user-'.$person['slug'],
                    'name' => $person['name'],
                    'description' => $person['description'] ?? null,
                ];
                $context->update(['users' => $users]);
            }
        }

        foreach ($elements['external_systems'] as $external) {
            $context = $system->c4Context;
            if ($context) {
                $externals = $context->external_systems ?? [];
                $externals[] = [
                    'id' => 'external-'.$external['slug'],
                    'name' => $external['name'],
                    'description' => $external['description'] ?? null,
                ];
                $context->update(['external_systems' => $externals]);
                $slugToId[$external['slug']] = 'external-'.$external['slug'];
            }
        }

        $import->updateProgress(45);

        foreach ($elements['containers'] as $item) {
            $container = C4Container::updateOrCreate(
                ['system_id' => $system->id, 'name' => $item['name']],
                [
                    'type' => $this->mapContainerType($item['technology'] ?? ''),
                    'technology' => $item['technology'] ?? null,
                    'description' => $item['description'] ?? null,
                    'metadata' => ['structurizr_slug' => $item['slug'], 'imported' => true],
                ],
            );
            $slugToId[$item['slug']] = $container->id;
            $containers++;
        }

        $import->updateProgress(60);

        foreach ($elements['components'] as $item) {
            $parentSlug = $item['parent_slug'] ?? $elements['default_container_slug'] ?? null;
            $parentId = $parentSlug ? ($slugToId[$parentSlug] ?? null) : null;

            if (! $parentId) {
                $parent = C4Container::firstOrCreate(
                    ['system_id' => $system->id, 'name' => 'Imported Components', 'type' => C4ContainerTypes::BACKEND],
                    ['technology' => 'Structurizr', 'metadata' => ['auto_created' => true]],
                );
                $parentId = $parent->id;
                $slugToId['__default__'] = $parentId;
            }

            $component = C4Component::updateOrCreate(
                ['c4_container_id' => $parentId, 'name' => $item['name']],
                [
                    'type' => C4ComponentTypes::SERVICE,
                    'technology' => $item['technology'] ?? null,
                    'description' => $item['description'] ?? null,
                    'metadata' => ['structurizr_slug' => $item['slug']],
                ],
            );
            $slugToId[$item['slug']] = $component->id;
            $components++;
        }

        $import->updateProgress(80);

        $systemNodeId = $system->c4Context?->id ?? 'system-'.$system->id;
        $slugToId['__system__'] = $systemNodeId;

        foreach ($elements['relationships'] as $rel) {
            $sourceId = $slugToId[$rel['source']] ?? null;
            $targetId = $slugToId[$rel['target']] ?? null;
            if (! $sourceId || ! $targetId) {
                continue;
            }

            C4Relationship::firstOrCreate(
                [
                    'source_id' => $sourceId,
                    'target_id' => $targetId,
                    'source_type' => $this->guessElementType($rel['source'], $elements),
                    'target_type' => $this->guessElementType($rel['target'], $elements),
                ],
                [
                    'protocol' => $rel['technology'] ?? 'HTTP',
                    'description' => $rel['description'] ?? null,
                    'sync' => true,
                ],
            );
            $relationships++;
        }

        if ($elements['system_name'] && $system->c4Context) {
            $system->c4Context->update([
                'name' => $elements['system_name'],
                'description' => $elements['system_description'],
            ]);
        }

        $import->updateProgress(95);
        $this->versionService->snapshot($system, 'Imported from Structurizr DSL: '.($import->original_filename));

        return [
            'containers' => $containers,
            'components' => $components,
            'relationships' => $relationships,
            'persons' => count($elements['persons']),
            'external_systems' => count($elements['external_systems']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parseDsl(string $content): array
    {
        $result = [
            'system_name' => null,
            'system_description' => null,
            'default_container_slug' => null,
            'persons' => [],
            'external_systems' => [],
            'containers' => [],
            'components' => [],
            'relationships' => [],
        ];

        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $currentContainerSlug = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '//') || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/^(\w+)\s*=\s*person\s+"([^"]*)"(?:\s+"([^"]*)")?/i', $line, $m)) {
                $result['persons'][] = ['slug' => $m[1], 'name' => $m[2], 'description' => $m[3] ?? null];

                continue;
            }

            if (preg_match('/^(\w+)\s*=\s*softwareSystem\s+"([^"]*)"(?:\s+"([^"]*)")?(?:\s+"([^"]*)")?/i', $line, $m)) {
                $entry = ['slug' => $m[1], 'name' => $m[2], 'description' => $m[3] ?? null];
                if (isset($m[4]) && strtolower($m[4]) === 'external') {
                    $result['external_systems'][] = $entry;
                } else {
                    $result['system_name'] = $m[2];
                    $result['system_description'] = $m[3] ?? null;
                }

                continue;
            }

            if (preg_match('/^(\w+)\s*=\s*container\s+"([^"]*)"(?:\s+"([^"]*)")?(?:\s+"([^"]*)")?(?:\s+"([^"]*)")?/i', $line, $m)) {
                $result['containers'][] = [
                    'slug' => $m[1],
                    'name' => $m[2],
                    'description' => $m[3] ?? null,
                    'technology' => $m[4] ?? ($m[5] ?? null),
                ];
                $currentContainerSlug = $m[1];
                $result['default_container_slug'] ??= $m[1];

                continue;
            }

            if (preg_match('/^(\w+)\s*=\s*component\s+"([^"]*)"(?:\s+"([^"]*)")?(?:\s+"([^"]*)")?/i', $line, $m)) {
                $result['components'][] = [
                    'slug' => $m[1],
                    'name' => $m[2],
                    'description' => $m[3] ?? null,
                    'technology' => $m[4] ?? null,
                    'parent_slug' => $currentContainerSlug,
                ];

                continue;
            }

            if (preg_match('/^(\w+)\s*->\s*(\w+)(?:\s+"([^"]*)")?(?:\s+"([^"]*)")?/i', $line, $m)) {
                $result['relationships'][] = [
                    'source' => $m[1],
                    'target' => $m[2],
                    'description' => $m[3] ?? null,
                    'technology' => $m[4] ?? null,
                ];
            }
        }

        return $result;
    }

    private function mapContainerType(?string $technology): string
    {
        $tech = strtolower($technology ?? '');

        return match (true) {
            str_contains($tech, 'database') || str_contains($tech, 'postgres') || str_contains($tech, 'mysql') => C4ContainerTypes::DATABASE,
            str_contains($tech, 'queue') || str_contains($tech, 'kafka') || str_contains($tech, 'rabbit') => C4ContainerTypes::EVENT_BUS,
            str_contains($tech, 'cache') || str_contains($tech, 'redis') => C4ContainerTypes::CACHE,
            str_contains($tech, 'gateway') => C4ContainerTypes::API_GATEWAY,
            str_contains($tech, 'react') || str_contains($tech, 'vue') || str_contains($tech, 'angular') => C4ContainerTypes::FRONTEND,
            default => C4ContainerTypes::BACKEND,
        };
    }

    /**
     * @param  array<string, mixed>  $elements
     */
    private function guessElementType(string $slug, array $elements): string
    {
        foreach ($elements['containers'] as $c) {
            if ($c['slug'] === $slug) {
                return C4ElementTypes::CONTAINER;
            }
        }
        foreach ($elements['components'] as $c) {
            if ($c['slug'] === $slug) {
                return C4ElementTypes::COMPONENT;
            }
        }
        foreach ($elements['persons'] as $p) {
            if ($p['slug'] === $slug) {
                return C4ElementTypes::USER;
            }
        }
        foreach ($elements['external_systems'] as $e) {
            if ($e['slug'] === $slug) {
                return C4ElementTypes::EXTERNAL_SYSTEM;
            }
        }

        return C4ElementTypes::CONTAINER;
    }
}
