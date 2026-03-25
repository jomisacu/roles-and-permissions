<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\ActorPermissionRelation;
use Jomisacu\RolesAndPermissions\ActorPermissionRelationRepositoryInterface;
use Jomisacu\RolesAndPermissions\ActorRoleRelation;
use Jomisacu\RolesAndPermissions\ActorRoleRelationRepositoryInterface;
use Jomisacu\RolesAndPermissions\PermissionChecker;
use Jomisacu\RolesAndPermissions\ResourceMatcherInterface;
use Jomisacu\RolesAndPermissions\RolePermissionRelation;
use Jomisacu\RolesAndPermissions\RolePermissionRelationRepositoryInterface;
use PHPUnit\Framework\TestCase;

class PermissionCheckerTest extends TestCase
{
    private const FIRST_CONTEXT_ID = 'FIRST_CONTEXT_ID';
    private const SECOND_CONTEXT_ID = 'SECOND_CONTEXT_ID';
    private const ASSIGNED_ROLE_ID = 'ASSIGNED_ROLE_ID';
    private const ASSIGNED_ROLE_ID_2 = 'ASSIGNED_ROLE_ID_2';
    private const UNASSIGNED_ROLE_ID = 'UNASSIGNED_ROLE_ID';
    private const GRANTED_EXPLICITLY_PERMISSION_ID = 'GRANTED_EXPLICITLY_PERMISSION_ID';
    private const GRANTED_EXPLICITLY_PERMISSION_ID_2 = 'GRANTED_EXPLICITLY_PERMISSION_ID_2';
    private const GRANTED_THROUGH_ROLE_PERMISSION_ID = 'GRANTED_THROUGH_ROLE_PERMISSION_ID';
    private const GRANTED_THROUGH_ROLE_PERMISSION_ID_2 = 'GRANTED_THROUGH_ROLE_PERMISSION_ID_2';
    private const UNGRANTED_PERMISSION_ID = 'UNGRANTED_PERMISSION_ID';
    private const NEGATED_EXPLICITLY_PERMISSION_ID = 'NEGATED_EXPLICITLY_PERMISSION_ID';
    private const NEGATED_THROUGH_ROLE_PERMISSION_ID = 'NEGATED_THROUGH_ROLE_PERMISSION_ID';
    private const FIRST_ACTOR_ID = 'FIRST_ACTOR_ID';
    private const SECOND_ACTOR_ID = 'SECOND_ACTOR_ID';
    private const GRANTED_RESOURCE_EXPRESSION = 'GRANTED_RESOURCE_EXPRESSION';
    private const UNGRANTED_RESOURCE_EXPRESSION = 'UNGRANTED_RESOURCE_EXPRESSION';
    private const NEGATED_RESOURCE_EXPRESSION = 'NEGATED_RESOURCE_EXPRESSION';
    private const NEGATED_BY_ROLE_RESOURCE_EXPRESSION = 'NEGATED_BY_ROLE_RESOURCE_EXPRESSION';

    public function testActorCanPerformAnActionByGrantedPermission()
    {
        $checker = $this->getPermissionChecker();

        $this->assertTrue($checker->can(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, self::GRANTED_EXPLICITLY_PERMISSION_ID, self::GRANTED_RESOURCE_EXPRESSION));
    }

    public function testActorCanPerformAnActionIfHasOneOfAnyGrantedPermission()
    {
        $checker = $this->getPermissionChecker();

        $this->assertTrue($checker->canAny(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, [self::GRANTED_EXPLICITLY_PERMISSION_ID, self::NEGATED_EXPLICITLY_PERMISSION_ID], self::GRANTED_RESOURCE_EXPRESSION));
    }

    public function testActorCanPerformAnActionIfHasAllGrantedPermission()
    {
        $checker = $this->getPermissionChecker();

        $this->assertTrue($checker->canAll(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, [self::GRANTED_EXPLICITLY_PERMISSION_ID, self::GRANTED_EXPLICITLY_PERMISSION_ID_2], self::GRANTED_RESOURCE_EXPRESSION));
    }

    public function testActorCanPerformAnActionByGrantedPermissionByRole()
    {
        $checker = $this->getPermissionChecker();

        $this->assertTrue($checker->can(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, self::GRANTED_THROUGH_ROLE_PERMISSION_ID, self::GRANTED_RESOURCE_EXPRESSION));
    }

    public function testActorCanNotPerformTheActionWithoutPermission()
    {
        $checker = $this->getPermissionChecker();

        $this->assertFalse($checker->can(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, self::UNGRANTED_PERMISSION_ID, self::GRANTED_RESOURCE_EXPRESSION));
    }

