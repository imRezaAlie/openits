<?php

namespace App\Services;

use App\Models\Api;
use App\Models\ApiVersion;
use App\Support\ApiTypes;

class ProtocolSpecBuilder
{
    public function build(Api $api, ?ApiVersion $version = null): array
    {
        $version = $version ?? $api->resolveVersion();
        $details = $version->protocol_details ?? [];

        return match ($api->type) {
            ApiTypes::GRAPHQL => $this->buildGraphql($api, $version, $details),
            ApiTypes::GRPC => $this->buildGrpc($api, $version, $details),
            ApiTypes::WEBSOCKET => $this->buildWebSocket($api, $version, $details),
            ApiTypes::SSE => $this->buildSse($api, $version, $details),
            ApiTypes::SOCKETIO => $this->buildSocketIo($api, $version, $details),
            ApiTypes::FTPS => $this->buildFileTransfer($api, $version, $details, 'FTPS'),
            ApiTypes::SFTP => $this->buildFileTransfer($api, $version, $details, 'SFTP'),
            ApiTypes::ZABBIX => $this->buildZabbix($api, $version, $details),
            ApiTypes::SIEM => $this->buildSiem($api, $version, $details),
            ApiTypes::SPLUNK => $this->buildSplunk($api, $version, $details),
            default => ['type' => $api->type, 'version' => $version->version, 'details' => $details],
        };
    }

    private function buildGraphql(Api $api, ApiVersion $version, array $details): array
    {
        return [
            'type' => ApiTypes::GRAPHQL,
            'title' => $api->name,
            'description' => $api->description,
            'version' => $version->version,
            'endpoint' => $version->endpoint_url,
            'operation_type' => $details['operation_type'] ?? 'query',
            'schema_url' => $details['schema_url'] ?? null,
            'operation_name' => $details['operation_name'] ?? null,
            'query' => $details['query'] ?? null,
            'authentication_type' => $version->authentication_type,
        ];
    }

    private function buildGrpc(Api $api, ApiVersion $version, array $details): array
    {
        return [
            'type' => ApiTypes::GRPC,
            'title' => $api->name,
            'description' => $api->description,
            'version' => $version->version,
            'endpoint' => $version->endpoint_url,
            'service_name' => $details['service_name'] ?? null,
            'method_name' => $details['method_name'] ?? null,
            'proto_url' => $details['proto_url'] ?? null,
            'rpc_type' => $details['rpc_type'] ?? 'unary',
        ];
    }

    private function buildWebSocket(Api $api, ApiVersion $version, array $details): array
    {
        return [
            'type' => ApiTypes::WEBSOCKET,
            'title' => $api->name,
            'description' => $api->description,
            'version' => $version->version,
            'endpoint' => $version->endpoint_url,
            'subprotocol' => $details['subprotocol'] ?? null,
            'message_format' => $details['message_format'] ?? $version->request_format,
            'handshake_headers' => $details['handshake_headers'] ?? null,
        ];
    }

    private function buildSse(Api $api, ApiVersion $version, array $details): array
    {
        return [
            'type' => ApiTypes::SSE,
            'title' => $api->name,
            'description' => $api->description,
            'version' => $version->version,
            'endpoint' => $version->endpoint_url,
            'event_types' => $details['event_types'] ?? null,
            'retry_interval' => $details['retry_interval'] ?? null,
        ];
    }

    private function buildSocketIo(Api $api, ApiVersion $version, array $details): array
    {
        return [
            'type' => ApiTypes::SOCKETIO,
            'title' => $api->name,
            'description' => $api->description,
            'version' => $version->version,
            'endpoint' => $version->endpoint_url,
            'namespace' => $details['namespace'] ?? '/',
            'transport' => $details['transport'] ?? 'websocket',
            'events_emit' => $details['events_emit'] ?? null,
            'events_listen' => $details['events_listen'] ?? null,
        ];
    }

    private function buildFileTransfer(Api $api, ApiVersion $version, array $details, string $label): array
    {
        return [
            'type' => $api->type,
            'integration_kind' => 'non_api',
            'title' => $api->name,
            'description' => $api->description,
            'version' => $version->version,
            'connection' => $version->endpoint_url,
            'protocol' => $label,
            'port' => $details['port'] ?? null,
            'remote_path' => $details['remote_path'] ?? null,
            'direction' => $details['direction'] ?? null,
            'file_pattern' => $details['file_pattern'] ?? null,
            'passive_mode' => $details['passive_mode'] ?? null,
            'auth_method' => $details['auth_method'] ?? null,
            'authentication_type' => $version->authentication_type,
        ];
    }

    private function buildZabbix(Api $api, ApiVersion $version, array $details): array
    {
        return [
            'type' => ApiTypes::ZABBIX,
            'integration_kind' => 'non_api',
            'title' => $api->name,
            'description' => $api->description,
            'version' => $version->version,
            'connection' => $version->endpoint_url,
            'agent_type' => $details['agent_type'] ?? null,
            'host_group' => $details['host_group'] ?? null,
            'template' => $details['template'] ?? null,
            'monitored_host' => $details['monitored_host'] ?? null,
            'trigger_severity' => $details['trigger_severity'] ?? null,
        ];
    }

    private function buildSiem(Api $api, ApiVersion $version, array $details): array
    {
        return [
            'type' => ApiTypes::SIEM,
            'integration_kind' => 'non_api',
            'title' => $api->name,
            'description' => $api->description,
            'version' => $version->version,
            'connection' => $version->endpoint_url,
            'platform' => $details['platform'] ?? null,
            'log_format' => $details['log_format'] ?? null,
            'ingestion_method' => $details['ingestion_method'] ?? null,
            'source_index' => $details['source_index'] ?? null,
            'port' => $details['port'] ?? null,
            'authentication_type' => $version->authentication_type,
        ];
    }

    private function buildSplunk(Api $api, ApiVersion $version, array $details): array
    {
        return [
            'type' => ApiTypes::SPLUNK,
            'integration_kind' => 'non_api',
            'title' => $api->name,
            'description' => $api->description,
            'version' => $version->version,
            'connection' => $version->endpoint_url,
            'ingestion_type' => $details['ingestion_type'] ?? null,
            'index' => $details['index'] ?? null,
            'sourcetype' => $details['sourcetype'] ?? null,
            'source' => $details['source'] ?? null,
            'hec_port' => $details['hec_port'] ?? null,
            'authentication_type' => $version->authentication_type,
        ];
    }
}
