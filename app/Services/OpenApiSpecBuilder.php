<?php

namespace App\Services;

use App\Models\Api;
use App\Models\ApiVersion;
use Illuminate\Support\Str;

class OpenApiSpecBuilder
{
    public function build(Api $api, ?ApiVersion $version = null): array
    {
        $version = $version ?? $api->resolveVersion();

        if ($version->restDetail?->openapi_spec) {
            return $version->restDetail->openapi_spec;
        }

        return $this->buildFromFields($api, $version);
    }

    public function buildAndPersist(Api $api, ?ApiVersion $version = null): array
    {
        $version = $version ?? $api->resolveVersion();
        $spec = $this->buildFromFields($api, $version);

        if ($version->restDetail) {
            $version->restDetail->update(['openapi_spec' => $spec]);
        }

        return $spec;
    }

    public function buildForImport(
        array $fullSpec,
        string $path,
        string $method,
        array $operation,
        string $baseUrl,
        string $title,
    ): array {
        $spec = [
            'openapi' => $fullSpec['openapi'] ?? '3.0.3',
            'info' => [
                'title' => $title,
                'description' => $operation['description'] ?? $operation['summary'] ?? null,
                'version' => $fullSpec['info']['version'] ?? '1.0.0',
            ],
            'servers' => $fullSpec['servers'] ?? [['url' => rtrim($baseUrl, '/') ?: '/']],
            'paths' => [
                $path => [
                    strtolower($method) => $operation,
                ],
            ],
        ];

        if (! empty($fullSpec['components'])) {
            $spec['components'] = $fullSpec['components'];
        }

        if (! empty($fullSpec['security'])) {
            $spec['security'] = $fullSpec['security'];
        }

        if (! empty($operation['security'])) {
            $spec['paths'][$path][strtolower($method)]['security'] = $operation['security'];
        }

        return $spec;
    }

    private function buildFromFields(Api $api, ApiVersion $version): array
    {
        $rest = $version->restDetail;
        $method = strtolower($rest?->http_method ?? 'get');
        [$serverUrl, $path] = $this->parseEndpoint($version->endpoint_url);

        $operation = [
            'summary' => $api->name,
            'description' => $api->description,
            'operationId' => Str::slug($api->name, '_') ?: 'operation',
            'parameters' => $this->buildParameters($rest?->request_parameters ?? []),
            'responses' => $this->buildResponses($rest?->response_schema),
        ];

        $requestBody = $this->buildRequestBody($rest?->request_parameters ?? [], $version->request_format);
        if ($requestBody) {
            $operation['requestBody'] = $requestBody;
        }

        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => $api->name,
                'description' => $api->description,
                'version' => $version->version,
            ],
            'servers' => [['url' => $serverUrl]],
            'paths' => [
                $path => [
                    $method => $operation,
                ],
            ],
        ];

        if ($version->authentication_type) {
            $scheme = $this->mapAuthScheme($version->authentication_type);
            $spec['components'] = ['securitySchemes' => ['defaultAuth' => $scheme]];
            $operation['security'] = [['defaultAuth' => []]];
            $spec['paths'][$path][$method] = $operation;
        }

        return $spec;
    }

    private function parseEndpoint(?string $endpointUrl): array
    {
        if (empty($endpointUrl)) {
            return ['https://api.example.com', '/'];
        }

        $parts = parse_url($endpointUrl);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? 'api.example.com';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';

        return ["{$scheme}://{$host}{$port}", $path.$query];
    }

    private function buildParameters(array $params): array
    {
        $parameters = [];

        foreach ($params as $param) {
            if (($param['in'] ?? '') === 'body') {
                continue;
            }

            $parameters[] = array_filter([
                'name' => $param['name'] ?? 'param',
                'in' => $param['in'] ?? 'query',
                'required' => $param['required'] ?? false,
                'description' => $param['description'] ?? null,
                'schema' => is_array($param['schema'] ?? null)
                    ? $param['schema']
                    : ['type' => $param['type'] ?? 'string'],
            ]);
        }

        return $parameters;
    }

    private function buildRequestBody(array $params, ?string $format): ?array
    {
        $bodyParam = collect($params)->first(fn ($p) => ($p['in'] ?? '') === 'body');

        if ($bodyParam && isset($bodyParam['schema'])) {
            $content = is_array($bodyParam['schema']) && ! isset($bodyParam['schema']['type'])
                ? $bodyParam['schema']
                : ['application/json' => ['schema' => $bodyParam['schema'] ?? ['type' => 'object']]];

            return [
                'required' => $bodyParam['required'] ?? false,
                'content' => $content,
            ];
        }

        if (in_array(strtoupper($format ?? ''), ['JSON', 'XML', 'FORM'])) {
            $mediaType = match (strtoupper($format)) {
                'XML' => 'application/xml',
                'FORM' => 'application/x-www-form-urlencoded',
                default => 'application/json',
            };

            return [
                'required' => true,
                'content' => [
                    $mediaType => [
                        'schema' => ['type' => 'object'],
                    ],
                ],
            ];
        }

        return null;
    }

    private function buildResponses(?array $responseSchema): array
    {
        if ($responseSchema && ! empty($responseSchema)) {
            return $responseSchema;
        }

        return [
            '200' => [
                'description' => 'Successful response',
                'content' => [
                    'application/json' => [
                        'schema' => ['type' => 'object'],
                    ],
                ],
            ],
        ];
    }

    private function mapAuthScheme(string $authType): array
    {
        $lower = strtolower($authType);

        if (str_contains($lower, 'bearer') || str_contains($lower, 'jwt')) {
            return ['type' => 'http', 'scheme' => 'bearer'];
        }
        if (str_contains($lower, 'basic')) {
            return ['type' => 'http', 'scheme' => 'basic'];
        }
        if (str_contains($lower, 'api') || str_contains($lower, 'key')) {
            return ['type' => 'apiKey', 'in' => 'header', 'name' => 'X-API-Key'];
        }
        if (str_contains($lower, 'oauth')) {
            return ['type' => 'oauth2', 'flows' => ['implicit' => ['authorizationUrl' => '/', 'scopes' => []]]];
        }

        return ['type' => 'apiKey', 'in' => 'header', 'name' => 'Authorization'];
    }
}
