<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use DateTimeImmutable;
use PDO;

final class RolePermissionRelationRepositoryPostgres implements RolePermissionRelationRepositoryInterface
{
    use HandlesPostgresRepositoryExceptions;

    private readonly PostgresTableNames $tableNames;

    public function __construct(private readonly PDO $pdo, ?PostgresTableNames $tableNames = null)
    {
        $this->tableNames = $tableNames ?? PostgresTableNames::default();
    }

    public function findByContextAndRole(string $contextId, string $roleId): array
    {
        $tableName = $this->tableNames->rolePermissionRelations();

        return $this->runRepositoryOperation('find role permissions', $tableName, function () use ($contextId, $roleId, $tableName): array {
            $statement = $this->pdo->prepare(sprintf('SELECT * FROM %s WHERE context_id = :context_id AND role_id = :role_id', $tableName));
            $statement->execute([':context_id' => $contextId, ':role_id' => $roleId]);

            return $this->parseRows($statement->fetchAll(PDO::FETCH_ASSOC));
        });
    }

    public function findByContextAndRoles(string $contextId, array $roleIds): array
    {
        if ($roleIds === []) {
            return [];
        }

        $tableName = $this->tableNames->rolePermissionRelations();

        return $this->runRepositoryOperation('find role permissions by role list', $tableName, function () use ($contextId, $roleIds, $tableName): array {
            $placeholders = str_repeat('?,', count($roleIds) - 1) . '?';
            $statement = $this->pdo->prepare(sprintf('SELECT * FROM %s WHERE context_id = ? AND role_id IN (%s)', $tableName, $placeholders));
            $statement->execute([$contextId, ...$roleIds]);

            return $this->parseRows($statement->fetchAll(PDO::FETCH_ASSOC));
        });
    }

    public function create(RolePermissionRelation $rolePermissionRelation): void
    {
        $tableName = $this->tableNames->rolePermissionRelations();

        $this->runRepositoryOperation('create role permission relation', $tableName, function () use ($rolePermissionRelation, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('INSERT INTO %s (context_id, role_id, permission_id, resource, resource_hash, negated, created_by_user_id, created_at) VALUES (:contextId, :roleId, :permissionId, :resource, :resourceHash, :negated, :createdByUserId, :createdAt)', $tableName));
            $statement->execute([
                'contextId' => $rolePermissionRelation->contextId,
                'roleId' => $rolePermissionRelation->roleId,
                'permissionId' => $rolePermissionRelation->permissionId,
                'resource' => $rolePermissionRelation->resource,
                'resourceHash' => hash('sha256', $rolePermissionRelation->resource),
                'negated' => $rolePermissionRelation->negated ? 'true' : 'false',
                'createdByUserId' => $rolePermissionRelation->createdByUserId,
                'createdAt' => $rolePermissionRelation->createdAt->format('Y-m-d H:i:s'),
            ]);
        });
    }

    public function delete(RolePermissionRelation $rolePermissionRelation): void
    {
        $tableName = $this->tableNames->rolePermissionRelations();

        $this->runRepositoryOperation('delete role permission relation', $tableName, function () use ($rolePermissionRelation, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE context_id = :contextId AND role_id = :roleId AND permission_id = :permissionId AND resource = :resource AND negated = :negated', $tableName));
            $statement->execute([
                'contextId' => $rolePermissionRelation->contextId,
                'roleId' => $rolePermissionRelation->roleId,
                'permissionId' => $rolePermissionRelation->permissionId,
                'resource' => $rolePermissionRelation->resource,
                'negated' => $rolePermissionRelation->negated ? 'true' : 'false',
            ]);
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
                contextId: $row['context_id'],
                roleId: $row['role_id'],
                permissionId: $row['permission_id'],
                resource: $row['resource'],
                negated: PostgresValueCaster::toBool($row['negated']),
                createdByUserId: $row['created_by_user_id'],
                createdAt: new DateTimeImmutable($row['created_at']),
                updatedByUserId: $row['updated_by_user_id'],
                updatedAt: isset($row['updated_at']) ? new DateTimeImmutable($row['updated_at']) : null,
            );
        }, $rows);
    }
}
