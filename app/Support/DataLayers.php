<?php

namespace App\Support;

class DataLayers
{
    public const BRONZE = 'bronze';

    public const SILVER = 'silver';

    public const GOLD = 'gold';

    public const NATIVE = 'native';

    public static function all(): array
    {
        return [
            self::BRONZE,
            self::SILVER,
            self::GOLD,
            self::NATIVE,
        ];
    }

    public static function label(string $layer): string
    {
        return match ($layer) {
            self::BRONZE => 'Bronze (Raw)',
            self::SILVER => 'Silver (Cleaned)',
            self::GOLD => 'Gold (Canonical)',
            self::NATIVE => 'Native (Platform)',
            default => ucfirst($layer),
        };
    }

    public static function description(string $layer): string
    {
        return match ($layer) {
            self::BRONZE => 'Raw data as received from source platforms',
            self::SILVER => 'Validated and normalized platform-specific data',
            self::GOLD => 'Business-ready canonical model shared across systems',
            self::NATIVE => 'Original platform dictionary and field names',
            default => '',
        };
    }

    public static function badgeClass(string $layer): string
    {
        return match ($layer) {
            self::BRONZE => 'warning',
            self::SILVER => 'info',
            self::GOLD => 'success',
            self::NATIVE => 'secondary',
            default => 'light',
        };
    }
}
