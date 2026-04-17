<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\ActorPermissionRelation;
use Jomisacu\RolesAndPermissions\ActorPermissionRelationRepositoryMySql;
use Jomisacu\RolesAndPermissions\Permission;
use Jomisacu\RolesAndPermissions\PermissionRepositoryMySql;
use PDO;

class ActorPermissionRelationRepositoryMySqlTest extends MySqlIntegrationTestCase
{
    const ACTOR_ID = 'b8292971-8fea-428f-9a6a-a266c11509f9';
    const PERMISSION_ID = '3b7d8190-3720-4ca0-8a38-0b36c64735a5';
    const NON_EXISTING_ACTOR_ID = '51082523-42c4-4b7e-97a8-178f37faecf8';
    const CONTEXT_ID = 'af881762-cabf-4cd1-8277-f2f88f7aba1c';

    protected ActorPermissionRelationRepositoryMySql $actorPermissionRelationRepository;
    protected PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->createPdoConnection();
        $this->ensureMySqlSchema($this->pdo, '_jomisacu_');
        $this->actorPermissionRelationRepository = new ActorPermissionRelationRepositoryMySql($this->pdo);

        $permissionRepository = new PermissionRepositoryMySql($this->pdo);
        $permissionRepository->create(new Permission(self::PERMISSION_ID, self::CONTEXT_ID, 'Test Permission', 'Test Permission Description'));
    }

    protected function tearDown(): void
    {
        if (!isset($this->pdo)) {
            return;
        }

        $permissionRepository = new PermissionRepositoryMySql($this->pdo);
        $permissionRepository->delete(new Permission(self::PERMISSION_ID, self::CONTEXT_ID, 'Test Permission', 'Test Permission Description'));
    }

    public function testCreate(): void
    {
        $actorPermissionRelation = new ActorPermissionRelation(
            self::CONTEXT_ID,
            self::ACTOR_ID,
            self::PERMISSION_ID,
            'resource',
            false,
            null,
            new \DateTimeImmutable(),
            null,
            null
        );
        $this->actorPermissionRelationRepository->create($actorPermissionRelation);

        $statement = $this->pdo->prepare('SELECT * FROM _jomisacu_actor_permission_relations WHERE actor_id = :actor_id AND permission_id = :permission_id');
        $params = [
            'actor_id' => self::ACTOR_ID,
            'permission_id' => self::PERMISSION_ID,
        ];
        $statement->execute($params);

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($result);
    }

    /**
     * @depends testCreate
     */
    public function testDelete(): void
    {
        $actorPermissionRelation = new ActorPermissionRelation(
            self::CONTEXT_ID,
            self::ACTOR_ID,
            self::PERMISSION_ID,
            'resource',
            false,
            null,
            new \DateTimeImmutable(),
            null,
            null
        );
        $this->actorPermissionRelationRepository->create($actorPermissionRelation);

        $this->actorPermissionRelationRepository->delete($actorPermissionRelation);
        $statement = $this->pdo->prepare('SELECT * FROM _jomisacu_actor_permission_relations WHERE actor_id = :actor_id AND permission_id = :permission_id');
        $params = [
            'actor_id' => self::ACTOR_ID,
            'permission_id' => self::PERMISSION_ID,
        ];
        $statement->execute($params);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $this->assertFalse($result);
    }

    public function testFindByContextAndActor()
    {
        $actorPermissionRelation = new ActorPermissionRelation(
            self::CONTEXT_ID,
            self::ACTOR_ID,
            self::PERMISSION_ID,
            'resource',
            false,
            null,
            new \DateTimeImmutable(),
            null,
            null
        );
        $this->actorPermissionRelationRepository->create($actorPermissionRelation);

        $actorPermissionRelations = $this->actorPermissionRelationRepository->findByContextAndActor(self::CONTEXT_ID, self::ACTOR_ID);
        $this->assertCount(1, $actorPermissionRelations);
        $this->assertEquals(self::CONTEXT_ID, $actorPermissionRelations[0]->contextId);
        $this->assertEquals(self::ACTOR_ID, $actorPermissionRelations[0]->actorId);
        $this->assertEquals(self::PERMISSION_ID, $actorPermissionRelations[0]->permissionId);
        $this->assertEquals('resource', $actorPermissionRelations[0]->resource);
    }

    public function testDeleteOnlyRemovesTheExactMatchingRule(): void
    {
        $firstRelation = new ActorPermissionRelation(
            self::CONTEXT_ID,
            self::ACTOR_ID,
            self::PERMISSION_ID,
            'resource-a',
            false,
            null,
            new \DateTimeImmutable(),
            null,
            null,
        );
        $secondRelation = new ActorPermissionRelation(
            self::CONTEXT_ID,
            self::ACTOR_ID,
            self::PERMISSION_ID,
            'resource-b',
            false,
            null,
            new \DateTimeImmutable(),
            null,
            null,
        );

        $this->actorPermissionRelationRepository->create($firstRelation);
        $this->actorPermissionRelationRepository->create($secondRelation);

        $this->actorPermissionRelationRepository->delete($firstRelation);

        $actorPermissionRelations = $this->actorPermissionRelationRepository->findByContextAndActor(self::CONTEXT_ID, self::ACTOR_ID);

        $this->assertCount(1, $actorPermissionRelations);
        $this->assertEquals('resource-b', $actorPermissionRelations[0]->resource);
    }
}