    public function testActorCanNotPerformTheActionWithPermissionNegated()
    {
        $checker = $this->getPermissionChecker();

        $this->assertFalse($checker->can(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, self::NEGATED_EXPLICITLY_PERMISSION_ID, self::GRANTED_RESOURCE_EXPRESSION));
    }

    public function testActorCanNotPerformTheActionWithPermissionNegatedByRole()
    {
        $checker = $this->getPermissionChecker();

        $this->assertFalse($checker->can(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, self::NEGATED_THROUGH_ROLE_PERMISSION_ID, self::NEGATED_BY_ROLE_RESOURCE_EXPRESSION));
    }

    public function testActorIsSomeoneWithAGivenRole()
    {
        $checker = $this->getPermissionChecker();

        $this->assertTrue($checker->is(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, self::ASSIGNED_ROLE_ID));
    }

    public function testActorIsSomeoneWithAtLeastOneFromGivenRoles()
    {
        $checker = $this->getPermissionChecker();

        $this->assertTrue($checker->isAny(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, [self::ASSIGNED_ROLE_ID, self::UNASSIGNED_ROLE_ID]));
    }

    public function testActorIsSomeoneWithAllRequestedRolesEvenWithAdditionalAssignedRoles()
    {
        $checker = $this->getPermissionChecker();

        $this->assertTrue($checker->isAll(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, [self::ASSIGNED_ROLE_ID]));
        $this->assertTrue($checker->isAll(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, [self::ASSIGNED_ROLE_ID, self::ASSIGNED_ROLE_ID_2]));
    }

    public function testActorIsNotSomeoneWithoutAllRequestedRoles()
    {
        $checker = $this->getPermissionChecker();

        $this->assertFalse($checker->isAll(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, [self::ASSIGNED_ROLE_ID, self::UNASSIGNED_ROLE_ID]));
        $this->assertFalse($checker->isAll(self::FIRST_CONTEXT_ID, self::SECOND_ACTOR_ID, [self::ASSIGNED_ROLE_ID]));
    }

    public function testActorCanNotPerformAnActionIfMissingOnePermissionWhenCheckingAll()
    {
        $checker = $this->getPermissionChecker();

        $this->assertFalse($checker->canAll(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, [self::GRANTED_EXPLICITLY_PERMISSION_ID, self::UNGRANTED_PERMISSION_ID], self::GRANTED_RESOURCE_EXPRESSION));
    }

    public function testCheckerReadsFreshExplicitPermissionsOnEveryCall(): void
    {
        $actorPermissionRepository = new MutableActorPermissionRelationRepository();
        $actorPermissionRepository->replacePermissions(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, [
            new ActorPermissionRelation(
                self::FIRST_CONTEXT_ID,
                self::FIRST_ACTOR_ID,
                self::GRANTED_EXPLICITLY_PERMISSION_ID,
                self::GRANTED_RESOURCE_EXPRESSION,
                false,
                null,
                new \DateTimeImmutable(),
                null,
                null,
            ),
        ]);

        $checker = new PermissionChecker(
            new MutableActorRoleRelationRepository(),
            $this->getResourceMatcher(),
            $actorPermissionRepository,
            new MutableRolePermissionRelationRepository(),
        );

        $this->assertTrue($checker->can(
            self::FIRST_CONTEXT_ID,
            self::FIRST_ACTOR_ID,
            self::GRANTED_EXPLICITLY_PERMISSION_ID,
            self::GRANTED_RESOURCE_EXPRESSION,
        ));

        $actorPermissionRepository->replacePermissions(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, []);

        $this->assertFalse($checker->can(
            self::FIRST_CONTEXT_ID,
            self::FIRST_ACTOR_ID,
            self::GRANTED_EXPLICITLY_PERMISSION_ID,
            self::GRANTED_RESOURCE_EXPRESSION,
        ));
    }

