<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

trait HandlesPostgresRepositoryExceptions
{
    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    private function runRepositoryOperation(string $action, string $table, callable $operation)
    {
        try {
            return $operation();
        } catch (\PDOException $exception) {
            throw PostgresRepositoryExceptionMapper::from($action, $table, $exception);
        }
    }
}
