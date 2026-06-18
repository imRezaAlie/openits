<?php

namespace App\Support;

use App\Models\ArchitecturalDecisionRecord;
use App\Models\Api;
use App\Models\Bpmn;
use App\Models\CanonicalEntity;
use App\Models\Domain;
use App\Models\PlatformSchema;
use App\Models\Project;
use App\Models\System;
use App\Models\SystemDocument;
use App\Models\Technology;
use App\Models\Vendor;

class Breadcrumbs
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array{label: string, url?: string}>|null
     */
    public static function resolve(?string $routeName, array $data = []): ?array
    {
        if (! $routeName) {
            return null;
        }

        return match ($routeName) {
            'home' => null,

            'project.index' => [...self::home(), self::page('Projects')],
            'project.show' => self::projectShow($data),
            'project.edit' => self::projectEdit($data),

            'supplier.index' => [...self::home(), self::page('Vendors')],
            'supplier.show' => self::vendorShow($data),
            'supplier.edit' => self::vendorEdit($data),

            'systems.index' => [...self::home(), self::page('Systems')],

            'systems.processes' => self::systemPage($data, 'Processes'),
            'systems.documents' => self::systemPage($data, 'Documents'),
            'systems.servers' => self::systemPage($data, 'Servers'),
            'systems.technologies' => self::systemPage($data, 'Tech Stack'),
            'systems.documents.create-markdown' => self::systemDocumentsSection($data, 'Write Document'),
            'systems.documents.edit-markdown' => self::systemDocumentsSection($data, 'Edit Document'),
            'systems.documents.view' => self::systemDocumentsSection($data, 'View Document'),
            'systems.documents.preview' => self::systemDocumentsSection($data, $data['typeLabel'] ?? 'Preview'),

            'systems.create.bpmn', 'systems.create.sequence' => self::systemPage($data, 'Create Process'),
            'systems.bpmn.show', 'systems.sequence.show' => self::processShow($data),

            'domains.index' => [...self::home(), self::page('Domains')],
            'domains.show' => self::domainShow($data),

            'technologies.index' => [...self::home(), self::page('Technologies')],
            'technologies.show' => self::technologyShow($data),

            'apis.index' => [...self::home(), self::page('API Docs')],
            'apis.create' => [...self::home(), self::link('API Docs', route('apis.index')), self::page('Create')],
            'apis.show' => self::apiShow($data),
            'apis.edit' => self::apiEdit($data),

            'user.index' => [...self::home(), self::page('Users')],

            'processes.index' => [...self::home(), self::page('Processes')],
            'documents.index' => [...self::home(), self::page('Documents')],
            'infrastructure.index' => [...self::home(), self::page('Infrastructure')],

            'integrations.tree' => self::integrationTree($data),
            'integrations.catalog' => [...self::home(), self::page('Integration Catalog')],
            'integrations.system' => self::integrationSystem($data),

            'c4.index' => [...self::home(), self::page('C4 Architecture')],
            'c4.systems.context' => self::c4SystemLevel($data, 'Context'),
            'c4.systems.containers' => self::c4SystemLevel($data, 'Containers'),
            'c4.containers.show' => self::c4ContainerShow($data),

            'c4.adrs.index' => [...self::home(), self::link('C4 Architecture', route('c4.index')), self::page('ADRs')],
            'c4.adrs.show' => self::adrShow($data),
            'c4.adrs.create' => [...self::home(), self::link('ADRs', route('c4.adrs.index')), self::page('Create')],
            'c4.adrs.edit' => self::adrEdit($data),
            'c4.adrs.timeline' => [...self::home(), self::link('ADRs', route('c4.adrs.index')), self::page('Timeline')],
            'c4.tech-radar.index' => [...self::home(), self::page('Tech Radar')],

            'data-stack.index' => [...self::home(), self::page('Data Stack')],
            'data-dictionary.entities.index' => [...self::home(), self::page('Data Dictionary')],
            'data-dictionary.entities.show' => self::canonicalEntityShow($data),
            'platform-schemas.index' => [...self::home(), self::page('Platform Schemas')],
            'platform-schemas.show' => self::platformSchemaShow($data),
            'field-mappings.index' => [...self::home(), self::page('Field Mappings')],

            default => null,
        };
    }

    /** @return array<int, array{label: string, url?: string}> */
    private static function home(): array
    {
        return [self::link('Dashboard', route('home'))];
    }

    /** @return array{label: string, url: string} */
    private static function link(string $label, string $url): array
    {
        return ['label' => $label, 'url' => $url];
    }

    /** @return array{label: string} */
    private static function page(string $label): array
    {
        return ['label' => $label];
    }

    /** @param  array<string, mixed>  $data */
    private static function projectShow(array $data): ?array
    {
        $project = self::model($data, Project::class, 'project');

        if (! $project) {
            return null;
        }

        return [
            ...self::home(),
            self::link('Projects', route('project.index')),
            self::page($project->name),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function projectEdit(array $data): ?array
    {
        $project = self::model($data, Project::class, 'project');

        if (! $project) {
            return null;
        }

        return [
            ...self::home(),
            self::link('Projects', route('project.index')),
            self::link($project->name, route('project.show', $project)),
            self::page('Edit'),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function vendorShow(array $data): ?array
    {
        $vendor = self::model($data, Vendor::class, 'vendor');

        if (! $vendor) {
            return null;
        }

        return [
            ...self::home(),
            self::link('Vendors', route('supplier.index')),
            self::page($vendor->name),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function vendorEdit(array $data): ?array
    {
        $vendor = self::model($data, Vendor::class, 'vendor');

        if (! $vendor) {
            return null;
        }

        return [
            ...self::home(),
            self::link('Vendors', route('supplier.index')),
            self::link($vendor->name, route('supplier.show', $vendor)),
            self::page('Edit'),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function systemPage(array $data, string $suffix): ?array
    {
        $system = self::model($data, System::class, 'system');

        if (! $system) {
            return null;
        }

        return [
            ...self::home(),
            ...self::systemContext($system),
            self::page($system->name.' — '.$suffix),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function systemDocumentsSection(array $data, string $suffix): ?array
    {
        $system = self::model($data, System::class, 'system');

        if (! $system) {
            return null;
        }

        return [
            ...self::home(),
            ...self::systemContext($system),
            self::link($system->name.' — Documents', route('systems.documents', $system)),
            self::page($suffix),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function processShow(array $data): ?array
    {
        $bpmn = self::model($data, Bpmn::class, 'bpmn');

        if (! $bpmn?->system) {
            return null;
        }

        return [
            ...self::home(),
            ...self::systemContext($bpmn->system),
            self::link($bpmn->system->name.' — Processes', route('systems.processes', $bpmn->system)),
            self::page($bpmn->name),
        ];
    }

    /** @return array<int, array{label: string, url?: string}> */
    private static function systemContext(System $system): array
    {
        $system->loadMissing('vendor');

        $items = [self::link('Systems', route('systems.index'))];

        if ($system->vendor) {
            $items[] = self::link(
                $system->vendor->name,
                route('systems.index', ['vendor_id' => $system->vendor_id])
            );
        }

        return $items;
    }

    /** @param  array<string, mixed>  $data */
    private static function domainShow(array $data): ?array
    {
        $domain = self::model($data, Domain::class, 'domain');

        if (! $domain) {
            return null;
        }

        return [
            ...self::home(),
            self::link('Domains', route('domains.index')),
            self::page($domain->name),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function technologyShow(array $data): ?array
    {
        $technology = self::model($data, Technology::class, 'technology');

        if (! $technology) {
            return null;
        }

        return [
            ...self::home(),
            self::link('Technologies', route('technologies.index')),
            self::page($technology->name),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function apiShow(array $data): ?array
    {
        $api = self::model($data, Api::class, 'api');

        if (! $api) {
            return null;
        }

        return [
            ...self::home(),
            self::link('API Docs', route('apis.index')),
            self::page($api->name),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function apiEdit(array $data): ?array
    {
        $api = self::model($data, Api::class, 'api');

        if (! $api) {
            return null;
        }

        return [
            ...self::home(),
            self::link('API Docs', route('apis.index')),
            self::link($api->name, route('apis.show', $api)),
            self::page('Edit'),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function integrationTree(array $data): array
    {
        $selectedSystem = $data['selectedSystem'] ?? null;
        $selectedVendor = $data['selectedVendor'] ?? null;

        if ($selectedSystem instanceof System) {
            $selectedSystem->loadMissing('vendor', 'parent');

            $items = [
                ...self::home(),
                self::link('Integration Tree', route('integrations.tree')),
            ];

            if ($selectedSystem->vendor) {
                $items[] = self::link(
                    $selectedSystem->vendor->name,
                    route('integrations.tree', ['vendor_id' => $selectedSystem->vendor_id])
                );
            }

            if ($selectedSystem->parent) {
                $items[] = self::link(
                    $selectedSystem->parent->name,
                    route('integrations.system', $selectedSystem->parent)
                );
            }

            $items[] = self::page($selectedSystem->name);

            return $items;
        }

        if ($selectedVendor instanceof Vendor) {
            return [
                ...self::home(),
                self::link('Integration Tree', route('integrations.tree')),
                self::page($selectedVendor->name),
            ];
        }

        return [...self::home(), self::page('Integration Tree')];
    }

    /** @param  array<string, mixed>  $data */
    private static function integrationSystem(array $data): ?array
    {
        $system = self::model($data, System::class, 'system')
            ?? self::model($data, System::class, 'selectedSystem');

        if (! $system) {
            return null;
        }

        $system->loadMissing('vendor');

        $items = [
            ...self::home(),
            self::link('Integration Tree', route('integrations.tree')),
        ];

        if ($system->vendor) {
            $items[] = self::link(
                $system->vendor->name,
                route('integrations.tree', ['vendor_id' => $system->vendor_id])
            );
        }

        $items[] = self::page($system->name);

        return $items;
    }

    /** @param  array<string, mixed>  $data */
    private static function canonicalEntityShow(array $data): ?array
    {
        $entity = self::model($data, CanonicalEntity::class, 'canonicalEntity');

        if (! $entity) {
            return null;
        }

        return [
            ...self::home(),
            self::link('Data Dictionary', route('data-dictionary.entities.index')),
            self::page($entity->name),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function platformSchemaShow(array $data): ?array
    {
        $schema = self::model($data, PlatformSchema::class, 'platformSchema');

        if (! $schema) {
            return null;
        }

        return [
            ...self::home(),
            self::link('Platform Schemas', route('platform-schemas.index')),
            self::page($schema->name),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function c4SystemLevel(array $data, string $levelLabel): ?array
    {
        $system = self::model($data, System::class, 'system');

        if (! $system) {
            return null;
        }

        return [
            ...self::home(),
            self::link('C4 Architecture', route('c4.index')),
            self::link($system->name, route('c4.systems.context', $system)),
            self::page($levelLabel),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function c4ContainerShow(array $data): ?array
    {
        $system = self::model($data, System::class, 'system');
        $container = $data['container'] ?? null;

        if (! $system || ! $container) {
            return null;
        }

        return [
            ...self::home(),
            self::link('C4 Architecture', route('c4.index')),
            self::link($system->name, route('c4.systems.context', $system)),
            self::link('Containers', route('c4.systems.containers', $system)),
            self::page($container->name),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function adrShow(array $data): ?array
    {
        $adr = self::model($data, ArchitecturalDecisionRecord::class, 'adr');

        if (! $adr) {
            return null;
        }

        return [
            ...self::home(),
            self::link('ADRs', route('c4.adrs.index')),
            self::page($adr->title),
        ];
    }

    /** @param  array<string, mixed>  $data */
    private static function adrEdit(array $data): ?array
    {
        $adr = self::model($data, ArchitecturalDecisionRecord::class, 'adr');

        if (! $adr) {
            return null;
        }

        return [
            ...self::home(),
            self::link('ADRs', route('c4.adrs.index')),
            self::link($adr->title, route('c4.adrs.show', $adr)),
            self::page('Edit'),
        ];
    }

    /**
     * @template T of object
     * @param  class-string<T>  $class
     * @return T|null
     */
    private static function model(array $data, string $class, string $key): ?object
    {
        $model = $data[$key] ?? null;

        return $model instanceof $class ? $model : null;
    }
}
