<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\Role;
use Jomisacu\RolesAndPermissions\RoleRepositoryMySql;
use PDO;

class RoleRepositoryMySqlTest extends MySqlIntegrationTestCase
{
    const ROLE_ID = 'b8292971-8fea-428f-9a6a-a266c11509f9';
    const CONTEXT_ID = '3b7d8190-3720-4ca0-8a38-0b36c64735a5';
    const NON_EXISTING_ROLE_ID = '51082523-42c4-4b7e-97a8-178f37faecf8';
    protected RoleRepositoryMySql $roleRepository;
    protected PDO $pdo;
    
    protected function setUp(): void
    {
        $this->pdo = $this->createPdoConnection();
        $this->ensureMySqlSchema($this->pdo, '_jomisacu_');
        $this->roleRepository = new RoleRepositoryMySql($this->pdo);
    }

    public function testFindById()
    {
        $id = self::ROLE_ID;
        $contextId = self::CONTEXT_ID;
        $name = 'Test Role';
        $description = 'Test Role Description';

        $role = new Role($id, $contextId, $name, $description);
        $this->roleRepository->create($role);

        $foundRole = $this->roleRepository->findById($id);
        $this->assertNotNull($foundRole);
        $this->assertEquals($id, $foundRole->id);
        $this->assertEquals($contextId, $foundRole->contextId);
        $this->assertEquals($name, $foundRole->name);
        $this->assertEquals($description, $foundRole->description);

        $nonExistingRole = $this->roleRepository->findById(self::NON_EXISTING_ROLE_ID);
        $this->assertNull($nonExistingRole);

        $this->roleRepository->delete($role);
    }
    
    public function testFindByContextId()
    {
        $id = self::ROLE_ID;
        $contextId = self::CONTEXT_ID;
        $name = 'Test Role';
        $description = 'Test Role Description';

        $role = new Role($id, $contextId, $name, $description);
        $this->roleRepository->create($role);

        $roles = $this->roleRepository->findByContextId($contextId);
        $this->assertCount(1, $roles);
        $this->assertEquals($id, $roles[0]->id);
        $this->assertEquals($contextId, $roles[0]->contextId);
        $this->assertEquals($name, $roles[0]->name);
        $this->assertEquals($description, $roles[0]->description);

        $rolesNonExisting = $this->roleRepository->findByContextId(self::NON_EXISTING_ROLE_ID);
        $this->assertEmpty($rolesNonExisting);

        $this->roleRepository->delete($role);
    }
    
    public function testUpdate()
    {
        $id = self::ROLE_ID;
        $contextId = self::CONTEXT_ID;
        $name = 'Test Role';
        $description = 'Test Role Description';

        $role = new Role($id, $contextId, $name, $description);
        $this->roleRepository->create($role);

        $updatedName = 'Updated Test Role';
        $updatedDescription = 'Updated Test Role Description';
        $role = new Role($id, $contextId, $updatedName, $updatedDescription);
        $this->roleRepository->update($role);

        $foundRole = $this->roleRepository->findById($id);
        $this->assertNotNull($foundRole);
        $this->assertEquals($id, $foundRole->id);
        $this->assertEquals($contextId, $foundRole->contextId);
        $this->assertEquals($updatedName, $foundRole->name);
        $this->assertEquals($updatedDescription, $foundRole->description);

        $this->roleRepository->delete($role);
    }
}
