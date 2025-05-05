<?php

declare(strict_types=1);

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

        $this->assertTrue($checker->canAny(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, [self::GRANTED_EXPLICITLY_PERMISSION_ID, self::GRANTED_EXPLICITLY_PERMISSION_ID_2], self::GRANTED_RESOURCE_EXPRESSION));
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

        $this->assertFalse($checker->can(self::FIRST_CONTEXT_ID, self::FIRST_ACTOR_ID, self::NEGATED_BY_ROLE_RESOURCE_EXPRESSION, self::NEGATED_RESOURCE_EXPRESSION));
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
                return [
                    new RolePermissionRelation(
                        $contextId,
                        $roleId,
                        'GRANTED_THROUGH_ROLE_PERMISSION_ID',
                        'GRANTED_RESOURCE_EXPRESSION',
                        false,
                        null,
                        new \DateTimeImmutable(),
                        null,
                        null,
                    )
                ];
            }

            public function findByContextAndRoles(string $contextId, array $roleIds): array
            {
                return [
                    new RolePermissionRelation(
                        $contextId,
                        $roleIds[0],
                        'GRANTED_THROUGH_ROLE_PERMISSION_ID',
                        'GRANTED_RESOURCE_EXPRESSION',
                        false,
                        null,
                        new \DateTimeImmutable(),
                        null,
                        null,
                    ),
                    new RolePermissionRelation(
                        $contextId,
                        $roleIds[1],
                        'GRANTED_THROUGH_ROLE_PERMISSION_ID_2',
                        'GRANTED_RESOURCE_EXPRESSION',
                        false,
                        null,
                        new \DateTimeImmutable(),
                        null,
                        null,
                    ),
                ];
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
