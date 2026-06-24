<?php

namespace App\Services;

use App\Models\Api;
use App\Models\ApiVersion;
use App\Models\RestDetail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Yaml\Yaml;

class OpenApiImporter
{
    public function __construct(private OpenApiSpecBuilder $specBuilder) {}

    /**
     * @return array<int, Api>
     */
    public function import(UploadedFile|string $source, ?string $baseUrl = null): array
    {
        $content = $source instanceof UploadedFile
            ? file_get_contents($source->getRealPath())
            : $source;

        $spec = $this->parseContent($content);
        $servers = $spec['servers'] ?? [];
        $defaultBase = $baseUrl ?? ($servers[0]['url'] ?? '');
        $paths = $spec['paths'] ?? [];
        $created = [];

        DB::transaction(function () use ($paths, $defaultBase, $spec, &$created) {
            foreach ($paths as $path => $methods) {
                foreach ($methods as $method => $operation) {
                    if (! in_array(strtolower($method), ['get', 'post', 'put', 'patch', 'delete', 'head', 'options'])) {
                        continue;
                    }

                    $name = $operation['operationId']
                        ?? $operation['summary']
                        ?? strtoupper($method).' '.$path;

                    $endpoint = rtrim($defaultBase, '/').$path;

                    $api = Api::create([
                        'name' => $name,
                        'type' => 'rest',
                        'description' => $operation['description'] ?? $operation['summary'] ?? null,
                    ]);

                    $specVersion = $spec['info']['version'] ?? '1.0.0';

                    $version = ApiVersion::create([
                        'api_id' => $api->id,
                        'version' => $specVersion,
                        'endpoint_url' => $endpoint,
                        'request_format' => $this->detectRequestFormat($operation),
                        'response_format' => 'JSON',
                        'authentication_type' => $this->detectAuth($spec),
                        'status' => 'active',
                        'is_default' => true,
                    ]);

                    RestDetail::create([
                        'api_version_id' => $version->id,
                        'http_method' => strtoupper($method),
                        'request_parameters' => $this->extractParameters($operation),
                        'response_schema' => $operation['responses'] ?? null,
                        'openapi_spec' => $this->specBuilder->buildForImport(
                            $spec,
                            $path,
                            $method,
                            $operation,
                            $defaultBase,
                            $name
                        ),
                    ]);

                    $created[] = $api;
                }
            }
        });

        return $created;
    }

    private function parseContent(string $content): array
    {
        $trimmed = ltrim($content);

        if (str_starts_with($trimmed, '{')) {
            $decoded = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid OpenAPI JSON: '.json_last_error_msg());
            }

            return $decoded;
        }

        return Yaml::parse($content);
    }

    private function extractParameters(array $operation): array
    {
        $params = [];

        foreach ($operation['parameters'] ?? [] as $param) {
            $params[] = [
                'name' => $param['name'] ?? '',
                'in' => $param['in'] ?? '',
                'required' => $param['required'] ?? false,
                'type' => $param['schema']['type'] ?? $param['type'] ?? 'string',
                'description' => $param['description'] ?? null,
            ];
        }

        if (isset($operation['requestBody'])) {
            $params[] = [
                'name' => 'body',
                'in' => 'body',
                'required' => $operation['requestBody']['required'] ?? false,
                'schema' => $operation['requestBody']['content'] ?? null,
            ];
        }

        return $params;
    }

    private function detectRequestFormat(array $operation): string
    {
        $content = $operation['requestBody']['content'] ?? [];

        if (isset($content['application/json'])) {
            return 'JSON';
        }
        if (isset($content['application/xml'])) {
            return 'XML';
        }
        if (isset($content['application/x-www-form-urlencoded'])) {
            return 'Form';
        }

        return 'JSON';
    }

    private function detectAuth(array $spec): ?string
    {
        $schemes = $spec['components']['securitySchemes'] ?? $spec['securityDefinitions'] ?? [];

        if (empty($schemes)) {
            return null;
        }

        $types = array_map(fn ($s) => $s['type'] ?? $s['scheme'] ?? 'unknown', $schemes);

        return implode(', ', array_unique($types));
    }
}
