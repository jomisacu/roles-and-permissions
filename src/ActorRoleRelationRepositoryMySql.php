<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use Jomisacu\RolesAndPermissions\ActorRoleRelationRepositoryInterface;

final class ActorRoleRelationRepositoryMySql implements ActorRoleRelationRepositoryInterface
{
    public function __construct(
        private readonly \PDO $pdo,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function findActorRoles(string $contextId, string $actorId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT *
            FROM _jomisacu_actor_role_relations
            WHERE context_id = :contextId AND actor_id = :actorId
        ');

        $stmt->execute([
            ':contextId' => $contextId,
            ':actorId' => $actorId,
        ]);

        return array_map(
            static fn(array $row) => new ActorRoleRelation(
                contextId: $row['context_id'],
                actorId: $row['actor_id'],
                roleId: $row['role_id'],
                createdByUserId: $row['created_by_user_id'],
                createdAt: new \DateTimeImmutable($row['created_at']),
                updatedByUserId: $row['updated_by_user_id'],
                updatedAt: isset($row['updated_at']) ? new \DateTimeImmutable($row['updated_at']) : null,
            ),
            $stmt->fetchAll(\PDO::FETCH_ASSOC),
        );
    }

    public function create(ActorRoleRelation $actorRoleRelation): void
    {
        $statement = $this->pdo->prepare('
            INSERT INTO _jomisacu_actor_role_relations (context_id, actor_id, role_id, created_by_user_id, created_at)
            VALUES (:contextId, :actorId, :roleId, :createdByUserId, :createdAt)
        ');
        $statement->execute([
            'contextId' => $actorRoleRelation->contextId,
            'actorId' => $actorRoleRelation->actorId,
            'roleId' => $actorRoleRelation->roleId,
            'createdByUserId' => $actorRoleRelation->createdByUserId,
            'createdAt' => $actorRoleRelation->createdAt,
        ]);
    }

    public function delete(ActorRoleRelation $actorRoleRelation): void
    {
        $statement = $this->pdo->prepare('DELETE FROM _jomisacu_actor_role_relations WHERE context_id = :contextId AND actor_id = :actorId AND role_id = :roleId');
        $statement->execute([
            'contextId' => $actorRoleRelation->contextId,
            'actorId' => $actorRoleRelation->actorId,
            'roleId' => $actorRoleRelation->roleId,
        ]);
    }
}
