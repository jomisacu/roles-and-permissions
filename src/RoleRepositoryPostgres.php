<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use PDO;

final class RoleRepositoryPostgres implements RoleRepositoryInterface
{
    use HandlesPostgresRepositoryExceptions;

    private PDO $pdo;
    private PostgresTableNames $tableNames;

    public function __construct(PDO $pdo, ?PostgresTableNames $tableNames = null)
    {
        $this->pdo = $pdo;
        $this->tableNames = $tableNames ?? PostgresTableNames::default();
    }

    public function findById(string $id): ?Role
    {
        $tableName = $this->tableNames->roles();

        return $this->runRepositoryOperation('find role by id', $tableName, function () use ($id, $tableName): ?Role {
            $statement = $this->pdo->prepare(sprintf('SELECT * FROM %s WHERE id = :id', $tableName));
            $statement->bindValue(':id', $id);
            $statement->execute();

            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if ($result === false) {
                return null;
            }

            return new Role((string) $result['id'], (string) $result['context_id'], (string) $result['name'], isset($result['description']) ? (string) $result['description'] : null);
        });
    }

    public function findByContextId(string $contextId): array
    {
        $tableName = $this->tableNames->roles();

        return $this->runRepositoryOperation('find roles by context', $tableName, function () use ($contextId, $tableName): array {
            $statement = $this->pdo->prepare(sprintf('SELECT * FROM %s WHERE context_id = :context_id', $tableName));
            $statement->bindValue(':context_id', $contextId);
            $statement->execute();

            return array_map(
                static fn (array $row): Role => new Role((string) $row['id'], (string) $row['context_id'], (string) $row['name'], isset($row['description']) ? (string) $row['description'] : null),
                $statement->fetchAll(PDO::FETCH_ASSOC),
            );
        });
    }

    public function create(Role $role): void
    {
        $tableName = $this->tableNames->roles();

        $this->runRepositoryOperation('create role', $tableName, function () use ($role, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('INSERT INTO %s (id, context_id, name, description) VALUES (:id, :context_id, :name, :description)', $tableName));
            $statement->execute([
                ':id' => $role->id,
                ':context_id' => $role->contextId,
                ':name' => $role->name,
                ':description' => $role->description,
            ]);
        });
    }

    public function update(Role $role): void
    {
        $tableName = $this->tableNames->roles();

        $this->runRepositoryOperation('update role', $tableName, function () use ($role, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('UPDATE %s SET name = :name, description = :description WHERE id = :id', $tableName));
            $statement->execute([
                ':id' => $role->id,
                ':name' => $role->name,
                ':description' => $role->description,
            ]);
        });
    }

    public function delete(Role $role): void
    {
        $tableName = $this->tableNames->roles();

        $this->runRepositoryOperation('delete role', $tableName, function () use ($role, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE id = :id', $tableName));
            $statement->bindValue(':id', $role->id);
            $statement->execute();
        });
    }
}
