<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\ActorRoleRelation;
use Jomisacu\RolesAndPermissions\ActorRoleRelationRepositoryMySql;
use Jomisacu\RolesAndPermissions\Role;
use Jomisacu\RolesAndPermissions\RoleRepositoryMySql;
use PDO;

class ActorRoleRelationRepositoryMySqlTest extends MySqlIntegrationTestCase
{
    private const ACTOR_ID = 'b8292971-8fea-428f-9a6a-a266c11509f9';
    private const ROLE_ID = '3b7d8190-3720-4ca0-8a38-0b36c64735a5';
    private const CONTEXT_ID = 'af881762-cabf-4cd1-8277-f2f88f7aba1c';

    protected ActorRoleRelationRepositoryMySql $actorRoleRelationRepository;
    protected PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->createPdoConnection();
        $this->actorRoleRelationRepository = new ActorRoleRelationRepositoryMySql($this->pdo);

        $roleRepository = new RoleRepositoryMySql($this->pdo);
        $roleRepository->create(new Role(self::ROLE_ID, self::CONTEXT_ID, 'Test Role', 'Test Role Description'));
    }

    protected function tearDown(): void
    {
        if (!isset($this->pdo)) {
            return;
        }

        $roleRepository = new RoleRepositoryMySql($this->pdo);
        $roleRepository->delete(new Role(self::ROLE_ID, self::CONTEXT_ID, 'Test Role', 'Test Role Description'));
    }

    public function testCreateFindAndDelete(): void
    {
        $actorRoleRelation = new ActorRoleRelation(
            self::CONTEXT_ID,
            self::ACTOR_ID,
            self::ROLE_ID,
            null,
            new \DateTimeImmutable(),
            null,
            null,
        );

        $this->actorRoleRelationRepository->create($actorRoleRelation);

        $actorRoleRelations = $this->actorRoleRelationRepository->findActorRoles(self::CONTEXT_ID, self::ACTOR_ID);

        $this->assertCount(1, $actorRoleRelations);
        $this->assertEquals(self::ROLE_ID, $actorRoleRelations[0]->roleId);
        $this->assertNull($actorRoleRelations[0]->updatedAt);

        $this->actorRoleRelationRepository->delete($actorRoleRelation);

        $this->assertSame([], $this->actorRoleRelationRepository->findActorRoles(self::CONTEXT_ID, self::ACTOR_ID));
    }
}