    public function testCheckerReadsFreshRoleAssignmentsOnEveryCall(): void
    {
        $actorRoleRepository = new MutableActorRoleRelationRepository();
        $actorRoleRepository->replaceRoles(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, [
            new ActorRoleRelation(
                self::FIRST_CONTEXT_ID,
                self::FIRST_ACTOR_ID,
                self::ASSIGNED_ROLE_ID,
                null,
                new \DateTimeImmutable(),
                null,
                null,
            ),
        ]);

        $rolePermissionRepository = new MutableRolePermissionRelationRepository();
        $rolePermissionRepository->replaceRelations(self::FIRST_CONTEXT_ID, self::ASSIGNED_ROLE_ID, [
            new RolePermissionRelation(
                self::FIRST_CONTEXT_ID,
                self::ASSIGNED_ROLE_ID,
                self::GRANTED_THROUGH_ROLE_PERMISSION_ID,
                self::GRANTED_RESOURCE_EXPRESSION,
                false,
                null,
                new \DateTimeImmutable(),
                null,
                null,
            ),
        ]);

        $checker = new PermissionChecker(
            $actorRoleRepository,
            $this->getResourceMatcher(),
            new MutableActorPermissionRelationRepository(),
            $rolePermissionRepository,
        );

        $this->assertTrue($checker->is(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, self::ASSIGNED_ROLE_ID));
        $this->assertTrue($checker->can(
            self::FIRST_CONTEXT_ID,
            self::FIRST_ACTOR_ID,
            self::GRANTED_THROUGH_ROLE_PERMISSION_ID,
            self::GRANTED_RESOURCE_EXPRESSION,
        ));

        $actorRoleRepository->replaceRoles(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, []);

        $this->assertFalse($checker->is(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, self::ASSIGNED_ROLE_ID));
        $this->assertFalse($checker->can(
            self::FIRST_CONTEXT_ID,
            self::FIRST_ACTOR_ID,
            self::GRANTED_THROUGH_ROLE_PERMISSION_ID,
            self::GRANTED_RESOURCE_EXPRESSION,
        ));
    }

    private function getPermissionChecker(): PermissionChecker
    {
        return new PermissionChecker(
            $this->getActorRoleRelationRepository(),
            $this->getResourceMatcher(),
            $this->getActorPermissionRelationRepository(),
            $this->getRolePermissionRelationRepository(),
        );
    }

    private function getActorRoleRelationRepository(): ActorRoleRelationRepositoryInterface
    {
        return new class implements ActorRoleRelationRepositoryInterface {
            private array $roles = [];

            public function __construct()
            {
                $this->roles = [
                    'FIRST_CONTEXT_ID' => [
                        'FIRST_ACTOR_ID' => [
                            new ActorRoleRelation(
                                'FIRST_CONTEXT_ID',
                                'FIRST_ACTOR_ID',
                                'ASSIGNED_ROLE_ID',
                                null,
                                new \DateTimeImmutable(),
                                null,
                                null,
                            ),
                            new ActorRoleRelation(
                                'FIRST_CONTEXT_ID',
                                'FIRST_ACTOR_ID',
                                'ASSIGNED_ROLE_ID_2',
                                null,
                                new \DateTimeImmutable(),
                                null,
                                null,
                            )
                        ],
                    ]
                ];
            }

            public function findActorRoles(string $contextId, string $actorId): array
            {
                return $this->roles[$contextId][$actorId] ?? [];
            }

            public function create(ActorRoleRelation $actorRoleRelation): void
            {
            }

            public function delete(ActorRoleRelation $actorRoleRelation): void
            {
            }
        };
    }

    private function getResourceMatcher(): ResourceMatcherInterface
    {
        return new \Jomisacu\RolesAndPermissions\ResourceMatcher();
    }

    private function getActorPermissionRelationRepository(): ActorPermissionRelationRepositoryInterface
    {
        return new class implements ActorPermissionRelationRepositoryInterface {
            private array $permissions = [];

            public function __construct()
            {
                $this->permissions = [
                    'FIRST_CONTEXT_ID' => [
                        'FIRST_ACTOR_ID' => [
                            new ActorPermissionRelation(
                                'FIRST_CONTEXT_ID',
                                'FIRST_ACTOR_ID',
                                'GRANTED_EXPLICITLY_PERMISSION_ID',
                                'GRANTED_RESOURCE_EXPRESSION',
                                false,
                                null,
                                new \DateTimeImmutable(),
                                null,
                                null,
                            ),
                            new ActorPermissionRelation(
                                'FIRST_CONTEXT_ID',
                                'FIRST_ACTOR_ID',
                                'GRANTED_EXPLICITLY_PERMISSION_ID_2',
                                'GRANTED_RESOURCE_EXPRESSION',
                                false,
                                null,
                                new \DateTimeImmutable(),
                                null,
                                null,
                            ),
                            new ActorPermissionRelation(
                                'FIRST_CONTEXT_ID',
                                'FIRST_ACTOR_ID',
                                'NEGATED_EXPLICITLY_PERMISSION_ID',
                                'GRANTED_RESOURCE_EXPRESSION',
                                true,
                                null,
                                new \DateTimeImmutable(),
                                null,
                                null,
                            ),
                        ]
                    ]
                ];
            }

            public function findByContextAndActor(string $contextId, string $actorId): array
            {
                return $this->permissions[$contextId][$actorId] ?? [];
            }

            public function create(ActorPermissionRelation $actorPermissionRelation): void
            {
            }

            public function delete(ActorPermissionRelation $actorPermissionRelation): void
            {
            }
        };
    }

