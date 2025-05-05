<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

interface PermissionCheckerInterface
{
    /**
     * Check if an actor has a specific role in a given context.
     *
     * @param string $contextId The ID of the context.
     * @param string $actorId The ID of the actor.
     * @param string $roleId The ID of the role.
     *
     * @return bool True if the actor has the role, false otherwise.
     */
    function is(string $contextId, string $actorId, string $roleId): bool;

    /**
     * Check if an actor has any of the specified roles in a given context.
     *
     * @param string $contextId The ID of the context.
     * @param string $actorId The ID of the actor.
     * @param array<string> $roleIds An array of role IDs to check against.
     *
     * @return bool True if the actor has any of the roles, false otherwise.
     */
    function isAny(string $contextId, string $actorId, array $roleIds): bool;

    /**
     * Check if an actor has all of the specified roles in a given context.
     *
     * @param string $contextId The ID of the context.
     * @param string $actorId The ID of the actor.
     * @param array<string> $roleIds An array of role IDs to check against.
     *
     * @return bool True if the actor has all of the roles, false otherwise.
     */
    function isAll(string $contextId, string $actorId, array $roleIds): bool;

    /**
     * Check if an actor has a specific permission in a given context.
     *
     * @param string $contextId The ID of the context.
     * @param string $actorId The ID of the actor.
     * @param string $permissionId The ID of the permission.
     * @param string $resource The resource to check the permission against.
     *
     * @return bool True if the actor has the permission, false otherwise.
     */
    function can(string $contextId, string $actorId, string $permissionId, string $resource): bool;

    /**
     * Check if an actor has any of the specified permissions in a given context.
     *
     * @param string $contextId The ID of the context.
     * @param string $actorId The ID of the actor.
     * @param array<string> $permissionIds An array of permission IDs to check against.
     * @param string $resource The resource to check the permissions against.
     *
     * @return bool True if the actor has any of the permissions, false otherwise.
     */
    function canAny(string $contextId, string $actorId, array $permissionIds, string $resource): bool;

    /**
     * Check if an actor has all of the specified permissions in a given context.
     *
     * @param string $contextId The ID of the context.
     * @param string $actorId The ID of the actor.
     * @param array<string> $permissionIds An array of permission IDs to check against.
     * @param string $resource The resource to check the permissions against.
     *
     * @return bool True if the actor has all of the permissions, false otherwise.
     */
    function canAll(string $contextId, string $actorId, array $permissionIds, string $resource): bool;
}
