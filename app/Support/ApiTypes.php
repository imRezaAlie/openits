<?php

namespace App\Support;

class ApiTypes
{
    public const REST = 'rest';

    public const GRAPHQL = 'graphql';

    public const GRPC = 'grpc';

    public const WEBSOCKET = 'websocket';

    public const SSE = 'sse';

    public const SOCKETIO = 'socketio';

    public const SOAP = 'soap';

    public const FTPS = 'ftps';

    public const SFTP = 'sftp';

    public const ZABBIX = 'zabbix';

    public const SIEM = 'siem';

    public const SPLUNK = 'splunk';

    /** @var list<string> */
    public const API_PROTOCOLS = [
        self::REST,
        self::GRAPHQL,
        self::GRPC,
        self::WEBSOCKET,
        self::SSE,
        self::SOCKETIO,
        self::SOAP,
    ];

    /** @var list<string> */
    public const NON_API_INTEGRATIONS = [
        self::FTPS,
        self::SFTP,
        self::ZABBIX,
        self::SIEM,
        self::SPLUNK,
    ];

    /** @var list<string> */
    public const ALL = [
        ...self::API_PROTOCOLS,
        ...self::NON_API_INTEGRATIONS,
    ];

    /** @var array<string, string> */
    public const LABELS = [
        self::REST => 'REST',
        self::GRAPHQL => 'GraphQL',
        self::GRPC => 'gRPC',
        self::WEBSOCKET => 'WebSocket',
        self::SSE => 'SSE',
        self::SOCKETIO => 'Socket.IO',
        self::SOAP => 'SOAP',
        self::FTPS => 'FTPS',
        self::SFTP => 'SFTP',
        self::ZABBIX => 'Zabbix',
        self::SIEM => 'SIEM',
        self::SPLUNK => 'Splunk',
    ];

    public const GROUP_API = 'API Protocols';

    public const GROUP_NON_API = 'Non-API Integrations';

    public static function label(string $type): string
    {
        return self::LABELS[$type] ?? strtoupper($type);
    }

    public static function validationRule(): string
    {
        return 'required|in:'.implode(',', self::ALL);
    }

    public static function isNonApiIntegration(string $type): bool
    {
        return in_array($type, self::NON_API_INTEGRATIONS, true);
    }

    public static function badgeClass(string $type): string
    {
        return match ($type) {
            self::REST => 'rest',
            self::GRAPHQL => 'graphql',
            self::GRPC => 'grpc',
            self::WEBSOCKET => 'websocket',
            self::SSE => 'sse',
            self::SOCKETIO => 'socketio',
            self::SOAP => 'soap',
            self::FTPS => 'ftps',
            self::SFTP => 'sftp',
            self::ZABBIX => 'zabbix',
            self::SIEM => 'siem',
            self::SPLUNK => 'splunk',
            default => 'secondary',
        };
    }

    public static function treeClass(string $type): string
    {
        return 'api-'.self::badgeClass($type);
    }

    public static function usesRestDetail(string $type): bool
    {
        return $type === self::REST;
    }

    public static function usesSoapDetail(string $type): bool
    {
        return $type === self::SOAP;
    }

    public static function usesProtocolDetails(string $type): bool
    {
        return ! self::usesRestDetail($type) && ! self::usesSoapDetail($type);
    }
}
