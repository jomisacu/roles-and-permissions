<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\ActorPermissionRelation;
use Jomisacu\RolesAndPermissions\ActorPermissionRelationRepositoryMySql;
use Jomisacu\RolesAndPermissions\ActorRoleRelation;
use Jomisacu\RolesAndPermissions\ActorRoleRelationRepositoryMySql;
use Jomisacu\RolesAndPermissions\MySqlTableNames;
use Jomisacu\RolesAndPermissions\Permission;
use Jomisacu\RolesAndPermissions\PermissionRepositoryMySql;
use Jomisacu\RolesAndPermissions\Role;
use Jomisacu\RolesAndPermissions\RolePermissionRelation;
use Jomisacu\RolesAndPermissions\RolePermissionRelationRepositoryMySql;
use Jomisacu\RolesAndPermissions\RoleRepositoryMySql;
use PDO;

final class MySqlCustomTableNamesTest extends MySqlIntegrationTestCase
{
    private const TABLE_PREFIX = 'custom_jomisacu_';
    private const CONTEXT_ID = '53139226-1c4d-4a8f-8b62-6976c4d8d86a';
    private const ACTOR_ID = '09bcae4f-b68c-46ba-abf6-915fec4bb961';
    private const ROLE_ID = '5e3c3b77-6b06-4928-8e6e-ab5dad35a28b';
    private const PERMISSION_ID = '3a0e47f3-24f8-451d-8474-04f0d1efc251';

    private PDO $pdo;
    private MySqlTableNames $tableNames;

    protected function setUp(): void
    {
        $this->pdo = $this->createPdoConnection();
        $this->tableNames = MySqlTableNames::fromPrefix(self::TABLE_PREFIX);

        $this->ensureMySqlSchema($this->pdo, self::TABLE_PREFIX);
        $this->truncateMySqlSchema($this->pdo, self::TABLE_PREFIX);
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo)) {
            $this->truncateMySqlSchema($this->pdo, self::TABLE_PREFIX);
        }
    }

    public function testRepositoriesUseConfiguredTablePrefix(): void
    {
        $permissionRepository = new PermissionRepositoryMySql($this->pdo, $this->tableNames);
        $roleRepository = new RoleRepositoryMySql($this->pdo, $this->tableNames);
        $actorRoleRepository = new ActorRoleRelationRepositoryMySql($this->pdo, $this->tableNames);
        $actorPermissionRepository = new ActorPermissionRelationRepositoryMySql($this->pdo, $this->tableNames);
        $rolePermissionRepository = new RolePermissionRelationRepositoryMySql($this->pdo, $this->tableNames);

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
