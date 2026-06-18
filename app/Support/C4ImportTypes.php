<?php

namespace App\Support;

class C4ImportTypes
{
    public const OPENAPI = 'openapi';

    public const ASYNCAPI = 'asyncapi';

    public const STRUCTURIZR = 'structurizr';

    public const JSON_BACKUP = 'json_backup';

    /** @var list<string> */
    public const ALL = [
        self::OPENAPI,
        self::ASYNCAPI,
        self::STRUCTURIZR,
        self::JSON_BACKUP,
    ];

    /** @var array<string, string> */
    public const LABELS = [
        self::OPENAPI => 'OpenAPI 3.0/3.1',
        self::ASYNCAPI => 'AsyncAPI 2.x/3.x',
        self::STRUCTURIZR => 'Structurizr DSL',
        self::JSON_BACKUP => 'JSON Backup',
    ];

    public static function label(string $type): string
    {
        return self::LABELS[$type] ?? $type;
    }

    public static function validationRule(): string
    {
        return 'required|in:'.implode(',', self::ALL);
    }

    /** @return list<string> */
    public static function acceptedExtensions(string $type): array
    {
        return match ($type) {
            self::OPENAPI => ['json', 'yaml', 'yml'],
            self::ASYNCAPI => ['json', 'yaml', 'yml'],
            self::STRUCTURIZR => ['dsl', 'txt', 'structurizr'],
            self::JSON_BACKUP => ['json'],
            default => ['json'],
        };
    }
}
