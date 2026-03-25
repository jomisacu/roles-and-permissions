<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\ActorPermissionRelation;
use Jomisacu\RolesAndPermissions\ActorPermissionRelationRepositoryPostgres;
use Jomisacu\RolesAndPermissions\ActorRoleRelation;
use Jomisacu\RolesAndPermissions\ActorRoleRelationRepositoryPostgres;
use Jomisacu\RolesAndPermissions\Permission;
use Jomisacu\RolesAndPermissions\PermissionRepositoryPostgres;
use Jomisacu\RolesAndPermissions\PostgresTableNames;
use Jomisacu\RolesAndPermissions\Role;
use Jomisacu\RolesAndPermissions\RolePermissionRelation;
use Jomisacu\RolesAndPermissions\RolePermissionRelationRepositoryPostgres;
use Jomisacu\RolesAndPermissions\RoleRepositoryPostgres;
use PDO;

final class PostgresCustomTableNamesTest extends PostgresIntegrationTestCase
{
    private const TABLE_PREFIX = 'custom_jomisacu_pg_';
    private const CONTEXT_ID = '8c8a59f7-8dc5-4d6c-ab76-d2713c8569ee';
    private const ACTOR_ID = '9b7e3fcf-ed3f-44cf-b850-ab852213558c';
    private const ROLE_ID = '53b9e1c0-7562-4aa7-b3c8-20ef5706a614';
    private const PERMISSION_ID = '6f7428f9-b53c-4c04-a56f-a00d07493e0a';

    private PDO $pdo;
    private PostgresTableNames $tableNames;

    protected function setUp(): void
    {
        $this->pdo = $this->createPdoConnection();
        $this->tableNames = PostgresTableNames::fromPrefix(self::TABLE_PREFIX);

        $this->ensurePostgresSchema($this->pdo, self::TABLE_PREFIX);
        $this->truncatePostgresSchema($this->pdo, self::TABLE_PREFIX);
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo)) {
            $this->truncatePostgresSchema($this->pdo, self::TABLE_PREFIX);
        }
    }

    public function testRepositoriesUseConfiguredTablePrefix(): void
    {
        $permissionRepository = new PermissionRepositoryPostgres($this->pdo, $this->tableNames);
        $roleRepository = new RoleRepositoryPostgres($this->pdo, $this->tableNames);
        $actorRoleRepository = new ActorRoleRelationRepositoryPostgres($this->pdo, $this->tableNames);
        $actorPermissionRepository = new ActorPermissionRelationRepositoryPostgres($this->pdo, $this->tableNames);
        $rolePermissionRepository = new RolePermissionRelationRepositoryPostgres($this->pdo, $this->tableNames);

        $permission = new Permission(self::PERMISSION_ID, self::CONTEXT_ID, 'Publish posts', 'Allows publishing posts');
        $role = new Role(self::ROLE_ID, self::CONTEXT_ID, 'Editor', 'Can publish content');
        $actorRoleRelation = new ActorRoleRelation(self::CONTEXT_ID, self::ACTOR_ID, self::ROLE_ID, null, new \DateTimeImmutable(), null, null);
        $actorPermissionRelation = new ActorPermissionRelation(self::CONTEXT_ID, self::ACTOR_ID, self::PERMISSION_ID, 'blog::post::*', false, null, new \DateTimeImmutable(), null, null);
        $rolePermissionRelation = new RolePermissionRelation(self::CONTEXT_ID, self::ROLE_ID, self::PERMISSION_ID, 'blog::post::*', false, null, new \DateTimeImmutable(), null, null);

        $permissionRepository->create($permission);
        $roleRepository->create($role);
        $actorRoleRepository->create($actorRoleRelation);
        $actorPermissionRepository->create($actorPermissionRelation);
        $rolePermissionRepository->create($rolePermissionRelation);

        $this->assertCount(1, $permissionRepository->findByContextId(self::CONTEXT_ID));
        $this->assertNotNull($roleRepository->findById(self::ROLE_ID));
        $this->assertCount(1, $roleRepository->findByContextId(self::CONTEXT_ID));
        $this->assertCount(1, $actorRoleRepository->findActorRoles(self::CONTEXT_ID, self::ACTOR_ID));
        $this->assertCount(1, $actorPermissionRepository->findByContextAndActor(self::CONTEXT_ID, self::ACTOR_ID));
        $this->assertCount(1, $rolePermissionRepository->findByContextAndRole(self::CONTEXT_ID, self::ROLE_ID));
        $this->assertCount(1, $rolePermissionRepository->findByContextAndRoles(self::CONTEXT_ID, [self::ROLE_ID]));
    }
}
