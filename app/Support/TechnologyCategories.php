<?php

namespace App\Support;

class TechnologyCategories
{
    public const PROGRAMMING_LANGUAGE = 'programming_language';

    public const RUNTIME = 'runtime';

    public const FRAMEWORK = 'framework';

    public const CONTAINER = 'container';

    public const ORCHESTRATION = 'orchestration';

    public const WEB_SERVER = 'web_server';

    public const DATABASE = 'database';

    public const MESSAGING = 'messaging';

    public const CLOUD = 'cloud';

    /** @var list<string> */
    public const ALL = [
        self::PROGRAMMING_LANGUAGE,
        self::RUNTIME,
        self::FRAMEWORK,
        self::CONTAINER,
        self::ORCHESTRATION,
        self::WEB_SERVER,
        self::DATABASE,
        self::MESSAGING,
        self::CLOUD,
    ];

    /** @var array<string, string> */
    public const LABELS = [
        self::PROGRAMMING_LANGUAGE => 'Programming Language',
        self::RUNTIME => 'Runtime',
        self::FRAMEWORK => 'Framework',
        self::CONTAINER => 'Container',
        self::ORCHESTRATION => 'Orchestration',
        self::WEB_SERVER => 'Web Server',
        self::DATABASE => 'Database',
        self::MESSAGING => 'Messaging',
        self::CLOUD => 'Cloud',
    ];

    public static function label(string $category): string
    {
        return self::LABELS[$category] ?? ucfirst(str_replace('_', ' ', $category));
    }

    public static function validationRule(): string
    {
        return 'required|in:'.implode(',', self::ALL);
    }
}
