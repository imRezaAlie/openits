<?php

namespace App\Support;

class C4ContainerTypes
{
    public const API_GATEWAY = 'api_gateway';

    public const DATABASE = 'database';

    public const FRONTEND = 'frontend';

    public const BACKEND = 'backend';

    public const QUEUE = 'queue';

    public const CACHE = 'cache';

    public const EVENT_BUS = 'event_bus';

    /** @var list<string> */
    public const ALL = [
        self::API_GATEWAY,
        self::DATABASE,
        self::FRONTEND,
        self::BACKEND,
        self::QUEUE,
        self::CACHE,
        self::EVENT_BUS,
    ];

    /** @var array<string, string> */
    public const LABELS = [
        self::API_GATEWAY => 'API Gateway',
        self::DATABASE => 'Database',
        self::FRONTEND => 'Frontend',
        self::BACKEND => 'Backend',
        self::QUEUE => 'Queue',
        self::CACHE => 'Cache',
        self::EVENT_BUS => 'Event Bus',
    ];

    public static function label(string $type): string
    {
        return self::LABELS[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    public static function validationRule(): string
    {
        return 'required|in:'.implode(',', self::ALL);
    }
}
