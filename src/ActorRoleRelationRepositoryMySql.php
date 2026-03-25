<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use Jomisacu\RolesAndPermissions\ActorRoleRelationRepositoryInterface;

final class ActorRoleRelationRepositoryMySql implements ActorRoleRelationRepositoryInterface
{
    use HandlesMySqlRepositoryExceptions;

    private readonly MySqlTableNames $tableNames;

    public function __construct(
        private readonly \PDO $pdo,
        ?MySqlTableNames $tableNames = null,
    ) {
        $this->tableNames = $tableNames ?? MySqlTableNames::default();
    }

    /**
     * @inheritDoc
     */
    public function findActorRoles(string $contextId, string $actorId): array
    {
        $tableName = $this->tableNames->actorRoleRelations();

        return $this->runRepositoryOperation('find actor roles', $tableName, function () use ($contextId, $actorId, $tableName): array {
            $stmt = $this->pdo->prepare(sprintf(
                'SELECT * FROM %s WHERE context_id = :contextId AND actor_id = :actorId',
                $tableName,
            ));

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
        });
    }

    public function create(ActorRoleRelation $actorRoleRelation): void
    {
        $tableName = $this->tableNames->actorRoleRelations();

        $this->runRepositoryOperation('create actor role relation', $tableName, function () use ($actorRoleRelation, $tableName): void {
            $statement = $this->pdo->prepare(sprintf(
                'INSERT INTO %s (context_id, actor_id, role_id, created_by_user_id, created_at) VALUES (:contextId, :actorId, :roleId, :createdByUserId, :createdAt)',
                $tableName,
            ));
            $statement->execute([
                'contextId' => $actorRoleRelation->contextId,
                'actorId' => $actorRoleRelation->actorId,
                'roleId' => $actorRoleRelation->roleId,
                'createdByUserId' => $actorRoleRelation->createdByUserId,
                'createdAt' => $actorRoleRelation->createdAt->format('Y-m-d H:i:s'),
            ]);
        });
    }

    public function delete(ActorRoleRelation $actorRoleRelation): void
    {
        $tableName = $this->tableNames->actorRoleRelations();

        $this->runRepositoryOperation('delete actor role relation', $tableName, function () use ($actorRoleRelation, $tableName): void {
            $statement = $this->pdo->prepare(sprintf(
                'DELETE FROM %s WHERE context_id = :contextId AND actor_id = :actorId AND role_id = :roleId',
                $tableName,
            ));
            $statement->execute([
                'contextId' => $actorRoleRelation->contextId,
                'actorId' => $actorRoleRelation->actorId,
                'roleId' => $actorRoleRelation->roleId,
            ]);
        });
    }
}
