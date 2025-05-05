<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

interface RoleRepositoryInterface
{
    /**
     * @return Role[]
     */
    public function findByContextId(string $contextId): array;

    public function findById(string $id): ?Role;

    public function create(Role $role): void;

    public function update(Role $role): void;

    public function delete(Role $role): void;
}
