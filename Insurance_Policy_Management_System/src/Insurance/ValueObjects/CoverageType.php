<?php

namespace Insurance\ValueObjects;

class CoverageType
{
    public const VEHICLE = 'vehicle';
    public const HEALTH = 'health';
    public const LIFE = 'life';
    public const PROPERTY = 'property';

    public static function all(): array
    {
        return [
            self::VEHICLE,
            self::HEALTH,
            self::LIFE,
            self::PROPERTY,
        ];
    }

    public static function isValid(string $type): bool
    {
        return in_array($type, self::all(), true);
    }
}

