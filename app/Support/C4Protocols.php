<?php

namespace App\Support;

class C4Protocols
{
    public const REST = 'REST';

    public const GRAPHQL = 'GraphQL';

    public const GRPC = 'gRPC';

    public const WEBSOCKET = 'WebSocket';

    public const KAFKA = 'Kafka';

    public const RABBITMQ = 'RabbitMQ';

    public const SOAP = 'SOAP';

    public const JDBC = 'JDBC';

    public const HTTP = 'HTTP';

    /** @var list<string> */
    public const ALL = [
        self::REST,
        self::GRAPHQL,
        self::GRPC,
        self::WEBSOCKET,
        self::KAFKA,
        self::RABBITMQ,
        self::SOAP,
        self::JDBC,
        self::HTTP,
    ];

    public static function validationRule(): string
    {
        return 'required|in:'.implode(',', self::ALL);
    }

    public static function fromApiType(string $apiType): string
    {
        return match ($apiType) {
            ApiTypes::REST => self::REST,
            ApiTypes::GRAPHQL => self::GRAPHQL,
            ApiTypes::GRPC => self::GRPC,
            ApiTypes::WEBSOCKET => self::WEBSOCKET,
            ApiTypes::SOAP => self::SOAP,
            default => self::HTTP,
        };
    }
}
