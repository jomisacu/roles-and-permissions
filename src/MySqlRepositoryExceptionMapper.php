<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class MySqlRepositoryExceptionMapper
{
    public static function from(string $operation, string $table, \PDOException $exception): RepositoryException
    {
        $driverCode = isset($exception->errorInfo[1]) ? (int) $exception->errorInfo[1] : 0;
        $message = sprintf('Failed to %s in `%s`.', $operation, $table);

        return match ($driverCode) {
            1062 => new UniqueConstraintViolationException($message, $driverCode, $exception),
            1451, 1452 => new ForeignKeyConstraintViolationException($message, $driverCode, $exception),
            default => new RepositoryException($message, $driverCode, $exception),
        };
    }
}
