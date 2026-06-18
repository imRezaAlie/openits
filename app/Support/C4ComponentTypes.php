<?php

namespace App\Support;

class C4ComponentTypes
{
    public const CONTROLLER = 'controller';

    public const SERVICE = 'service';

    public const REPOSITORY = 'repository';

    public const DTO = 'dto';

    public const MAPPER = 'mapper';

    public const CONSUMER = 'consumer';

    public const PRODUCER = 'producer';

    /** @var list<string> */
    public const ALL = [
        self::CONTROLLER,
        self::SERVICE,
        self::REPOSITORY,
        self::DTO,
        self::MAPPER,
        self::CONSUMER,
        self::PRODUCER,
    ];

    /** @var array<string, string> */
    public const LABELS = [
        self::CONTROLLER => 'Controller',
        self::SERVICE => 'Service',
        self::REPOSITORY => 'Repository',
        self::DTO => 'DTO',
        self::MAPPER => 'Mapper',
        self::CONSUMER => 'Consumer',
        self::PRODUCER => 'Producer',
    ];

    public static function label(string $type): string
    {
        return self::LABELS[$type] ?? ucfirst($type);
    }

    public static function validationRule(): string
    {
        return 'required|in:'.implode(',', self::ALL);
    }
}
