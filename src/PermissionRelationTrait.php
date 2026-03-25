<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

trait PermissionRelationTrait
{
    public function getContextId(): string
    {
        return $this->contextId;
    }

    public function getPermissionId(): string
    {
        return $this->permissionId;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function isNegated(): bool
    {
        return $this->negated;
    }
}
