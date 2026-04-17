<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use PDO;

final class PermissionRepositoryPostgres implements PermissionRepositoryInterface
{
    use HandlesPostgresRepositoryExceptions;

    private PDO $pdo;
    private PostgresTableNames $tableNames;

    public function __construct(PDO $pdo, ?PostgresTableNames $tableNames = null)
    {
        $this->pdo = $pdo;
        $this->tableNames = $tableNames ?? PostgresTableNames::default();
    }

    public function findById(string $id): ?Permission
    {
        $tableName = $this->tableNames->permissions();

        return $this->runRepositoryOperation('find permission by id', $tableName, function () use ($id, $tableName): ?Permission {
            $statement = $this->pdo->prepare(sprintf('SELECT * FROM %s WHERE id = :id', $tableName));
            $statement->bindValue(':id', $id);
            $statement->execute();

            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if ($result === false) {
                return null;
            }

            return new Permission((string) $result['id'], (string) $result['context_id'], (string) $result['name'], isset($result['description']) ? (string) $result['description'] : null);
        });
    }

    public function findByContextId(string $contextId): array
    {
        $tableName = $this->tableNames->permissions();

        return $this->runRepositoryOperation('find permissions by context', $tableName, function () use ($contextId, $tableName): array {
            $statement = $this->pdo->prepare(sprintf('SELECT * FROM %s WHERE context_id = :context_id', $tableName));
            $statement->execute(['context_id' => $contextId]);

            return array_map(
                fn (array $row): Permission => new Permission($row['id'], $row['context_id'], $row['name'], $row['description']),
                $statement->fetchAll(PDO::FETCH_ASSOC),
            );
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
