<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class PostgresValueCaster
{
    /**
     * @param mixed $value
     */
    public static function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 't', 'yes', 'y', 'on'], true);
        }

        return (bool) $value;
    }
}
