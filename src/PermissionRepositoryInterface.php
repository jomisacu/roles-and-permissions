<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

interface PermissionRepositoryInterface
{
    /**
     * @return Permission[]
     */
    public function findByContextId(string $contextId): array;

    public function create(Permission $permission): void;

    public function update(Permission $permission): void;

    public function delete(Permission $permission): void;
}
