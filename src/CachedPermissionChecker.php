<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

/**
 * Decorator that caches permission check results in memory for the lifetime of the instance.
 *
 * This avoids repeated database queries when the same permission/role checks are performed
 * multiple times within a single request. For cross-request caching, wrap the underlying
 * repositories with a caching layer (e.g. Redis/Memcached).
 */
final class CachedPermissionChecker implements PermissionCheckerInterface
{
    /** @var array<string, bool> */
    private array $cache = [];

    private PermissionCheckerInterface $inner;

    public function __construct(
        PermissionCheckerInterface $inner
    ) {
        $this->inner = $inner;
    }

    public function is(string $contextId, string $actorId, string $roleId): bool
    {
        $key = "is:$contextId:$actorId:$roleId";

        return $this->cache[$key] ??= $this->inner->is($contextId, $actorId, $roleId);
    }

    public function isAny(string $contextId, string $actorId, array $roleIds): bool
    {
        $key = 'isAny:' . $contextId . ':' . $actorId . ':' . implode(',', $roleIds);

        return $this->cache[$key] ??= $this->inner->isAny($contextId, $actorId, $roleIds);
    }

    public function isAll(string $contextId, string $actorId, array $roleIds): bool
    {
        $key = 'isAll:' . $contextId . ':' . $actorId . ':' . implode(',', $roleIds);

        return $this->cache[$key] ??= $this->inner->isAll($contextId, $actorId, $roleIds);
    }

    public function can(string $contextId, string $actorId, string $permissionId, string $resource): bool
    {
        $key = "can:$contextId:$actorId:$permissionId:$resource";

        return $this->cache[$key] ??= $this->inner->can($contextId, $actorId, $permissionId, $resource);
    }

    public function canAny(string $contextId, string $actorId, array $permissionIds, string $resource): bool
    {
        $key = 'canAny:' . $contextId . ':' . $actorId . ':' . implode(',', $permissionIds) . ':' . $resource;

        return $this->cache[$key] ??= $this->inner->canAny($contextId, $actorId, $permissionIds, $resource);
    }

    public function canAll(string $contextId, string $actorId, array $permissionIds, string $resource): bool
    {
        $key = 'canAll:' . $contextId . ':' . $actorId . ':' . implode(',', $permissionIds) . ':' . $resource;

        return $this->cache[$key] ??= $this->inner->canAll($contextId, $actorId, $permissionIds, $resource);
    }

    /**
     * Clear all cached results. Useful after modifying permissions or roles.
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
