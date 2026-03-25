<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use Exception;
use PDO;

final class RoleRepositoryMySql implements RoleRepositoryInterface
{
    use HandlesMySqlRepositoryExceptions;

    private readonly MySqlTableNames $tableNames;

    public function __construct(private readonly PDO $pdo, ?MySqlTableNames $tableNames = null)
    {
        $this->tableNames = $tableNames ?? MySqlTableNames::default();
    }

    /**
     * @throws Exception
     */
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

            return new Role($result['id'], $result['context_id'], $result['name'], $result['description']);
        });
    }

    public function findByContextId(string $contextId): array
    {
        $tableName = $this->tableNames->roles();

        return $this->runRepositoryOperation('find roles by context', $tableName, function () use ($contextId, $tableName): array {
            $statement = $this->pdo->prepare(sprintf('SELECT * FROM %s WHERE context_id = :context_id', $tableName));
            $statement->bindValue(':context_id', $contextId);
            $statement->execute();

            $roles = $statement->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function ($role) {
                return new Role($role['id'], $role['context_id'], $role['name'], $role['description']);
            }, $roles);
        });
    }

    public function create(Role $role): void
    {
        $tableName = $this->tableNames->roles();

        $this->runRepositoryOperation('create role', $tableName, function () use ($role, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('INSERT INTO %s (id, context_id, name, description) VALUES (:id, :context_id, :name, :description)', $tableName));
            $params = [
                ':id' => $role->id,
                ':context_id' => $role->contextId,
                ':name' => $role->name,
                ':description' => $role->description,
            ];
            $statement->execute($params);
        });
    }

    public function update(Role $role): void
    {
        $tableName = $this->tableNames->roles();

        $this->runRepositoryOperation('update role', $tableName, function () use ($role, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('UPDATE %s SET name = :name, description = :description WHERE id = :id', $tableName));
            $params = [
                ':id' => $role->id,
                ':name' => $role->name,
                ':description' => $role->description,
            ];
            $statement->execute($params);
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
