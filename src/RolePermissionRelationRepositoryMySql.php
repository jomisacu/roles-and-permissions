<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use DateTimeImmutable;
use PDO;

final class RolePermissionRelationRepositoryMySql implements RolePermissionRelationRepositoryInterface
{
    use HandlesMySqlRepositoryExceptions;

    private readonly MySqlTableNames $tableNames;

    public function __construct(
        private readonly PDO $pdo,
        ?MySqlTableNames $tableNames = null,
    ) {
        $this->tableNames = $tableNames ?? MySqlTableNames::default();
    }

    /**
     * @inheritDoc
     */
    public function findByContextAndRole(string $contextId, string $roleId): array
    {
        $tableName = $this->tableNames->rolePermissionRelations();

        return $this->runRepositoryOperation('find role permissions', $tableName, function () use ($contextId, $roleId, $tableName): array {
            $stmt = $this->pdo->prepare(sprintf(
                'SELECT * FROM %s WHERE context_id = :context_id AND role_id = :role_id',
                $tableName,
            ));

            $stmt->execute([':context_id' => $contextId, ':role_id' => $roleId]);

            return $this->parseRows($stmt->fetchAll(PDO::FETCH_ASSOC));
        });
    }

    /**
     * @param array<array<string, mixed>> $rows
     * @return array<RolePermissionRelation>
     */
    private function parseRows(array $rows): array
    {
        return array_map(static function (array $row): RolePermissionRelation {
            return new RolePermissionRelation(
                contextId:       (string) $row['context_id'],
                roleId:          (string) $row['role_id'],
                permissionId:    (string) $row['permission_id'],
                resource:        (string) $row['resource'],
                negated:         (bool) $row['negated'],
                createdByUserId: isset($row['created_by_user_id']) ? (string) $row['created_by_user_id'] : null,
                createdAt:       new DateTimeImmutable((string) $row['created_at']),
                updatedByUserId: isset($row['updated_by_user_id']) ? (string) $row['updated_by_user_id'] : null,
                updatedAt:       isset($row['updated_at']) ? new DateTimeImmutable((string) $row['updated_at']) : null,
            );
        }, $rows);
    }

    /**
     * @inheritDoc
     */
    public function findByContextAndRoles(string $contextId, array $roleIds): array
    {
        if (empty($roleIds)) {
            return [];
        }

        $tableName = $this->tableNames->rolePermissionRelations();

        return $this->runRepositoryOperation('find role permissions by role list', $tableName, function () use ($contextId, $roleIds, $tableName): array {
            $placeholders = str_repeat('?,', count($roleIds) - 1) . '?';
            $stmt = $this->pdo->prepare(sprintf(
                'SELECT * FROM %s WHERE context_id = ? AND role_id IN (%s)',
                $tableName,
                $placeholders,
            ));

            $stmt->execute([$contextId, ...$roleIds]);

            return $this->parseRows($stmt->fetchAll(PDO::FETCH_ASSOC));
        });
    }

    public function create(RolePermissionRelation $rolePermissionRelation): void
    {
        $tableName = $this->tableNames->rolePermissionRelations();

        $this->runRepositoryOperation('create role permission relation', $tableName, function () use ($rolePermissionRelation, $tableName): void {
            $statement = $this->pdo->prepare(sprintf(
                'INSERT INTO %s (context_id, role_id, permission_id, resource, resource_hash, negated, created_by_user_id, created_at) VALUES (:contextId, :roleId, :permissionId, :resource, :resourceHash, :negated, :createdByUserId, :createdAt)',
                $tableName,
            ));
            $statement->execute([
                'contextId' => $rolePermissionRelation->contextId,
                'roleId' => $rolePermissionRelation->roleId,
                'permissionId' => $rolePermissionRelation->permissionId,
                'resource' => $rolePermissionRelation->resource,
                'resourceHash' => hash('sha256', $rolePermissionRelation->resource),
                'negated' => $rolePermissionRelation->negated ? 1 : 0,
                'createdByUserId' => $rolePermissionRelation->createdByUserId,
                'createdAt' => $rolePermissionRelation->createdAt->format('Y-m-d H:i:s'),
            ]);
        });
    }

    public function delete(RolePermissionRelation $rolePermissionRelation): void
    {
        $tableName = $this->tableNames->rolePermissionRelations();

        $this->runRepositoryOperation('delete role permission relation', $tableName, function () use ($rolePermissionRelation, $tableName): void {
            $statement = $this->pdo->prepare(sprintf(
                'DELETE FROM %s WHERE context_id = :contextId AND role_id = :roleId AND permission_id = :permissionId AND resource = :resource AND negated = :negated',
                $tableName,
            ));
            $statement->execute([
                'contextId' => $rolePermissionRelation->contextId,
                'roleId' => $rolePermissionRelation->roleId,
                'permissionId' => $rolePermissionRelation->permissionId,
                'resource' => $rolePermissionRelation->resource,
                'negated' => $rolePermissionRelation->negated ? 1 : 0,
            ]);
        });
    }
}
