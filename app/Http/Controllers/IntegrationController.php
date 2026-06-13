<?php

namespace App\Http\Controllers;

use App\Models\Api;
use App\Models\Domain;
use App\Models\System;
use App\Models\Vendor;
use App\Support\ApiTypes;
use App\Services\IntegrationCatalogService;
use App\Services\TpsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function __construct(
        private TpsService $tpsService,
        private IntegrationCatalogService $catalogService,
    ) {}

    public function catalog(Request $request): View
    {
        $domainId = $request->integer('domain_id') ?: null;
        $vendorId = $request->integer('vendor_id') ?: null;
        $apiType = $request->input('api_type');

        $links = $this->catalogService->query($domainId, $vendorId, $apiType);
        $stats = $this->catalogService->stats($links);

        return view('integrations.catalog', [
            'links' => $links,
            'stats' => $stats,
            'domains' => Domain::orderBy('name')->get(),
            'vendors' => Vendor::orderBy('name')->get(),
            'apiTypes' => ApiTypes::ALL,
            'filters' => [
                'domain_id' => $domainId,
                'vendor_id' => $vendorId,
                'api_type' => $apiType,
            ],
        ]);
    }

    public function exportCatalog(Request $request): Response|JsonResponse
    {
        $links = $this->catalogService->query(
            $request->integer('domain_id') ?: null,
            $request->integer('vendor_id') ?: null,
            $request->input('api_type'),
        );

        if ($request->input('format') === 'csv') {
            $csv = $this->catalogService->toCsv($links);

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="integration-catalog-'.date('Y-m-d').'.csv"',
            ]);
        }

        return response()->json([
            'exported_at' => now()->toIso8601String(),
            'count' => $links->count(),
            'integrations' => $links->values(),
        ]);
    }

    public function exportLandscape(): JsonResponse
    {
        return response()->json($this->catalogService->buildLandscapeExport());
    }

    public function tree(Request $request): View
    {
        $system = $request->filled('system_id')
            ? System::with('vendor', 'parent')->findOrFail($request->integer('system_id'))
            : null;

        $vendor = $request->filled('vendor_id')
            ? Vendor::findOrFail($request->integer('vendor_id'))
            : ($system?->vendor);

        return $this->renderTreeView($system, $vendor);
    }

    public function show(System $system): View
    {
        $system->load(['vendor', 'parent', 'domain']);

        return $this->renderTreeView($system, $system->vendor);
    }

    public function treeData(Request $request): JsonResponse
    {
        $system = $request->filled('system_id')
            ? System::with('vendor')->findOrFail($request->integer('system_id'))
            : null;

        $vendor = $request->filled('vendor_id')
            ? Vendor::findOrFail($request->integer('vendor_id'))
            : ($system?->vendor);

        return response()->json($this->buildTreeData($system, $vendor));
    }

    private function renderTreeView(?System $selectedSystem = null, ?Vendor $selectedVendor = null): View
    {
        $treeData = $this->buildTreeData($selectedSystem, $selectedVendor);
        $systemsOverview = $this->buildSystemsOverview($selectedSystem, $selectedVendor);
        $allSystems = System::with('vendor')->orderBy('name')->get(['id', 'name', 'system_type', 'parent_system_id', 'vendor_id']);
        $allVendors = Vendor::withCount('systems')->orderBy('name')->get();

        return view('integrations.tree', compact(
            'treeData',
            'systemsOverview',
            'allSystems',
            'allVendors',
            'selectedSystem',
            'selectedVendor',
        ));
    }

    private function buildTreeData(?System $system = null, ?Vendor $vendor = null): array
    {
        if ($system) {
            $this->loadSystemTreeRelations($system);
            $node = $this->systemToNode($system);
            $node['type'] = 'root';
            $node['name'] = $system->name;
            if ($system->vendor) {
                $node['vendor_name'] = $system->vendor->name;
            }

            return $node;
        }

        if ($vendor) {
            return $this->vendorToNode($vendor, true);
        }

        $vendors = Vendor::with($this->vendorEagerLoads())
            ->withCount('systems')
            ->orderBy('name')
            ->get();

        $vendorNodes = $vendors->map(fn (Vendor $v) => $this->vendorToNode($v))->all();

        $unassignedSystems = System::with($this->systemEagerLoads())
            ->whereNull('vendor_id')
            ->whereNull('parent_system_id')
            ->orderBy('name')
            ->get();

        if ($unassignedSystems->isNotEmpty()) {
            $vendorNodes[] = [
                'id' => 'vendor-unassigned',
                'name' => 'Unassigned Vendor',
                'type' => 'vendor',
                'system_count' => $unassignedSystems->count(),
                'description' => 'Systems not linked to a vendor',
                'children' => $unassignedSystems->map(fn ($s) => $this->systemToNode($s))->all(),
            ];
        }

        $orphanApis = Api::with(['latestTps', 'defaultVersion', 'systems'])
            ->whereNull('owner_system_id')
            ->whereDoesntHave('systems')
            ->orderBy('name')
            ->get();

        $children = $vendorNodes;
        if ($orphanApis->isNotEmpty()) {
            $children[] = [
                'name' => 'Unassigned APIs',
                'type' => 'system',
                'system_type' => 'unassigned',
                'description' => 'APIs without an owner system',
                'api_count' => $orphanApis->count(),
                'children' => $orphanApis->map(fn ($api) => $this->apiToNode($api))->all(),
            ];
        }

        return [
            'name' => 'Integrations',
            'type' => 'root',
            'children' => $children,
        ];
    }

    private function buildSystemsOverview(?System $system = null, ?Vendor $vendor = null): Collection
    {
        $query = System::with(['parent', 'vendor', 'domain', 'ownedApis.latestTps', 'ownedApis.systems', 'children.ownedApis.latestTps'])
            ->orderBy('name');

        if ($system) {
            $query->whereIn('id', $this->collectSystemAndDescendantIds($system));
        } elseif ($vendor) {
            $query->where('vendor_id', $vendor->id);
        }

        return $query->get()
            ->map(function (System $s) {
                $apis = $s->ownedApis->map(fn (Api $api) => [
                    'id' => $api->id,
                    'name' => $api->name,
                    'type' => ApiTypes::label($api->type),
                    'type_raw' => $api->type,
                    'tps' => $this->tpsService->getCurrentTps($api),
                    'url' => route('apis.show', $api),
                    'integrations' => $api->integratedSystems()->map(fn (System $sys) => [
                        'id' => $sys->id,
                        'name' => $sys->name,
                        'vendor' => $sys->vendor?->name,
                        'url' => route('integrations.system', $sys),
                    ])->values()->all(),
                ]);

                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'system_type' => $s->system_type,
                    'description' => $s->description,
                    'parent_name' => $s->parent?->name,
                    'vendor_name' => $s->vendor?->name,
                    'domain_name' => $s->domain?->name,
                    'domain_color' => $s->domain?->color,
                    'apis' => $apis,
                    'api_count' => $apis->count(),
                    'integrations_url' => route('integrations.system', $s),
                ];
            })
            ->filter(fn ($s) => $s['api_count'] > 0 || ($system && $s['id'] === $system->id));
    }

    /**
     * @return array<int, int>
     */
    private function collectSystemAndDescendantIds(System $system): array
    {
        $this->loadSystemTreeRelations($system);
        $ids = [$system->id];

        foreach ($system->children as $child) {
            $ids = array_merge($ids, $this->collectSystemAndDescendantIds($child));
        }

        return $ids;
    }

    private function loadSystemTreeRelations(System $system): void
    {
        $system->load($this->systemEagerLoads());
    }

    private function vendorEagerLoads(): array
    {
        return [
            'systems' => fn ($q) => $q->whereNull('parent_system_id')->orderBy('name'),
            'systems.domain',
            'systems.children.domain',
            'systems.children.children.domain',
            'systems.children.children.ownedApis.latestTps',
            'systems.children.children.ownedApis.systems.vendor',
            'systems.children.children.ownedApis.defaultVersion',
            'systems.children.ownedApis.latestTps',
            'systems.children.ownedApis.systems.vendor',
            'systems.children.ownedApis.defaultVersion',
            'systems.ownedApis.latestTps',
            'systems.ownedApis.systems.vendor',
            'systems.ownedApis.defaultVersion',
        ];
    }

    private function systemEagerLoads(): array
    {
        return [
            'vendor',
            'domain',
            'children.children.ownedApis.latestTps',
            'children.children.ownedApis.systems.vendor',
            'children.children.ownedApis.defaultVersion',
            'children.ownedApis.latestTps',
            'children.ownedApis.systems.vendor',
            'children.ownedApis.defaultVersion',
            'ownedApis.latestTps',
            'ownedApis.systems.vendor',
            'ownedApis.defaultVersion',
        ];
    }

    private function vendorToNode(Vendor $vendor, bool $asRoot = false): array
    {
        $rootSystems = $vendor->relationLoaded('systems')
            ? $vendor->systems
            : $vendor->systems()->whereNull('parent_system_id')->with($this->systemEagerLoads())->orderBy('name')->get();

        $node = [
            'id' => 'vendor-'.$vendor->id,
            'vendor_id' => $vendor->id,
            'name' => $vendor->name,
            'type' => $asRoot ? 'root' : 'vendor',
            'system_count' => $rootSystems->count(),
            'integrations_url' => route('integrations.tree', ['vendor_id' => $vendor->id]),
            'children' => $rootSystems->map(fn ($s) => $this->systemToNode($s))->all(),
        ];

        if ($asRoot) {
            $node['type'] = 'root';
        }

        return $node;
    }

    private function systemToNode(System $system): array
    {
        $children = [];

        foreach ($system->children as $child) {
            $children[] = $this->systemToNode($child);
        }

        foreach ($system->ownedApis as $api) {
            $children[] = $this->apiToNode($api);
        }

        $apiCount = $system->ownedApis->count();
        $totalApiCount = $apiCount + collect($system->children)->sum(
            fn (System $child) => $this->countApisInSubtree($child)
        );

        return [
            'id' => 'system-'.$system->id,
            'system_id' => $system->id,
            'name' => $system->name,
            'type' => 'system',
            'system_type' => $system->system_type,
            'description' => $system->description,
            'icon' => $system->icon,
            'vendor_name' => $system->vendor?->name,
            'domain_name' => $system->domain?->name,
            'domain_color' => $system->domain?->color,
            'api_count' => $apiCount,
            'total_api_count' => $totalApiCount,
            'integrations_url' => route('integrations.system', $system),
            'children' => $children,
        ];
    }

    private function countApisInSubtree(System $system): int
    {
        $count = $system->ownedApis->count();

        foreach ($system->children as $child) {
            $count += $this->countApisInSubtree($child);
        }

        return $count;
    }

    private function apiToNode(Api $api): array
    {
        $tps = $this->tpsService->getCurrentTps($api);
        $typeLabel = ApiTypes::label($api->type);
        $integrations = $api->integratedSystems()->map(fn (System $sys) => [
            'id' => $sys->id,
            'name' => $sys->name,
            'vendor_name' => $sys->vendor?->name,
            'url' => route('integrations.system', $sys),
        ])->values()->all();

        return [
            'id' => 'api-'.$api->id,
            'api_id' => $api->id,
            'name' => $api->name,
            'type' => 'api',
            'api_type' => $api->type,
            'endpoint_url' => $api->defaultVersion?->endpoint_url,
            'version_count' => $api->relationLoaded('versions') ? $api->versions->count() : $api->versions()->count(),
            'default_version' => $api->defaultVersion?->version,
            'description' => $api->description,
            'tps_value' => $tps,
            'tps_label' => $tps !== null ? number_format($tps, 0).' TPS' : 'N/A',
            'display_label' => $api->name.' ('.$typeLabel.')',
            'url' => route('apis.show', $api),
            'integrated_systems' => $integrations,
            'integration_count' => count($integrations),
            'children' => collect($integrations)->map(fn ($sys) => [
                'id' => 'integration-'.$api->id.'-'.$sys['id'],
                'name' => $sys['name'],
                'type' => 'integration',
                'vendor_name' => $sys['vendor_name'],
                'url' => $sys['url'],
                'children' => [],
            ])->all(),
        ];
    }
}
