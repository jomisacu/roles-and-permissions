<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\ActorRoleRelation;
use Jomisacu\RolesAndPermissions\ActorRoleRelationRepositoryPostgres;
use Jomisacu\RolesAndPermissions\ForeignKeyConstraintViolationException;
use Jomisacu\RolesAndPermissions\Role;
use Jomisacu\RolesAndPermissions\RoleRepositoryPostgres;
use PDO;

final class ActorRoleRelationRepositoryPostgresTest extends PostgresIntegrationTestCase
{
    private const ACTOR_ID = '4bf24190-7e61-4580-ab62-eb72e29cb4cc';
    private const ROLE_ID = '26d08d48-7b02-47fd-b4d3-dc667fcb08c0';
    private const CONTEXT_ID = '1dcb35d9-54f0-4218-9691-e2467b0ec9e3';

    private ActorRoleRelationRepositoryPostgres $actorRoleRelationRepository;
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->createPdoConnection();
        $this->ensurePostgresSchema($this->pdo, '_jomisacu_');
        $this->truncatePostgresSchema($this->pdo, '_jomisacu_');
        $this->actorRoleRelationRepository = new ActorRoleRelationRepositoryPostgres($this->pdo);

        $roleRepository = new RoleRepositoryPostgres($this->pdo);
        $roleRepository->create(new Role(self::ROLE_ID, self::CONTEXT_ID, 'Test Role', 'Test Role Description'));
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo)) {
            $this->truncatePostgresSchema($this->pdo, '_jomisacu_');
        }
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
        $this->assertSame(self::ROLE_ID, $actorRoleRelations[0]->roleId);
        $this->assertNull($actorRoleRelations[0]->updatedAt);

        $this->actorRoleRelationRepository->delete($actorRoleRelation);

        $this->assertSame([], $this->actorRoleRelationRepository->findActorRoles(self::CONTEXT_ID, self::ACTOR_ID));
    }

    public function testCreateThrowsForeignKeyConstraintViolationForUnknownRole(): void
    {
        $this->expectException(ForeignKeyConstraintViolationException::class);

        $this->actorRoleRelationRepository->create(new ActorRoleRelation(
            self::CONTEXT_ID,
            self::ACTOR_ID,
            'missing-role-id',
            null,
            new \DateTimeImmutable(),
            null,
            null,
        ));
    }
}
