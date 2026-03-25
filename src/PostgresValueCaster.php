<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class PostgresValueCaster
{
    public static function toBool(mixed $value): bool
    {
        return match (true) {
            is_bool($value) => $value,
            is_int($value) => $value === 1,
            is_string($value) => in_array(strtolower($value), ['1', 'true', 't', 'yes', 'y', 'on'], true),
            default => (bool) $value,
        };
    }
}
