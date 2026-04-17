<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class ActorPermissionRelationRepositoryMySql implements ActorPermissionRelationRepositoryInterface
{
    use HandlesMySqlRepositoryExceptions;

    private \PDO $pdo;
    private MySqlTableNames $tableNames;

    public function __construct(
        \PDO $pdo,
        ?MySqlTableNames $tableNames = null
    ) {
        $this->pdo = $pdo;
        $this->tableNames = $tableNames ?? MySqlTableNames::default();
    }

    /**
     * @inheritDoc
     */
    public function findByContextAndActor(string $contextId, string $actorId): array
    {
        $tableName = $this->tableNames->actorPermissionRelations();

        return $this->runRepositoryOperation('find actor permissions', $tableName, function () use ($contextId, $actorId, $tableName): array {
            $stmt = $this->pdo->prepare(sprintf(
                'SELECT context_id, actor_id, permission_id, resource, negated, created_by_user_id, created_at, updated_by_user_id, updated_at FROM %s WHERE context_id = :contextId AND actor_id = :actorId',
                $tableName,
            ));

            $stmt->execute([
                ':contextId' => $contextId,
                ':actorId' => $actorId,
            ]);

            return array_map(
                static fn(array $row) => new ActorPermissionRelation(
                    $row['context_id'],
                    $row['actor_id'],
                    $row['permission_id'],
                    $row['resource'],
                    (bool) $row['negated'],
                    $row['created_by_user_id'],
                    new \DateTimeImmutable($row['created_at']),
                    $row['updated_by_user_id'],
                    isset($row['updated_at']) ? new \DateTimeImmutable($row['updated_at']) : null
                ),
                $stmt->fetchAll(\PDO::FETCH_ASSOC),
            );
        });
    }

    public function create(ActorPermissionRelation $actorPermissionRelation): void
    {
        $tableName = $this->tableNames->actorPermissionRelations();

        $this->runRepositoryOperation('create actor permission relation', $tableName, function () use ($actorPermissionRelation, $tableName): void {
            $statement = $this->pdo->prepare(sprintf(
                'INSERT INTO %s (context_id, actor_id, permission_id, resource, resource_hash, negated, created_by_user_id, created_at) VALUES (:contextId, :actorId, :permissionId, :resource, :resourceHash, :negated, :createdByUserId, :createdAt)',
                $tableName,
            ));
            $statement->execute([
                'contextId' => $actorPermissionRelation->contextId,
                'actorId' => $actorPermissionRelation->actorId,
                'permissionId' => $actorPermissionRelation->permissionId,
                'resource' => $actorPermissionRelation->resource,
                'resourceHash' => hash('sha256', $actorPermissionRelation->resource),
                'negated' => $actorPermissionRelation->negated ? 1 : 0,
                'createdByUserId' => $actorPermissionRelation->createdByUserId,
                'createdAt' => $actorPermissionRelation->createdAt->format('Y-m-d H:i:s'),
            ]);
        });
    }

    public function delete(ActorPermissionRelation $actorPermissionRelation): void
    {
        $tableName = $this->tableNames->actorPermissionRelations();

        $this->runRepositoryOperation('delete actor permission relation', $tableName, function () use ($actorPermissionRelation, $tableName): void {
            $statement = $this->pdo->prepare(sprintf(
                'DELETE FROM %s WHERE context_id = :contextId AND actor_id = :actorId AND permission_id = :permissionId AND resource = :resource AND negated = :negated',
                $tableName,
            ));
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
