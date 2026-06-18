<?php

namespace App\Services;

use App\Models\C4Component;
use App\Models\C4Container;
use App\Models\C4Context;
use App\Models\C4Relationship;
use App\Models\System;
use App\Support\C4ElementTypes;
use App\Support\C4Protocols;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class C4DiagramService
{
    public function __construct(
        private C4ContextElementService $contextElements,
    ) {}

    public function buildContextDiagram(System $system): array
    {
        $system->loadMissing(['c4Context', 'domain', 'vendor', 'c4Containers']);

        $context = $system->c4Context;
        if ($context) {
            $context = $this->contextElements->ensureElementUuids($context);
        }

        $nodes = [];
        $edges = [];

        $systemNodeId = $context?->id ?? 'system-'.$system->id;

        $nodes[] = $this->node(
            $systemNodeId,
            $system->name,
            C4ElementTypes::CONTEXT,
            [
                'description' => $system->description,
                'system_id' => $system->id,
                'drill_down' => route('c4.systems.containers', $system),
                'level' => 'context',
            ],
            $context?->position ?? null,
        );

        foreach ($context?->external_systems ?? [] as $index => $external) {
            $id = $external['id'];
            $nodes[] = $this->node(
                $id,
                $external['name'] ?? 'External System',
                C4ElementTypes::EXTERNAL_SYSTEM,
                [
                    'description' => $external['description'] ?? null,
                    'linked_system_id' => $external['linked_system_id'] ?? null,
                    'level' => 'context',
                ],
                $external['position'] ?? null,
            );
        }

        foreach ($context?->users ?? [] as $index => $user) {
            $id = $user['id'];
            $nodes[] = $this->node(
                $id,
                $user['name'] ?? 'User',
                C4ElementTypes::USER,
                [
                    'description' => $user['description'] ?? null,
                    'role' => $user['role'] ?? null,
                    'level' => 'context',
                ],
                $user['position'] ?? null,
            );
        }

        $relationships = $this->relationshipsForLevel($system, 'context', $context);

        foreach ($relationships as $rel) {
            $edges[] = $this->edge($rel);
        }

        return [
            'level' => 'context',
            'system' => $this->systemMeta($system),
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }

    public function buildContainerDiagram(System $system): array
    {
        $system->loadMissing(['c4Containers.components', 'c4Context', 'technologies', 'servers']);

        $nodes = [];
        $edges = [];

        $boundaryId = 'boundary-'.$system->id;
        $nodes[] = $this->node(
            $boundaryId,
            $system->name,
            C4ElementTypes::SYSTEM,
            [
                'description' => $system->description,
                'is_boundary' => true,
                'level' => 'container',
                'drill_up' => route('c4.systems.context', $system),
            ],
            null,
            true,
        );

        foreach ($system->c4Containers as $container) {
            $nodes[] = $this->node(
                $container->id,
                $container->name,
                C4ElementTypes::CONTAINER,
                [
                    'description' => $container->description,
                    'container_type' => $container->type,
                    'technology' => $container->technology,
                    'component_count' => $container->components->count(),
                    'deprecated' => $container->isDeprecated(),
                    'sunset_date' => $container->sunset_date?->toDateString(),
                    'drill_down' => route('c4.containers.show', $container),
                    'level' => 'container',
                ],
                $container->position,
            );
        }

        $relationships = $this->relationshipsForLevel($system, 'container');

        foreach ($relationships as $rel) {
            $edges[] = $this->edge($rel);
        }

        return [
            'level' => 'container',
            'system' => $this->systemMeta($system),
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }

    public function buildComponentDiagram(C4Container $container): array
    {
        $container->loadMissing(['components', 'system.c4Context']);

        $system = $container->system;
        $nodes = [];
        $edges = [];

        foreach ($container->components as $component) {
            $nodes[] = $this->node(
                $component->id,
                $component->name,
                C4ElementTypes::COMPONENT,
                [
                    'description' => $component->description,
                    'component_type' => $component->type,
                    'technology' => $component->technology,
                    'dependencies' => $component->dependencies ?? [],
                    'deprecated' => $component->isDeprecated(),
                    'sunset_date' => $component->sunset_date?->toDateString(),
                    'level' => 'component',
                ],
                $component->position,
            );
        }

        $componentIds = $container->components->pluck('id');

        $relationships = C4Relationship::query()
            ->where(function ($q) use ($componentIds) {
                $q->whereIn('source_id', $componentIds)
                    ->orWhereIn('target_id', $componentIds);
            })
            ->get();

        foreach ($relationships as $rel) {
            $edges[] = $this->edge($rel);
        }

        foreach ($container->components as $component) {
            foreach ($component->dependencies ?? [] as $depId) {
                if ($componentIds->contains($depId)) {
                    $edges[] = [
                        'id' => 'dep-'.$component->id.'-'.$depId,
                        'source' => $depId,
                        'target' => $component->id,
                        'label' => 'depends',
                        'protocol' => null,
                        'sync' => true,
                        'description' => null,
                    ];
                }
            }
        }

        return [
            'level' => 'component',
            'system' => $this->systemMeta($system),
            'container' => [
                'id' => $container->id,
                'name' => $container->name,
                'type' => $container->type,
                'technology' => $container->technology,
                'drill_up' => route('c4.systems.containers', $system),
            ],
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }

    public function search(System $system, string $query): Collection
    {
        $query = Str::lower(trim($query));

        if ($query === '') {
            return collect();
        }

        $results = collect();

        if ($system->c4Context && Str::contains(Str::lower($system->c4Context->name), $query)) {
            $results->push(['type' => 'context', 'id' => $system->c4Context->id, 'name' => $system->c4Context->name]);
        }

        foreach ($system->c4Containers as $container) {
            if (Str::contains(Str::lower($container->name), $query)) {
                $results->push(['type' => 'container', 'id' => $container->id, 'name' => $container->name]);
            }
            foreach ($container->components as $component) {
                if (Str::contains(Str::lower($component->name), $query)) {
                    $results->push(['type' => 'component', 'id' => $component->id, 'name' => $component->name, 'container_id' => $container->id]);
                }
            }
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>|null  $position
     * @return array<string, mixed>
     */
    private function node(string $id, string $name, string $type, array $meta = [], ?array $position = null, bool $isBoundary = false): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'color' => C4ElementTypes::levelColor($type),
            'position' => $position ?? ['x' => 0, 'y' => 0],
            'is_boundary' => $isBoundary,
            ...$meta,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function edge(C4Relationship $rel): array
    {
        return [
            'id' => $rel->id,
            'source' => $rel->source_id,
            'target' => $rel->target_id,
            'label' => $rel->protocol,
            'protocol' => $rel->protocol,
            'sync' => $rel->sync,
            'description' => $rel->description,
        ];
    }

    /**
     * @return Collection<int, C4Relationship>
     */
    private function relationshipsForLevel(System $system, string $level, ?C4Context $context = null): Collection
    {
        if ($level === 'context' && $context) {
            $ids = $this->contextElements->contextRelationshipIds($system, $context);

            return C4Relationship::query()
                ->where(function ($q) use ($ids) {
                    $q->whereIn('source_id', $ids)->orWhereIn('target_id', $ids);
                })
                ->get();
        }

        $ids = collect();

        if ($level === 'container') {
            $ids = $system->c4Containers->pluck('id');
        }

        if ($ids->isEmpty()) {
            return collect();
        }

        return C4Relationship::query()
            ->where(function ($q) use ($ids) {
                $q->whereIn('source_id', $ids)->orWhereIn('target_id', $ids);
            })
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function systemMeta(System $system): array
    {
        return [
            'id' => $system->id,
            'name' => $system->name,
            'description' => $system->description,
            'c4_enabled' => $system->c4_enabled,
            'domain' => $system->domain?->name,
            'vendor' => $system->vendor?->name,
        ];
    }
}