    private function getRolePermissionRelationRepository(): RolePermissionRelationRepositoryInterface
    {
        return new class implements RolePermissionRelationRepositoryInterface {
            public function findByContextAndRole(string $contextId, string $roleId): array
            {
                return $this->findByContextAndRoles($contextId, [$roleId]);
            }

            public function findByContextAndRoles(string $contextId, array $roleIds): array
            {
                $relations = [];

                if (in_array('ASSIGNED_ROLE_ID', $roleIds, true)) {
                    $relations[] = new RolePermissionRelation(
                        $contextId,
                        'ASSIGNED_ROLE_ID',
                        'GRANTED_THROUGH_ROLE_PERMISSION_ID',
                        'GRANTED_RESOURCE_EXPRESSION',
                        false,
                        null,
                        new \DateTimeImmutable(),
                        null,
                        null,
                    );
                }

                if (in_array('ASSIGNED_ROLE_ID_2', $roleIds, true)) {
                    $relations[] = new RolePermissionRelation(
                        $contextId,
                        'ASSIGNED_ROLE_ID_2',
                        'GRANTED_THROUGH_ROLE_PERMISSION_ID_2',
                        'GRANTED_RESOURCE_EXPRESSION',
                        false,
                        null,
                        new \DateTimeImmutable(),
                        null,
                        null,
                    );
                    $relations[] = new RolePermissionRelation(
                        $contextId,
                        'ASSIGNED_ROLE_ID_2',
                        'NEGATED_THROUGH_ROLE_PERMISSION_ID',
                        'NEGATED_BY_ROLE_RESOURCE_EXPRESSION',
                        true,
                        null,
                        new \DateTimeImmutable(),
                        null,
                        null,
                    );
                }

                return $relations;
            }

            public function create(RolePermissionRelation $rolePermissionRelation): void
            {
            }

            public function delete(RolePermissionRelation $rolePermissionRelation): void
            {
            }
        };
    }
}

final class MutableActorRoleRelationRepository implements ActorRoleRelationRepositoryInterface
{
    /**
     * @var array<string, array<string, array<ActorRoleRelation>>>
     */
    private array $roles = [];

    /**
     * @param ActorRoleRelation[] $relations
     */
    public function replaceRoles(string $contextId, string $actorId, array $relations): void
    {
        $this->roles[$contextId][$actorId] = $relations;
    }

    public function findActorRoles(string $contextId, string $actorId): array
    {
        return $this->roles[$contextId][$actorId] ?? [];
    }

    public function create(ActorRoleRelation $actorRoleRelation): void
    {
    }

    public function delete(ActorRoleRelation $actorRoleRelation): void
    {
    }
}

final class MutableActorPermissionRelationRepository implements ActorPermissionRelationRepositoryInterface
{
    /**
     * @var array<string, array<string, array<ActorPermissionRelation>>>
     */
    private array $permissions = [];

    /**
     * @param ActorPermissionRelation[] $relations
     */
    public function replacePermissions(string $contextId, string $actorId, array $relations): void
    {
        $this->permissions[$contextId][$actorId] = $relations;
    }

    public function findByContextAndActor(string $contextId, string $actorId): array
    {
        return $this->permissions[$contextId][$actorId] ?? [];
    }

    public function create(ActorPermissionRelation $actorPermissionRelation): void
    {
    }

    public function delete(ActorPermissionRelation $actorPermissionRelation): void
    {
    }
}

final class MutableRolePermissionRelationRepository implements RolePermissionRelationRepositoryInterface
{
    /**
     * @var array<string, array<string, array<RolePermissionRelation>>>
     */
    private array $relations = [];

    /**
     * @param RolePermissionRelation[] $relations
     */
    public function replaceRelations(string $contextId, string $roleId, array $relations): void
    {
        $this->relations[$contextId][$roleId] = $relations;
    }

    public function findByContextAndRole(string $contextId, string $roleId): array
    {
        return $this->relations[$contextId][$roleId] ?? [];
    }

    public function findByContextAndRoles(string $contextId, array $roleIds): array
    {
        $result = [];

        foreach ($roleIds as $roleId) {
            $result = [...$result, ...($this->relations[$contextId][$roleId] ?? [])];
        }

        return $result;
    }

    public function create(RolePermissionRelation $rolePermissionRelation): void
    {
    }

    public function delete(RolePermissionRelation $rolePermissionRelation): void
    {
    }
}
