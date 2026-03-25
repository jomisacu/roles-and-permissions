<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class PermissionChecker implements PermissionCheckerInterface
{
    public function __construct(
        private readonly ActorRoleRelationRepositoryInterface $actorRoleRelationRepository,
        private readonly ResourceMatcherInterface $resourceMatcher,
        private readonly ActorPermissionRelationRepositoryInterface $actorPermissionRelationRepository,
        private readonly RolePermissionRelationRepositoryInterface $rolePermissionRelationRepository,
    ) {
    }

    public function isAny(string $contextId, string $actorId, array $roleIds): bool
    {
        $actorRoles = $this->findActorRoles($contextId, $actorId);

        foreach ($roleIds as $roleId) {
            if ($this->hasRole($actorRoles, $roleId)) {
                return true;
            }
        }

        return false;
    }

    public function is(string $contextId, string $actorId, string $roleId): bool
    {
        return $this->hasRole($this->findActorRoles($contextId, $actorId), $roleId);
    }

    /**
     * @return ActorRoleRelation[]
     */
    private function findActorRoles(string $contextId, string $actorId): array
    {
        return $this->actorRoleRelationRepository->findActorRoles($contextId, $actorId);
    }

    public function isAll(string $contextId, string $actorId, array $roleIds): bool
    {
        if ($roleIds === []) {
            return true;
        }

        $assignedRoleIds = array_map(
            static fn (ActorRoleRelation $relation): string => $relation->roleId,
            $this->findActorRoles($contextId, $actorId),
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
        $permissionRelations = $this->findPermissionRelations($contextId, $actorId);

        foreach ($permissionIds as $permissionId) {
            if ($this->permissionAllowed($permissionRelations, $permissionId, $resource)) {
                return true;
            }
        }

        return false;
    }

    public function can(string $contextId, string $actorId, string $permissionId, string $resource): bool
    {
        return $this->permissionAllowed(
            $this->findPermissionRelations($contextId, $actorId),
            $permissionId,
            $resource,
        );
    }

    /**
     * @param PermissionRelationInterface[] $permissionRelations
     */
    private function permissionAllowed(array $permissionRelations, string $permissionId, string $resource): bool
    {
        if ($this->permissionNegated($permissionRelations, $permissionId, $resource)) {
            return false;
        }

        return $this->permissionGranted($permissionRelations, $permissionId, $resource);
    }

    /**
     * @param PermissionRelationInterface[] $permissionRelations
     */
    private function permissionNegated(
        array $permissionRelations,
        string $permissionId,
        string $resource
    ): bool {
        foreach ($permissionRelations as $permissionRelation) {
            if ($permissionRelation->getPermissionId() === $permissionId && $this->resourceMatcher->match($permissionRelation->getResource(), $resource)) {
                if ($permissionRelation->isNegated()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array<PermissionRelationInterface>
     */
    private function findPermissionRelations(string $contextId, string $actorId): array
    {
        $actorRoles = $this->findActorRoles($contextId, $actorId);
        $roleIds = array_map(fn ($relation) => $relation->roleId, $actorRoles);

        $rolePermissions = empty($roleIds) ? [] : $this->rolePermissionRelationRepository->findByContextAndRoles($contextId, $roleIds);
        $actorPermissions = $this->actorPermissionRelationRepository->findByContextAndActor($contextId, $actorId);

        return [...$rolePermissions, ...$actorPermissions];
    }

    /**
     * @param PermissionRelationInterface[] $permissionRelations
     */
    private function permissionGranted(
        array $permissionRelations,
        string $permissionId,
        string $resource
    ): bool {
        foreach ($permissionRelations as $permissionRelation) {
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
        $permissionRelations = $this->findPermissionRelations($contextId, $actorId);

        foreach ($permissionIds as $permissionId) {
            if (!$this->permissionAllowed($permissionRelations, $permissionId, $resource)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ActorRoleRelation[] $actorRoles
     */
    private function hasRole(array $actorRoles, string $roleId): bool
    {
        foreach ($actorRoles as $relation) {
            if ($relation->roleId === $roleId) {
                return true;
            }
        }

        return false;
    }
}
