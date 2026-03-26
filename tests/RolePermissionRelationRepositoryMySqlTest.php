<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\Permission;
use Jomisacu\RolesAndPermissions\PermissionRepositoryMySql;
use Jomisacu\RolesAndPermissions\Role;
use Jomisacu\RolesAndPermissions\RolePermissionRelation;
use Jomisacu\RolesAndPermissions\RolePermissionRelationRepositoryMySql;
use Jomisacu\RolesAndPermissions\RoleRepositoryMySql;
use PDO;
use PHPUnit\Framework\Attributes\Depends;

class RolePermissionRelationRepositoryMySqlTest extends MySqlIntegrationTestCase
{
    const ROLE_ID = 'b8292971-8fea-428f-9a6a-a266c11509f9';
    const PERMISSION_ID = '3b7d8190-3720-4ca0-8a38-0b36c64735a5';
    const NON_EXISTING_ROLE_ID = '51082523-42c4-4b7e-97a8-178f37faecf8';
    const CONTEXT_ID = 'af881762-cabf-4cd1-8277-f2f88f7aba1c';

    protected RolePermissionRelationRepositoryMySql $rolePermissionRelationRepository;
    protected PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->createPdoConnection();
        $this->ensureMySqlSchema($this->pdo, '_jomisacu_');
        $this->rolePermissionRelationRepository = new RolePermissionRelationRepositoryMySql($this->pdo);

        $permissionRepository = new PermissionRepositoryMySql($this->pdo);
        $permissionRepository->create(new Permission(self::PERMISSION_ID, self::CONTEXT_ID, 'Test Permission', 'Test Permission Description'));

        $roleRepository = new RoleRepositoryMySql($this->pdo);
        $roleRepository->create(new Role(self::ROLE_ID, self::CONTEXT_ID, 'Test Role', 'Test Role Description'));
    }

    protected function tearDown(): void
    {
        if (!isset($this->pdo)) {
            return;
        }

        $permissionRepository = new PermissionRepositoryMySql($this->pdo);
        $permissionRepository->delete(new Permission(self::PERMISSION_ID, self::CONTEXT_ID, 'Test Permission', 'Test Permission Description'));

        $roleRepository = new RoleRepositoryMySql($this->pdo);
        $roleRepository->delete(new Role(self::ROLE_ID, self::CONTEXT_ID, 'Test Role', 'Test Role Description'));
    }

    public function testCreate(): void
    {
        $rolePermissionRelation = new RolePermissionRelation(
            self::CONTEXT_ID,
            self::ROLE_ID,
            self::PERMISSION_ID,
            'resource',
            false,
            null,
            new \DateTimeImmutable(),
            null,
            null
        );
        $this->rolePermissionRelationRepository->create($rolePermissionRelation);

        $statement = $this->pdo->prepare('SELECT * FROM _jomisacu_role_permission_relations WHERE role_id = :role_id AND permission_id = :permission_id');
        $params = [
            'role_id' => self::ROLE_ID,
            'permission_id' => self::PERMISSION_ID,
        ];
        $statement->execute($params);

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($result);

        $this->rolePermissionRelationRepository->delete($rolePermissionRelation);
    }

    #[Depends('testCreate')]
    public function testDelete(): void
    {
        $rolePermissionRelation = new RolePermissionRelation(
            self::CONTEXT_ID,
            self::ROLE_ID,
            self::PERMISSION_ID,
            'resource',
            false,
            null,
            new \DateTimeImmutable(),
            null,
            null
        );
        $this->rolePermissionRelationRepository->create($rolePermissionRelation);

        $this->rolePermissionRelationRepository->delete($rolePermissionRelation);

        $statement = $this->pdo->prepare('SELECT * FROM _jomisacu_role_permission_relations WHERE role_id = :role_id AND permission_id = :permission_id');
        $params = [
            'role_id' => self::ROLE_ID,
            'permission_id' => self::PERMISSION_ID,
        ];
        $statement->execute($params);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $this->assertFalse($result);
    }

    public function testFindByContextAndRole()
    {
        $rolePermissionRelation = new RolePermissionRelation(
            self::CONTEXT_ID,
            self::ROLE_ID,
            self::PERMISSION_ID,
            'resource',
            false,
            null,
            new \DateTimeImmutable(),
            null,
            null
        );
        $this->rolePermissionRelationRepository->create($rolePermissionRelation);

        $rolePermissionRelations = $this->rolePermissionRelationRepository->findByContextAndRole(self::CONTEXT_ID, self::ROLE_ID);
        $this->assertCount(1, $rolePermissionRelations);
        $this->assertEquals(self::CONTEXT_ID, $rolePermissionRelations[0]->contextId);
        $this->assertEquals(self::ROLE_ID, $rolePermissionRelations[0]->roleId);
        $this->assertEquals(self::PERMISSION_ID, $rolePermissionRelations[0]->permissionId);
        $this->assertEquals('resource', $rolePermissionRelations[0]->resource);
    }

    public function testDeleteOnlyRemovesTheExactMatchingRule(): void
    {
        $firstRelation = new RolePermissionRelation(
            self::CONTEXT_ID,
            self::ROLE_ID,
            self::PERMISSION_ID,
            'resource-a',
            false,
            null,
            new \DateTimeImmutable(),
            null,
            null,
        );
        $secondRelation = new RolePermissionRelation(
            self::CONTEXT_ID,
            self::ROLE_ID,
            self::PERMISSION_ID,
            'resource-b',
            false,
            null,
            new \DateTimeImmutable(),
            null,
            null,
        );

        $this->rolePermissionRelationRepository->create($firstRelation);
        $this->rolePermissionRelationRepository->create($secondRelation);

        $this->rolePermissionRelationRepository->delete($firstRelation);

        $rolePermissionRelations = $this->rolePermissionRelationRepository->findByContextAndRole(self::CONTEXT_ID, self::ROLE_ID);

        $this->assertCount(1, $rolePermissionRelations);
        $this->assertEquals('resource-b', $rolePermissionRelations[0]->resource);
    }
}
