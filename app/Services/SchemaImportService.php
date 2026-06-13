<?php

namespace App\Services;

use App\Models\Api;
use App\Models\ApiVersion;
use App\Models\PlatformField;
use App\Models\PlatformSchema;
use App\Models\RestDetail;
use App\Models\System;
use App\Support\DataLayers;
use Illuminate\Support\Str;

class SchemaImportService
{
    /**
     * Extract platform fields from a REST API's OpenAPI spec or response schema.
     */
    public function importFromApi(Api $api, ?ApiVersion $version = null): PlatformSchema
    {
        $version ??= $api->resolveVersion();
        $system = $api->ownerSystem;

        if (! $system) {
            throw new \InvalidArgumentException('API must have an owner system to import schema.');
        }

        $restDetail = $version?->restDetail;
        $fields = [];

        if ($restDetail?->openapi_spec) {
            $fields = $this->extractFromOpenApi($restDetail->openapi_spec);
        } elseif ($restDetail?->response_schema) {
            $fields = $this->extractFromJsonSchema($restDetail->response_schema);
        } elseif ($restDetail?->request_parameters) {
            $fields = $this->extractFromJsonSchema($restDetail->request_parameters);
        }

        $slug = Str::slug($api->name.'-'.$version?->version);

        $schema = PlatformSchema::updateOrCreate(
            [
                'system_id' => $system->id,
                'slug' => $slug,
            ],
            [
                'name' => $api->name,
                'description' => "Imported from API: {$api->name} ({$version?->version})",
                'data_layer' => DataLayers::BRONZE,
                'source_type' => 'openapi',
                'version' => $version?->version,
                'metadata' => [
                    'api_id' => $api->id,
                    'api_version_id' => $version?->id,
                ],
            ]
        );

        $sortOrder = 0;
        foreach ($fields as $field) {
            PlatformField::updateOrCreate(
                [
                    'platform_schema_id' => $schema->id,
                    'native_name' => $field['name'],
                ],
                [
                    'native_path' => $field['path'] ?? null,
                    'data_type' => $field['type'] ?? 'string',
                    'description' => $field['description'] ?? null,
                    'is_primary_key' => $field['is_primary_key'] ?? false,
                    'nullable' => $field['nullable'] ?? true,
                    'sort_order' => $sortOrder++,
                ]
            );
        }

        return $schema->load('fields');
    }

    /**
     * Import from all REST APIs owned by a system.
     *
     * @return array<int, PlatformSchema>
     */
    public function importFromSystem(System $system): array
    {
        $schemas = [];

        foreach ($system->ownedApis()->where('type', 'rest')->get() as $api) {
            $version = $api->resolveVersion();
            if ($version->restDetail) {
                $schemas[] = $this->importFromApi($api, $version);
            }
        }

        return $schemas;
    }

    /**
     * @return array<int, array{name: string, path?: string, type?: string, description?: string, nullable?: bool}>
     */
    private function extractFromOpenApi(array $spec): array
    {
        $fields = [];
        $schemas = $spec['components']['schemas'] ?? [];

        foreach ($schemas as $schemaName => $schemaDef) {
            $fields = array_merge($fields, $this->flattenSchemaProperties($schemaDef, $schemaName));
        }

        if (empty($fields) && isset($spec['paths'])) {
            foreach ($spec['paths'] as $path => $methods) {
                foreach ($methods as $method => $operation) {
                    if (! is_array($operation)) {
                        continue;
                    }
                    foreach ($operation['parameters'] ?? [] as $param) {
                        $fields[] = [
                            'name' => $param['name'] ?? 'unknown',
                            'path' => $path.'.'.$param['name'],
                            'type' => $param['schema']['type'] ?? $param['type'] ?? 'string',
                            'description' => $param['description'] ?? null,
                            'nullable' => ! ($param['required'] ?? false),
                        ];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * @return array<int, array{name: string, path?: string, type?: string, description?: string, nullable?: bool}>
     */
    private function extractFromJsonSchema(mixed $schema): array
    {
        if (! is_array($schema)) {
            return [];
        }

        if (isset($schema['raw'])) {
            $decoded = json_decode($schema['raw'], true);

            return is_array($decoded) ? $this->extractFromJsonSchema($decoded) : [];
        }

        return $this->flattenSchemaProperties($schema);
    }

    /**
     * @return array<int, array{name: string, path?: string, type?: string, description?: string, nullable?: bool, is_primary_key?: bool}>
     */
    private function flattenSchemaProperties(array $schema, string $prefix = ''): array
    {
        $fields = [];
        $properties = $schema['properties'] ?? [];

        if (empty($properties) && isset($schema['type']) && $schema['type'] === 'object' && isset($schema['additionalProperties'])) {
            return $fields;
        }

        $required = $schema['required'] ?? [];

        foreach ($properties as $name => $prop) {
            $path = $prefix ? "{$prefix}.{$name}" : $name;
            $type = is_array($prop) ? ($prop['type'] ?? 'object') : 'string';

            $fields[] = [
                'name' => $name,
                'path' => $path,
                'type' => is_array($type) ? ($type[0] ?? 'string') : $type,
                'description' => is_array($prop) ? ($prop['description'] ?? null) : null,
                'nullable' => ! in_array($name, $required, true),
                'is_primary_key' => $name === 'id' || str_ends_with(strtolower($name), '_id'),
            ];

            if (is_array($prop) && ($prop['type'] ?? null) === 'object' && isset($prop['properties'])) {
                $fields = array_merge($fields, $this->flattenSchemaProperties($prop, $path));
            }
        }

        return $fields;
    }
}
