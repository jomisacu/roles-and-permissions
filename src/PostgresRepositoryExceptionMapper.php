<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class PostgresRepositoryExceptionMapper
{
    public static function from(string $operation, string $table, \PDOException $exception): RepositoryException
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $message = sprintf('Failed to %s in `%s`.', $operation, $table);

        switch ($sqlState) {
            case '23505':
                return new UniqueConstraintViolationException($message, 0, $exception);

            case '23503':
                return new ForeignKeyConstraintViolationException($message, 0, $exception);
        }

        return new RepositoryException($message, 0, $exception);
    }
}
