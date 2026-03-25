<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class RolePermissionRelation implements PermissionRelationInterface
{
    use PermissionRelationTrait;

    public function __construct(
        public readonly string $contextId,
        public readonly string $roleId,
        public readonly string $permissionId,
        public readonly string $resource,
        public readonly bool $negated,
        public readonly ?string $createdByUserId,
        public readonly \DateTimeInterface $createdAt,
        public readonly ?string $updatedByUserId,
        public readonly ?\DateTimeInterface $updatedAt,
    ) {
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }
}
