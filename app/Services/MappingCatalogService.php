<?php

namespace App\Services;

use App\Models\CanonicalEntity;
use App\Models\FieldMapping;
use App\Models\PlatformSchema;
use App\Support\DataLayers;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MappingCatalogService
{
    public function query(?int $systemId = null, ?int $entityId = null): Collection
    {
        $query = DB::table('field_mappings')
            ->join('platform_fields', 'platform_fields.id', '=', 'field_mappings.platform_field_id')
            ->join('platform_schemas', 'platform_schemas.id', '=', 'platform_fields.platform_schema_id')
            ->join('systems', 'systems.id', '=', 'platform_schemas.system_id')
            ->join('canonical_attributes', 'canonical_attributes.id', '=', 'field_mappings.canonical_attribute_id')
            ->join('canonical_entities', 'canonical_entities.id', '=', 'canonical_attributes.canonical_entity_id')
            ->leftJoin('api_versions', 'api_versions.id', '=', 'field_mappings.api_version_id')
            ->select([
                'field_mappings.id',
                'field_mappings.direction',
                'field_mappings.transform_rule',
                'field_mappings.notes',
                'systems.id as system_id',
                'systems.name as system_name',
                'platform_schemas.name as schema_name',
                'platform_schemas.data_layer',
                'platform_fields.native_name',
                'platform_fields.native_path',
                'platform_fields.data_type as platform_data_type',
                'canonical_entities.id as entity_id',
                'canonical_entities.name as entity_name',
                'canonical_attributes.id as attribute_id',
                'canonical_attributes.name as attribute_name',
                'canonical_attributes.data_type as canonical_data_type',
                'api_versions.version as api_version',
            ])
            ->orderBy('canonical_entities.name')
            ->orderBy('canonical_attributes.name')
            ->orderBy('systems.name');

        if ($systemId) {
            $query->where('systems.id', $systemId);
        }

        if ($entityId) {
            $query->where('canonical_entities.id', $entityId);
        }

        return collect($query->get())->map(function ($row) {
            $row->data_layer_label = DataLayers::label($row->data_layer);

            return $row;
        });
    }

    public function stats(): array
    {
        return [
            'canonical_entities' => CanonicalEntity::count(),
            'canonical_attributes' => DB::table('canonical_attributes')->count(),
            'platform_schemas' => PlatformSchema::count(),
            'platform_fields' => DB::table('platform_fields')->count(),
            'field_mappings' => FieldMapping::count(),
            'mapped_fields_pct' => $this->mappedFieldsPercentage(),
            'by_layer' => PlatformSchema::selectRaw('data_layer, COUNT(*) as aggregate')
                ->groupBy('data_layer')
                ->pluck('aggregate', 'data_layer'),
        ];
    }

    public function buildExport(): array
    {
        $entities = CanonicalEntity::with(['attributes' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('name')
            ->get()
            ->map(fn (CanonicalEntity $entity) => [
                'id' => $entity->id,
                'name' => $entity->name,
                'slug' => $entity->slug,
                'description' => $entity->description,
                'attributes' => $entity->attributes->map(fn ($attr) => [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'slug' => $attr->slug,
                    'data_type' => $attr->data_type,
                    'is_required' => $attr->is_required,
                    'description' => $attr->description,
                ])->values()->all(),
            ]);

        $schemas = PlatformSchema::with(['system', 'fields'])
            ->orderBy('name')
            ->get()
            ->map(fn (PlatformSchema $schema) => [
                'id' => $schema->id,
                'system' => $schema->system?->name,
                'name' => $schema->name,
                'slug' => $schema->slug,
                'data_layer' => $schema->data_layer,
                'source_type' => $schema->source_type,
                'version' => $schema->version,
                'fields' => $schema->fields->map(fn ($field) => [
                    'id' => $field->id,
                    'native_name' => $field->native_name,
                    'native_path' => $field->native_path,
                    'data_type' => $field->data_type,
                    'is_primary_key' => $field->is_primary_key,
                ])->values()->all(),
            ]);

        $mappings = $this->query()->map(fn ($m) => [
            'system' => $m->system_name,
            'schema' => $m->schema_name,
            'data_layer' => $m->data_layer,
            'platform_field' => $m->native_name,
            'platform_path' => $m->native_path,
            'canonical_entity' => $m->entity_name,
            'canonical_attribute' => $m->attribute_name,
            'direction' => $m->direction,
            'transform_rule' => $m->transform_rule,
            'api_version' => $m->api_version,
        ])->values()->all();

        return [
            'exported_at' => now()->toIso8601String(),
            'architecture' => 'modern_data_stack',
            'layers' => collect(DataLayers::all())->map(fn ($layer) => [
                'key' => $layer,
                'label' => DataLayers::label($layer),
                'description' => DataLayers::description($layer),
            ])->values()->all(),
            'summary' => $this->stats(),
            'canonical_entities' => $entities,
            'platform_schemas' => $schemas,
            'field_mappings' => $mappings,
        ];
    }

    private function mappedFieldsPercentage(): float
    {
        $totalFields = DB::table('platform_fields')->count();
        if ($totalFields === 0) {
            return 0;
        }

        $mappedFields = DB::table('platform_fields')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('field_mappings')
                    ->whereColumn('field_mappings.platform_field_id', 'platform_fields.id');
            })
            ->count();

        return round(($mappedFields / $totalFields) * 100, 1);
    }
}
