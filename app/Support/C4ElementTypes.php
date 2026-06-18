<?php

namespace App\Support;

class C4ElementTypes
{
    public const CONTEXT = 'context';

    public const EXTERNAL_SYSTEM = 'external_system';

    public const USER = 'user';

    public const CONTAINER = 'container';

    public const COMPONENT = 'component';

    public const SYSTEM = 'system';

    /** @var list<string> */
    public const RELATIONSHIP_SOURCES = [
        self::CONTEXT,
        self::EXTERNAL_SYSTEM,
        self::USER,
        self::CONTAINER,
        self::COMPONENT,
        self::SYSTEM,
    ];

    public static function relationshipValidationRule(): string
    {
        return 'required|in:'.implode(',', self::RELATIONSHIP_SOURCES);
    }

    public static function levelColor(string $level): string
    {
        return match ($level) {
            self::CONTEXT, self::EXTERNAL_SYSTEM, self::USER => '#3b82f6',
            self::CONTAINER => '#06b6d4',
            self::COMPONENT => '#8b5cf6',
            default => '#64748b',
        };
    }
}
