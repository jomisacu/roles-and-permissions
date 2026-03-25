<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class ActorPermissionRelationRepositoryMySql implements ActorPermissionRelationRepositoryInterface
{
    use HandlesMySqlRepositoryExceptions;

    public function __construct(
        private readonly \PDO $pdo,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function findByContextAndActor(string $contextId, string $actorId): array
    {
        return $this->runRepositoryOperation('find actor permissions', '_jomisacu_actor_permission_relations', function () use ($contextId, $actorId): array {
            $stmt = $this->pdo->prepare('
                SELECT context_id, actor_id, permission_id, resource, negated, created_by_user_id, created_at, updated_by_user_id, updated_at
                FROM _jomisacu_actor_permission_relations
                WHERE context_id = :contextId AND actor_id = :actorId
            ');

            $stmt->execute([
                ':contextId' => $contextId,
                ':actorId' => $actorId,
            ]);

            return array_map(
                static fn(array $row) => new ActorPermissionRelation(
                    contextId: $row['context_id'],
                    actorId: $row['actor_id'],
                    permissionId: $row['permission_id'],
                    resource: $row['resource'],
                    negated: (bool)$row['negated'],
                    createdByUserId: $row['created_by_user_id'],
                    createdAt: new \DateTimeImmutable($row['created_at']),
                    updatedByUserId: $row['updated_by_user_id'],
                    updatedAt: isset($row['updated_at']) ? new \DateTimeImmutable($row['updated_at']) : null,
                ),
                $stmt->fetchAll(\PDO::FETCH_ASSOC),
            );
        });
    }

    public function create(ActorPermissionRelation $actorPermissionRelation): void
    {
        $this->runRepositoryOperation('create actor permission relation', '_jomisacu_actor_permission_relations', function () use ($actorPermissionRelation): void {
            $statement = $this->pdo->prepare('
                INSERT INTO _jomisacu_actor_permission_relations (context_id, actor_id, permission_id, resource, negated, created_by_user_id, created_at)
                VALUES (:contextId, :actorId, :permissionId, :resource, :negated, :createdByUserId, :createdAt)
            ');
            $statement->execute([
                'contextId' => $actorPermissionRelation->contextId,
                'actorId' => $actorPermissionRelation->actorId,
                'permissionId' => $actorPermissionRelation->permissionId,
                'resource' => $actorPermissionRelation->resource,
                'negated' => $actorPermissionRelation->negated ? 1 : 0,
                'createdByUserId' => $actorPermissionRelation->createdByUserId,
                'createdAt' => $actorPermissionRelation->createdAt->format('Y-m-d H:i:s'),
            ]);
        });
    }

    public function delete(ActorPermissionRelation $actorPermissionRelation): void
    {
        $this->runRepositoryOperation('delete actor permission relation', '_jomisacu_actor_permission_relations', function () use ($actorPermissionRelation): void {
            $statement = $this->pdo->prepare('
                DELETE FROM _jomisacu_actor_permission_relations
                WHERE context_id = :contextId
                  AND actor_id = :actorId
                  AND permission_id = :permissionId
                  AND resource = :resource
                  AND negated = :negated
            ');
            $statement->execute([
                'contextId' => $actorPermissionRelation->contextId,
                'actorId' => $actorPermissionRelation->actorId,
                'permissionId' => $actorPermissionRelation->permissionId,
                'resource' => $actorPermissionRelation->resource,
                'negated' => $actorPermissionRelation->negated ? 1 : 0,
            ]);
        });
    }
}
