<?php

namespace App\Support;

class ServerTypes
{
    public const DATABASE = 'database';

    public const APPLICATION = 'application';

    public const WEB = 'web';

    public const CACHE = 'cache';

    public const MESSAGE_BROKER = 'message_broker';

    public const LOAD_BALANCER = 'load_balancer';

    public const FILE = 'file';

    public const OTHER = 'other';

    /** @var list<string> */
    public const ALL = [
        self::DATABASE,
        self::APPLICATION,
        self::WEB,
        self::CACHE,
        self::MESSAGE_BROKER,
        self::LOAD_BALANCER,
        self::FILE,
        self::OTHER,
    ];

    /** @var array<string, string> */
    public const LABELS = [
        self::DATABASE => 'Database Server',
        self::APPLICATION => 'Application Server',
        self::WEB => 'Web Server',
        self::CACHE => 'Cache Server',
        self::MESSAGE_BROKER => 'Message Broker',
        self::LOAD_BALANCER => 'Load Balancer',
        self::FILE => 'File Server',
        self::OTHER => 'Other',
    ];

    /** @var array<string, string> */
    public const ICONS = [
        self::DATABASE => 'fa-solid fa-database',
        self::APPLICATION => 'fa-solid fa-cubes',
        self::WEB => 'fa-solid fa-globe',
        self::CACHE => 'fa-solid fa-bolt',
        self::MESSAGE_BROKER => 'fa-solid fa-envelope',
        self::LOAD_BALANCER => 'fa-solid fa-scale-balanced',
        self::FILE => 'fa-solid fa-folder-open',
        self::OTHER => 'fa-solid fa-server',
    ];

    public static function label(string $type): string
    {
        return self::LABELS[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    public static function icon(string $type): string
    {
        return self::ICONS[$type] ?? self::ICONS[self::OTHER];
    }

    public static function validationRule(): string
    {
        return 'required|in:'.implode(',', self::ALL);
    }
}
