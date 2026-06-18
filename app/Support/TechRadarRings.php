<?php

namespace App\Support;

class TechRadarRings
{
    public const ADOPT = 'adopt';

    public const TRIAL = 'trial';

    public const ASSESS = 'assess';

    public const HOLD = 'hold';

    /** @var list<string> */
    public const ALL = [
        self::ADOPT,
        self::TRIAL,
        self::ASSESS,
        self::HOLD,
    ];

    /** @var array<string, string> */
    public const LABELS = [
        self::ADOPT => 'Adopt',
        self::TRIAL => 'Trial',
        self::ASSESS => 'Assess',
        self::HOLD => 'Hold',
    ];

    /** Ring radius as fraction of max (inner = adopt) */
    /** @var array<string, float> */
    public const RADIUS = [
        self::ADOPT => 0.25,
        self::TRIAL => 0.45,
        self::ASSESS => 0.65,
        self::HOLD => 0.85,
    ];

    public static function label(string $ring): string
    {
        return self::LABELS[$ring] ?? ucfirst($ring);
    }

    public static function validationRule(): string
    {
        return 'required|in:'.implode(',', self::ALL);
    }
}
