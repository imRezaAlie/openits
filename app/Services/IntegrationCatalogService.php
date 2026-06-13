<?php

namespace App\Services;

use App\Support\ApiTypes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IntegrationCatalogService
{
    public function query(?int $domainId = null, ?int $vendorId = null, ?string $apiType = null): Collection
    {
        $query = DB::table('api_system')
            ->join('apis', 'apis.id', '=', 'api_system.api_id')
            ->join('systems as owner', 'owner.id', '=', 'apis.owner_system_id')
            ->join('systems as consumer', 'consumer.id', '=', 'api_system.system_id')
            ->leftJoin('vendors as owner_vendor', 'owner_vendor.id', '=', 'owner.vendor_id')
            ->leftJoin('vendors as consumer_vendor', 'consumer_vendor.id', '=', 'consumer.vendor_id')
            ->leftJoin('domains as owner_domain', 'owner_domain.id', '=', 'owner.domain_id')
            ->leftJoin('domains as consumer_domain', 'consumer_domain.id', '=', 'consumer.domain_id')
            ->whereColumn('api_system.system_id', '!=', 'apis.owner_system_id')
            ->select([
                'apis.id as api_id',
                'apis.name as api_name',
                'apis.type as api_type',
                'apis.description as api_description',
                'owner.id as owner_system_id',
                'owner.name as owner_system',
                'owner.system_type as owner_system_type',
                'owner_vendor.name as owner_vendor',
                'owner_domain.id as owner_domain_id',
                'owner_domain.name as owner_domain',
                'owner_domain.color as owner_domain_color',
                'consumer.id as consumer_system_id',
                'consumer.name as consumer_system',
                'consumer.system_type as consumer_system_type',
                'consumer_vendor.name as consumer_vendor',
                'consumer_domain.id as consumer_domain_id',
                'consumer_domain.name as consumer_domain',
                'consumer_domain.color as consumer_domain_color',
            ])
            ->orderBy('owner_domain.name')
            ->orderBy('owner.name')
            ->orderBy('apis.name')
            ->orderBy('consumer.name');

        if ($domainId) {
            $query->where(function ($q) use ($domainId) {
                $q->where('owner.domain_id', $domainId)
                    ->orWhere('consumer.domain_id', $domainId);
            });
        }

        if ($vendorId) {
            $query->where(function ($q) use ($vendorId) {
                $q->where('owner.vendor_id', $vendorId)
                    ->orWhere('consumer.vendor_id', $vendorId);
            });
        }

        if ($apiType) {
            $query->where('apis.type', $apiType);
        }

        return collect($query->get())->map(function ($row) {
            $row->api_type_label = ApiTypes::label($row->api_type);
            $row->cross_domain = $row->owner_domain_id && $row->consumer_domain_id
                && $row->owner_domain_id !== $row->consumer_domain_id;

            return $row;
        });
    }

    public function stats(Collection $links): array
    {
        return [
            'total' => $links->count(),
            'cross_domain' => $links->where('cross_domain', true)->count(),
            'by_type' => $links->groupBy('api_type')->map->count(),
            'by_domain' => $links->groupBy('owner_domain')->map->count(),
        ];
    }

    public function toCsv(Collection $links): string
    {
        $headers = [
            'API',
            'Type',
            'Owner System',
            'Owner Vendor',
            'Owner Domain',
            'Consumer System',
            'Consumer Vendor',
            'Consumer Domain',
            'Cross Domain',
        ];

        $rows = $links->map(fn ($l) => [
            $l->api_name,
            $l->api_type_label,
            $l->owner_system,
            $l->owner_vendor ?? '',
            $l->owner_domain ?? '',
            $l->consumer_system,
            $l->consumer_vendor ?? '',
            $l->consumer_domain ?? '',
            $l->cross_domain ? 'Yes' : 'No',
        ]);

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv ?: '';
    }

    public function buildLandscapeExport(): array
    {
        $domains = DB::table('domains')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'color']);

        $systems = DB::table('systems')
            ->leftJoin('vendors', 'vendors.id', '=', 'systems.vendor_id')
            ->leftJoin('domains', 'domains.id', '=', 'systems.domain_id')
            ->select([
                'systems.id',
                'systems.name',
                'systems.system_type',
                'systems.description',
                'systems.parent_system_id',
                'vendors.name as vendor',
                'domains.name as domain',
            ])
            ->orderBy('systems.name')
            ->get();

        $apis = DB::table('apis')
            ->leftJoin('systems as owner', 'owner.id', '=', 'apis.owner_system_id')
            ->select([
                'apis.id',
                'apis.name',
                'apis.type',
                'apis.description',
                'owner.name as owner_system',
            ])
            ->orderBy('apis.name')
            ->get();

        $integrations = $this->query()->map(fn ($l) => [
            'api' => $l->api_name,
            'type' => $l->api_type,
            'owner_system' => $l->owner_system,
            'owner_domain' => $l->owner_domain,
            'consumer_system' => $l->consumer_system,
            'consumer_domain' => $l->consumer_domain,
            'cross_domain' => $l->cross_domain,
        ])->values()->all();

        $dataStack = app(MappingCatalogService::class)->buildExport();

        return [
            'exported_at' => now()->toIso8601String(),
            'summary' => [
                'domains' => $domains->count(),
                'systems' => $systems->count(),
                'apis' => $apis->count(),
                'integrations' => count($integrations),
                'canonical_entities' => $dataStack['summary']['canonical_entities'] ?? 0,
                'field_mappings' => $dataStack['summary']['field_mappings'] ?? 0,
            ],
            'domains' => $domains,
            'systems' => $systems,
            'apis' => $apis,
            'integrations' => $integrations,
            'data_stack' => $dataStack,
        ];
    }
}
