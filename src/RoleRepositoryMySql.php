<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use Exception;
use PDO;

final class RoleRepositoryMySql implements RoleRepositoryInterface
{
    use HandlesMySqlRepositoryExceptions;

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @throws Exception
     */
    public function findById(string $id): ?Role
    {
        return $this->runRepositoryOperation('find role by id', '_jomisacu_roles', function () use ($id): ?Role {
            $statement = $this->pdo->prepare('SELECT * FROM _jomisacu_roles WHERE id = :id');
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
        return $this->runRepositoryOperation('find roles by context', '_jomisacu_roles', function () use ($contextId): array {
            $statement = $this->pdo->prepare('SELECT * FROM _jomisacu_roles WHERE context_id = :context_id');
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
        $this->runRepositoryOperation('create role', '_jomisacu_roles', function () use ($role): void {
            $statement = $this->pdo->prepare('INSERT INTO _jomisacu_roles (id, context_id, name, description) VALUES (:id, :context_id, :name, :description)');
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
        $this->runRepositoryOperation('update role', '_jomisacu_roles', function () use ($role): void {
            $statement = $this->pdo->prepare('UPDATE _jomisacu_roles SET name = :name, description = :description WHERE id = :id');
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
        $this->runRepositoryOperation('delete role', '_jomisacu_roles', function () use ($role): void {
            $statement = $this->pdo->prepare('DELETE FROM _jomisacu_roles WHERE id = :id');
            $statement->bindValue(':id', $role->id);
            $statement->execute();
        });
    }
}
