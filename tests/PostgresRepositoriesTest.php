<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\ActorPermissionRelationRepositoryPostgres;
use Jomisacu\RolesAndPermissions\PermissionRepositoryPostgres;
use Jomisacu\RolesAndPermissions\PostgresTableNames;
use Jomisacu\RolesAndPermissions\RolePermissionRelationRepositoryPostgres;
use Jomisacu\RolesAndPermissions\RoleRepositoryPostgres;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PostgresRepositoriesTest extends TestCase
{
    public function testPermissionRepositoryFindsPermissionsWithConfiguredTables(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects($this->once())
            ->method('execute')
            ->with(['context_id' => 'context-1']);
        $statement->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([
                [
                    'id' => 'permission-1',
                    'context_id' => 'context-1',
                    'name' => 'Publish',
                    'description' => 'Allows publishing',
                ],
            ]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM acme_permissions WHERE context_id = :context_id')
            ->willReturn($statement);

        $repository = new PermissionRepositoryPostgres($pdo, PostgresTableNames::fromPrefix('acme_'));
        $permissions = $repository->findByContextId('context-1');

        $this->assertCount(1, $permissions);
        $this->assertSame('permission-1', $permissions[0]->id);
        $this->assertSame('Publish', $permissions[0]->name);
    }

    public function testRoleRepositoryFindsRolesByIdWithConfiguredTables(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects($this->once())
            ->method('bindValue')
            ->with(':id', 'role-1');
        $statement->expects($this->once())
            ->method('execute');
        $statement->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([
                'id' => 'role-1',
                'context_id' => 'context-1',
                'name' => 'Editor',
                'description' => 'Can edit content',
            ]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM acme_roles WHERE id = :id')
            ->willReturn($statement);

        $repository = new RoleRepositoryPostgres($pdo, PostgresTableNames::fromPrefix('acme_'));
        $role = $repository->findById('role-1');

        $this->assertNotNull($role);
        $this->assertSame('Editor', $role->name);
    }

    public function testActorPermissionRepositoryHydratesBooleanValues(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects($this->once())
            ->method('execute')
            ->with([
                ':contextId' => 'context-1',
                ':actorId' => 'actor-1',
            ]);
        $statement->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([
                [
                    'context_id' => 'context-1',
                    'actor_id' => 'actor-1',
                    'permission_id' => 'permission-1',
                    'resource' => 'blog::post::*',
                    'negated' => 't',
                    'created_by_user_id' => null,
                    'created_at' => '2026-03-25 10:00:00',
                    'updated_by_user_id' => null,
                    'updated_at' => null,
                ],
            ]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT context_id, actor_id, permission_id, resource, negated, created_by_user_id, created_at, updated_by_user_id, updated_at FROM acme_actor_permission_relations WHERE context_id = :contextId AND actor_id = :actorId')
            ->willReturn($statement);

        $repository = new ActorPermissionRelationRepositoryPostgres($pdo, PostgresTableNames::fromPrefix('acme_'));
        $relations = $repository->findByContextAndActor('context-1', 'actor-1');

        $this->assertCount(1, $relations);
        $this->assertTrue($relations[0]->negated);
    }

    public function testRolePermissionRepositoryBuildsInQueryWithConfiguredTables(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects($this->once())
            ->method('execute')
            ->with(['context-1', 'role-1', 'role-2']);
        $statement->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([
                [
                    'context_id' => 'context-1',
                    'role_id' => 'role-1',
                    'permission_id' => 'permission-1',
                    'resource' => 'blog::post::*',
                    'negated' => false,
                    'created_by_user_id' => null,
                    'created_at' => '2026-03-25 10:00:00',
                    'updated_by_user_id' => null,
                    'updated_at' => null,
                ],
            ]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM acme_role_permission_relations WHERE context_id = ? AND role_id IN (?,?)')
            ->willReturn($statement);

        $repository = new RolePermissionRelationRepositoryPostgres($pdo, PostgresTableNames::fromPrefix('acme_'));
        $relations = $repository->findByContextAndRoles('context-1', ['role-1', 'role-2']);

        $this->assertCount(1, $relations);
        $this->assertFalse($relations[0]->negated);
    }
}
