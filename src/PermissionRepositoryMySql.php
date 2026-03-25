<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use Jomisacu\RolesAndPermissions\PermissionRepositoryInterface;

final class PermissionRepositoryMySql implements PermissionRepositoryInterface
{
    use HandlesMySqlRepositoryExceptions;

    public function __construct(
        private readonly \PDO $pdo
    ) {
    }
    /**
     * @inheritDoc
     */
    public function findByContextId(string $contextId): array
    {
        return $this->runRepositoryOperation('find permissions by context', '_jomisacu_permissions', function () use ($contextId): array {
            $statement = $this->pdo->prepare('SELECT * FROM _jomisacu_permissions WHERE context_id = :context_id');
            $statement->execute(['context_id' => $contextId]);

            return array_map(fn($row) => new Permission($row['id'], $row['context_id'], $row['name'], $row['description']), $statement->fetchAll());
        });
    }

    public function create(Permission $permission): void
    {
        $this->runRepositoryOperation('create permission', '_jomisacu_permissions', function () use ($permission): void {
            $statement = $this->pdo->prepare('INSERT INTO _jomisacu_permissions (id, context_id, name, description) VALUES (:id, :context_id, :name, :description)');
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
        $this->runRepositoryOperation('update permission', '_jomisacu_permissions', function () use ($permission): void {
            $statement = $this->pdo->prepare('UPDATE _jomisacu_permissions SET name = :name, description = :description WHERE id = :id');
            $statement->execute([
                'id' => $permission->id,
                'name' => $permission->name,
                'description' => $permission->description,
            ]);
        });
    }

    public function delete(Permission $permission): void
    {
        $this->runRepositoryOperation('delete permission', '_jomisacu_permissions', function () use ($permission): void {
            $statement = $this->pdo->prepare('DELETE FROM _jomisacu_permissions WHERE id = :id');
            $statement->execute(['id' => $permission->id]);
        });
    }
}
