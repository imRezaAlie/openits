<?php

namespace App\Support;

class DiagramTypes
{
    public const BPMN = 'bpmn';

    public const SEQUENCE = 'sequence';

    public const ALL = [
        self::BPMN,
        self::SEQUENCE,
    ];

    public static function label(string $type): string
    {
        return match ($type) {
            self::SEQUENCE => 'Sequence Diagram',
            default => 'BPMN Process',
        };
    }

    public static function badgeClass(string $type): string
    {
        return match ($type) {
            self::SEQUENCE => 'info',
            default => 'primary',
        };
    }

    public static function defaultSequenceTemplate(): string
    {
        return <<<'MERMAID'
sequenceDiagram
    participant Client
    participant API Gateway
    participant Service
    participant Database

    Client->>API Gateway: HTTP Request
    API Gateway->>Service: Forward request
    Service->>Database: Query data
    Database-->>Service: Result set
    Service-->>API Gateway: Response payload
    API Gateway-->>Client: HTTP Response
MERMAID;
    }
}
