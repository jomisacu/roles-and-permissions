<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use Jomisacu\RolesAndPermissions\PermissionRepositoryInterface;

final class PermissionRepositoryMySql implements PermissionRepositoryInterface
{
    use HandlesMySqlRepositoryExceptions;

    private readonly MySqlTableNames $tableNames;

    public function __construct(
        private readonly \PDO $pdo,
        ?MySqlTableNames $tableNames = null,
    ) {
        $this->tableNames = $tableNames ?? MySqlTableNames::default();
    }
    /**
     * @inheritDoc
     */
    public function findByContextId(string $contextId): array
    {
        $tableName = $this->tableNames->permissions();

        return $this->runRepositoryOperation('find permissions by context', $tableName, function () use ($contextId, $tableName): array {
            $statement = $this->pdo->prepare(sprintf('SELECT * FROM %s WHERE context_id = :context_id', $tableName));
            $statement->execute(['context_id' => $contextId]);

            return array_map(fn($row) => new Permission($row['id'], $row['context_id'], $row['name'], $row['description']), $statement->fetchAll());
        });
    }

    public function create(Permission $permission): void
    {
        $tableName = $this->tableNames->permissions();

        $this->runRepositoryOperation('create permission', $tableName, function () use ($permission, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('INSERT INTO %s (id, context_id, name, description) VALUES (:id, :context_id, :name, :description)', $tableName));
            $statement->execute([
                'id' => $permission->id,
                'context_id' => $permission->contextId,
                'name' => $permission->name,
                'description' => $permission->description,
            ]);
        });
    }

    public function update(Permission $permission): void
    {
        $tableName = $this->tableNames->permissions();

        $this->runRepositoryOperation('update permission', $tableName, function () use ($permission, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('UPDATE %s SET name = :name, description = :description WHERE id = :id', $tableName));
            $statement->execute([
                'id' => $permission->id,
                'name' => $permission->name,
                'description' => $permission->description,
            ]);
        });
    }

    public function delete(Permission $permission): void
    {
        $tableName = $this->tableNames->permissions();

        $this->runRepositoryOperation('delete permission', $tableName, function () use ($permission, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE id = :id', $tableName));
            $statement->execute(['id' => $permission->id]);
        });
    }
}
