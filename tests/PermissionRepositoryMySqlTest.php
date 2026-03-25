<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\Permission;
use Jomisacu\RolesAndPermissions\PermissionRepositoryMySql;
use PDO;

class PermissionRepositoryMySqlTest extends MySqlIntegrationTestCase
{
    const PERMISSION_ID = 'b8292971-8fea-428f-9a6a-a266c11509f9';
    const CONTEXT_ID = '3b7d8190-3720-4ca0-8a38-0b36c64735a5';
    const NON_EXISTING_PERMISSION_ID = '51082523-42c4-4b7e-97a8-178f37faecf8';

    protected PermissionRepositoryMySql $permissionRepository;
    protected PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->createPdoConnection();
        $this->permissionRepository = new PermissionRepositoryMySql($this->pdo);
    }

    public function testFindByContextId()
    {
        $id = self::PERMISSION_ID;
        $contextId = self::CONTEXT_ID;
        $name = 'Test Permission';
        $description = 'Test Permission Description';

        $permission = new Permission($id, $contextId, $name, $description);
        $this->permissionRepository->create($permission);

        $permissions = $this->permissionRepository->findByContextId($contextId);
        $this->assertCount(1, $permissions);
        $this->assertEquals($id, $permissions[0]->id);
        $this->assertEquals($contextId, $permissions[0]->contextId);
        $this->assertEquals($name, $permissions[0]->name);
        $this->assertEquals($description, $permissions[0]->description);

        $permissionsNonExisting = $this->permissionRepository->findByContextId(self::NON_EXISTING_PERMISSION_ID);
        $this->assertEmpty($permissionsNonExisting);

        $this->permissionRepository->delete($permission);
    }

    public function testCreate()
    {
        $id = self::PERMISSION_ID;
        $contextId = self::CONTEXT_ID;
        $name = 'Test Permission';
        $description = 'Test Permission Description';

        $permission = new Permission($id, $contextId, $name, $description);
        $this->permissionRepository->create($permission);

        $permissions = $this->permissionRepository->findByContextId($contextId);
        $this->assertCount(1, $permissions);
        $this->assertEquals($id, $permissions[0]->id);
        $this->assertEquals($contextId, $permissions[0]->contextId);
        $this->assertEquals($name, $permissions[0]->name);
        $this->assertEquals($description, $permissions[0]->description);

        $this->permissionRepository->delete($permission);
    }

    public function testUpdate()
    {
        $id = self::PERMISSION_ID;
        $contextId = self::CONTEXT_ID;
        $name = 'Test Permission';
        $description = 'Test Permission Description';

        $permission = new Permission($id, $contextId, $name, $description);
        $this->permissionRepository->create($permission);

        $updatedName = 'Updated Test Permission';
        $updatedDescription = 'Updated Test Permission Description';
        $permission = new Permission($id, $contextId, $updatedName, $updatedDescription);
        $this->permissionRepository->update($permission);

        $permissions = $this->permissionRepository->findByContextId($contextId);
        $this->assertCount(1, $permissions);
        $this->assertEquals($id, $permissions[0]->id);
        $this->assertEquals($contextId, $permissions[0]->contextId);
        $this->assertEquals($updatedName, $permissions[0]->name);
        $this->assertEquals($updatedDescription, $permissions[0]->description);

        $this->permissionRepository->delete($permission);
    }

    public function testDelete()
    {
        $id = self::PERMISSION_ID;
        $contextId = self::CONTEXT_ID;
        $name = 'Test Permission';
        $description = 'Test Permission Description';

        $permission = new Permission($id, $contextId, $name, $description);
        $this->permissionRepository->create($permission);
        $this->permissionRepository->delete($permission);

        $permissions = $this->permissionRepository->findByContextId($contextId);
        $this->assertEmpty($permissions);
    }
}
