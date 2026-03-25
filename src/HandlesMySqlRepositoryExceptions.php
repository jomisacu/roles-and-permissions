<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

trait HandlesMySqlRepositoryExceptions
{
    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    private function runRepositoryOperation(string $action, string $table, callable $operation): mixed
    {
        try {
            return $operation();
        } catch (\PDOException $exception) {
            throw MySqlRepositoryExceptionMapper::from($action, $table, $exception);
        }
    }
}
