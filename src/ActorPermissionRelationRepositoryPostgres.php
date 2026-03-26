<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

use PDO;

final class ActorPermissionRelationRepositoryPostgres implements ActorPermissionRelationRepositoryInterface
{
    use HandlesPostgresRepositoryExceptions;

    private readonly PostgresTableNames $tableNames;

    public function __construct(private readonly PDO $pdo, ?PostgresTableNames $tableNames = null)
    {
        $this->tableNames = $tableNames ?? PostgresTableNames::default();
    }

    public function findByContextAndActor(string $contextId, string $actorId): array
    {
        $tableName = $this->tableNames->actorPermissionRelations();

        return $this->runRepositoryOperation('find actor permissions', $tableName, function () use ($contextId, $actorId, $tableName): array {
            $statement = $this->pdo->prepare(sprintf('SELECT context_id, actor_id, permission_id, resource, negated, created_by_user_id, created_at, updated_by_user_id, updated_at FROM %s WHERE context_id = :contextId AND actor_id = :actorId', $tableName));
            $statement->execute([
                ':contextId' => $contextId,
                ':actorId' => $actorId,
            ]);

            return array_map(
                static fn (array $row): ActorPermissionRelation => new ActorPermissionRelation(
                    contextId: $row['context_id'],
                    actorId: $row['actor_id'],
                    permissionId: $row['permission_id'],
                    resource: $row['resource'],
                    negated: PostgresValueCaster::toBool($row['negated']),
                    createdByUserId: $row['created_by_user_id'],
                    createdAt: new \DateTimeImmutable($row['created_at']),
                    updatedByUserId: $row['updated_by_user_id'],
                    updatedAt: isset($row['updated_at']) ? new \DateTimeImmutable($row['updated_at']) : null,
                ),
                $statement->fetchAll(PDO::FETCH_ASSOC),
            );
        });
    }

    public function create(ActorPermissionRelation $actorPermissionRelation): void
    {
        $tableName = $this->tableNames->actorPermissionRelations();

        $this->runRepositoryOperation('create actor permission relation', $tableName, function () use ($actorPermissionRelation, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('INSERT INTO %s (context_id, actor_id, permission_id, resource, resource_hash, negated, created_by_user_id, created_at) VALUES (:contextId, :actorId, :permissionId, :resource, :resourceHash, :negated, :createdByUserId, :createdAt)', $tableName));
            $statement->execute([
                'contextId' => $actorPermissionRelation->contextId,
                'actorId' => $actorPermissionRelation->actorId,
                'permissionId' => $actorPermissionRelation->permissionId,
                'resource' => $actorPermissionRelation->resource,
                'resourceHash' => hash('sha256', $actorPermissionRelation->resource),
                'negated' => $actorPermissionRelation->negated ? 'true' : 'false',
                'createdByUserId' => $actorPermissionRelation->createdByUserId,
                'createdAt' => $actorPermissionRelation->createdAt->format('Y-m-d H:i:s'),
            ]);
        });
    }

    public function delete(ActorPermissionRelation $actorPermissionRelation): void
    {
        $tableName = $this->tableNames->actorPermissionRelations();

        $this->runRepositoryOperation('delete actor permission relation', $tableName, function () use ($actorPermissionRelation, $tableName): void {
            $statement = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE context_id = :contextId AND actor_id = :actorId AND permission_id = :permissionId AND resource = :resource AND negated = :negated', $tableName));
            $statement->execute([
                'contextId' => $actorPermissionRelation->contextId,
                'actorId' => $actorPermissionRelation->actorId,
                'permissionId' => $actorPermissionRelation->permissionId,
                'resource' => $actorPermissionRelation->resource,
                'negated' => $actorPermissionRelation->negated ? 'true' : 'false',
            ]);
        });
    }
}
