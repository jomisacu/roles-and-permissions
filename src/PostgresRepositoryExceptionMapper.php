<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class PostgresRepositoryExceptionMapper
{
    public static function from(string $operation, string $table, \PDOException $exception): RepositoryException
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $message = sprintf('Failed to %s in `%s`.', $operation, $table);

        return match ($sqlState) {
            '23505' => new UniqueConstraintViolationException($message, 0, $exception),
            '23503' => new ForeignKeyConstraintViolationException($message, 0, $exception),
            default => new RepositoryException($message, 0, $exception),
        };
    }
}
