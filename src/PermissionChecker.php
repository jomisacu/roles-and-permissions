<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class PermissionChecker implements PermissionCheckerInterface
{
    private array $actorRoleRelationsCache = [];
    private array $actorPermissionsCache = [];

    public function __construct(
        private readonly ActorRoleRelationRepositoryInterface $actorRoleRelationRepository,
        private readonly ResourceMatcherInterface $resourceMatcher,
        private readonly ActorPermissionRelationRepositoryInterface $actorPermissionRelationRepository,
        private readonly RolePermissionRelationRepositoryInterface $rolePermissionRelationRepository,
    ) {
    }

    public function isAny(string $contextId, string $actorId, array $roleIds): bool
    {
        foreach ($roleIds as $roleId) {
            if ($this->is($contextId, $actorId, $roleId)) {
                return true;
            }
        }

        return false;
    }

    public function is(string $contextId, string $actorId, string $roleId): bool
    {
        foreach ($this->getActorRoles($contextId, $actorId) as $relation) {
            if ($relation->roleId === $roleId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ActorRoleRelation[]
     */
    private function getActorRoles(string $contextId, string $actorId): array
    {
        $this->loadActorRoles($contextId, $actorId);

        return $this->actorRoleRelationsCache[$contextId][$actorId];
    }

    private function loadActorRoles(string $contextId, string $actorId): void
    {
        if (!isset($this->actorRoleRelationsCache[$contextId][$actorId])) {
            $this->actorRoleRelationsCache[$contextId][$actorId] = $this->actorRoleRelationRepository->findActorRoles($contextId, $actorId);
        }
    }

    public function isAll(string $contextId, string $actorId, array $roleIds): bool
    {
        if ($roleIds === []) {
            return true;
        }

        $assignedRoleIds = array_map(
            static fn (ActorRoleRelation $relation): string => $relation->roleId,
            $this->getActorRoles($contextId, $actorId),
        );

        foreach ($roleIds as $roleId) {
            if (!in_array($roleId, $assignedRoleIds, true)) {
                return false;
            }
        }

        return true;
    }

    public function canAny(string $contextId, string $actorId, array $permissionIds, string $resource): bool
    {
        foreach ($permissionIds as $permissionId) {
            if ($this->can($contextId, $actorId, $permissionId, $resource)) {
                return true;
            }
        }

        return false;
    }

    public function can(string $contextId, string $actorId, string $permissionId, string $resource): bool
    {
        if ($this->permissionNegatedToThisActor($contextId, $actorId, $permissionId, $resource)) {
            return false;
        }

        return $this->permissionGrantedToThisActor($contextId, $actorId, $permissionId, $resource);
    }

    private function permissionNegatedToThisActor(
        string $contextId,
        string $actorId,
        string $permissionId,
        string $resource
    ): bool {
        foreach ($this->getActorPermissions($contextId, $actorId) as $permissionRelation) {
            if ($permissionRelation->getPermissionId() === $permissionId && $this->resourceMatcher->match($permissionRelation->getResource(), $resource)) {
                if ($permissionRelation->isNegated()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $contextId
     * @param string $actorId
     *
     * @return PermissionRelationInterface[]
     */
    private function getActorPermissions(string $contextId, string $actorId): array
    {
        $this->loadActorPermissions($contextId, $actorId);

        return $this->actorPermissionsCache[$contextId][$actorId];
    }

    private function loadActorPermissions(string $contextId, string $actorId): void
    {
        if (!isset($this->actorPermissionsCache[$contextId][$actorId])) {
            $this->actorPermissionsCache[$contextId][$actorId] = $this->findActorPermissions($contextId, $actorId);
        }
    }

    /**
     * @param string $contextId
     * @param string $actorId
     *
     * @return array<PermissionRelationInterface>
     */
    private function findActorPermissions(string $contextId, string $actorId): array
    {
        $actorRoles = $this->getActorRoles($contextId, $actorId);
        $roleIds = array_map(fn ($relation) => $relation->roleId, $actorRoles);

        $rolePermissions = empty($roleIds) ? [] : $this->rolePermissionRelationRepository->findByContextAndRoles($contextId, $roleIds);
        $actorPermissions = $this->actorPermissionRelationRepository->findByContextAndActor($contextId, $actorId);

        return [...$rolePermissions, ...$actorPermissions];
    }

    private function permissionGrantedToThisActor(
        string $contextId,
        string $actorId,
        string $permissionId,
        string $resource
    ): bool {
        foreach ($this->getActorPermissions($contextId, $actorId) as $permissionRelation) {
            if ($permissionRelation->getPermissionId() === $permissionId && $this->resourceMatcher->match($permissionRelation->getResource(), $resource)) {
                if (!$permissionRelation->isNegated()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canAll(string $contextId, string $actorId, array $permissionIds, string $resource): bool
    {
        foreach ($permissionIds as $permissionId) {
            if (!$this->can($contextId, $actorId, $permissionId, $resource)) {
                return false;
            }
        }

        return true;
    }
}
