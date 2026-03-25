<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\Permission;
use Jomisacu\RolesAndPermissions\PermissionRepositoryPostgres;
use Jomisacu\RolesAndPermissions\UniqueConstraintViolationException;
use PDO;

final class PermissionRepositoryPostgresTest extends PostgresIntegrationTestCase
{
    private const PERMISSION_ID = 'e10ca9be-e4e4-4772-b85c-731b62717713';
    private const CONTEXT_ID = 'a086b4cf-f5ae-48e0-bb62-d6171843dc4b';
    private const NON_EXISTING_PERMISSION_ID = '78a5ca82-c16f-44de-a0fd-f7d614f95316';

    private PermissionRepositoryPostgres $permissionRepository;
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->createPdoConnection();
        $this->ensurePostgresSchema($this->pdo, '_jomisacu_');
        $this->truncatePostgresSchema($this->pdo, '_jomisacu_');
        $this->permissionRepository = new PermissionRepositoryPostgres($this->pdo);
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo)) {
            $this->truncatePostgresSchema($this->pdo, '_jomisacu_');
        }
    }

    public function testFindByContextId(): void
    {
        $permission = new Permission(self::PERMISSION_ID, self::CONTEXT_ID, 'Test Permission', 'Test Permission Description');
        $this->permissionRepository->create($permission);

        $permissions = $this->permissionRepository->findByContextId(self::CONTEXT_ID);
        $this->assertCount(1, $permissions);
        $this->assertSame(self::PERMISSION_ID, $permissions[0]->id);
        $this->assertSame(self::CONTEXT_ID, $permissions[0]->contextId);
        $this->assertSame([], $this->permissionRepository->findByContextId(self::NON_EXISTING_PERMISSION_ID));
    }

    public function testUpdate(): void
    {
        $permission = new Permission(self::PERMISSION_ID, self::CONTEXT_ID, 'Test Permission', 'Test Permission Description');
        $this->permissionRepository->create($permission);

        $updatedPermission = new Permission(self::PERMISSION_ID, self::CONTEXT_ID, 'Updated Test Permission', 'Updated Test Permission Description');
        $this->permissionRepository->update($updatedPermission);

        $permissions = $this->permissionRepository->findByContextId(self::CONTEXT_ID);
        $this->assertCount(1, $permissions);
        $this->assertSame('Updated Test Permission', $permissions[0]->name);
        $this->assertSame('Updated Test Permission Description', $permissions[0]->description);
    }

    public function testDelete(): void
    {
        $permission = new Permission(self::PERMISSION_ID, self::CONTEXT_ID, 'Test Permission', 'Test Permission Description');
        $this->permissionRepository->create($permission);
        $this->permissionRepository->delete($permission);

        $this->assertSame([], $this->permissionRepository->findByContextId(self::CONTEXT_ID));
    }

    public function testCreateThrowsUniqueConstraintViolationOnDuplicateId(): void
    {
        $permission = new Permission(self::PERMISSION_ID, self::CONTEXT_ID, 'Test Permission', 'Test Permission Description');
        $this->permissionRepository->create($permission);

        $this->expectException(UniqueConstraintViolationException::class);

        $this->permissionRepository->create($permission);
    }
}
