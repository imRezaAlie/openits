<?php

namespace App\Support;

class AdrStatuses
{
    public const PROPOSED = 'proposed';

    public const ACCEPTED = 'accepted';

    public const DEPRECATED = 'deprecated';

    public const SUPERSEDED = 'superseded';

    /** @var list<string> */
    public const ALL = [
        self::PROPOSED,
        self::ACCEPTED,
        self::DEPRECATED,
        self::SUPERSEDED,
    ];

    /** @var array<string, string> */
    public const LABELS = [
        self::PROPOSED => 'Proposed',
        self::ACCEPTED => 'Accepted',
        self::DEPRECATED => 'Deprecated',
        self::SUPERSEDED => 'Superseded',
    ];

    public static function label(string $status): string
    {
        return self::LABELS[$status] ?? ucfirst($status);
    }

    public static function validationRule(): string
    {
        return 'required|in:'.implode(',', self::ALL);
    }

    public static function badgeClass(string $status): string
    {
        return match ($status) {
            self::ACCEPTED => 'success',
            self::PROPOSED => 'info',
            self::DEPRECATED => 'warning',
            self::SUPERSEDED => 'secondary',
            default => 'light',
        };
    }
}
