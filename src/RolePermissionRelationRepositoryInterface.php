<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

interface RolePermissionRelationRepositoryInterface
{
    
    /**
     * Find permission relations by context and role identifiers
     *
     * @param string $contextId The context identifier
     * @param string $roleId The role identifier
     *
     * @return RolePermissionRelation[]
     */
    public function findByContextAndRole(string $contextId, string $roleId): array;


    /**
     * Find permission relations by context and multiple role identifiers
     *
     * @param string $contextId The context identifier
     * @param string[] $roleIds Array of role identifiers
     *
     * @return RolePermissionRelation[] Array of permission relations
     */
    public function findByContextAndRoles(string $contextId, array $roleIds): array;

    public function create(RolePermissionRelation $rolePermissionRelation): void;

    public function delete(RolePermissionRelation $rolePermissionRelation): void;
}
