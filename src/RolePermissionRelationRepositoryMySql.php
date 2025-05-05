<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use DateTimeImmutable;
use PDO;

final class RolePermissionRelationRepositoryMySql implements RolePermissionRelationRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function findByContextAndRole(string $contextId, string $roleId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM _jomisacu_role_permission_relations 
            WHERE context_id = :context_id 
            AND role_id = :role_id
        ');

        $stmt->execute([':context_id' => $contextId, ':role_id' => $roleId,]);

        return $this->parseRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function parseRows(array $rows): array
    {
        return array_map(function ($row) {
            return new RolePermissionRelation(
                contextId:       $row['context_id'],
                roleId:          $row['role_id'],
                permissionId:    $row['permission_id'],
                resource:        $row['resource'],
                negated:         (bool)$row['negated'],
                createdByUserId: $row['created_by_user_id'],
                createdAt:       new DateTimeImmutable($row['created_at']),
                updatedByUserId: $row['updated_by_user_id'],
                updatedAt:       isset($row['updated_at']) ? new DateTimeImmutable($row['updated_at']) : null,
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

        $placeholders = str_repeat('?,', count($roleIds) - 1) . '?';
        $stmt = $this->pdo->prepare("
            SELECT * FROM _jomisacu_role_permission_relations 
            WHERE context_id = ? 
            AND role_id IN ($placeholders)
        ");

        $stmt->execute([$contextId, ...$roleIds]);

        return $this->parseRows($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(RolePermissionRelation $rolePermissionRelation): void
    {
        $statement = $this->pdo->prepare('
            INSERT INTO _jomisacu_role_permission_relations (context_id, role_id, permission_id, resource, negated, created_by_user_id, created_at)
            VALUES (:contextId, :roleId, :permissionId, :resource, :negated, :createdByUserId, :createdAt)
        ');
        $statement->execute([
            'contextId' => $rolePermissionRelation->contextId,
            'roleId' => $rolePermissionRelation->roleId,
            'permissionId' => $rolePermissionRelation->permissionId,
            'resource' => $rolePermissionRelation->resource,
            'negated' => $rolePermissionRelation->negated ? 1 : 0,
            'createdByUserId' => $rolePermissionRelation->createdByUserId,
            'createdAt' => $rolePermissionRelation->createdAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(RolePermissionRelation $rolePermissionRelation): void
    {
        $statement = $this->pdo->prepare('
            DELETE FROM _jomisacu_role_permission_relations
            WHERE context_id = :contextId AND role_id = :roleId AND permission_id = :permissionId
        ');
        $statement->execute([
            'contextId' => $rolePermissionRelation->contextId,
            'roleId' => $rolePermissionRelation->roleId,
            'permissionId' => $rolePermissionRelation->permissionId,
        ]);
    }
}
