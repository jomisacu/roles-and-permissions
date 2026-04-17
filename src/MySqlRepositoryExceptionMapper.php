<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class MySqlRepositoryExceptionMapper
{
    public static function from(string $operation, string $table, \PDOException $exception): RepositoryException
    {
        $driverCode = isset($exception->errorInfo[1]) ? (int) $exception->errorInfo[1] : 0;
        $message = sprintf('Failed to %s in `%s`.', $operation, $table);

        switch ($driverCode) {
            case 1062:
                return new UniqueConstraintViolationException($message, $driverCode, $exception);

            case 1451:
            case 1452:
                return new ForeignKeyConstraintViolationException($message, $driverCode, $exception);
        }

        return new RepositoryException($message, $driverCode, $exception);
    }
}
