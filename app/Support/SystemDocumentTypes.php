<?php

namespace App\Support;

use Illuminate\Support\Str;

class SystemDocumentTypes
{
    public const OVERVIEW = 'overview';

    public const API_CATALOG = 'api-catalog';

    public const INTEGRATIONS = 'integrations';

    public const TECHNOLOGY_STACK = 'technology-stack';

    public const INFRASTRUCTURE = 'infrastructure';

    public const PROCESSES = 'processes';

    public const DATA_SCHEMAS = 'data-schemas';

    public const FULL = 'full';

    /** @return array<string, string> */
    public static function all(): array
    {
        return [
            self::OVERVIEW => 'System Overview',
            self::API_CATALOG => 'API Catalog',
            self::INTEGRATIONS => 'Integration Map',
            self::TECHNOLOGY_STACK => 'Technology Stack',
            self::INFRASTRUCTURE => 'Infrastructure',
            self::PROCESSES => 'Business Processes',
            self::DATA_SCHEMAS => 'Data Schemas',
            self::FULL => 'Complete Documentation',
        ];
    }

    public static function isValid(string $type): bool
    {
        return array_key_exists($type, self::all());
    }

    public static function label(string $type): string
    {
        return self::all()[$type] ?? 'Documentation';
    }

    public static function filename(string $type, string $systemName): string
    {
        return Str::slug($systemName).'-'.$type.'.md';
    }
}
