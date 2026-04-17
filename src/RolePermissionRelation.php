<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

/**
 * @property-read string $contextId
 * @property-read string $roleId
 * @property-read string $permissionId
 * @property-read string $resource
 * @property-read bool $negated
 * @property-read string|null $createdByUserId
 * @property-read \DateTimeInterface $createdAt
 * @property-read string|null $updatedByUserId
 * @property-read \DateTimeInterface|null $updatedAt
 */
final class RolePermissionRelation implements PermissionRelationInterface
{
    use PermissionRelationTrait;
    use ReadsPrivateProperties;

    private string $contextId;
    private string $roleId;
    private string $permissionId;
    private string $resource;
    private bool $negated;
    private ?string $createdByUserId;
    private \DateTimeInterface $createdAt;
    private ?string $updatedByUserId;
    private ?\DateTimeInterface $updatedAt;

    public function __construct(
        string $contextId,
        string $roleId,
        string $permissionId,
        string $resource,
        bool $negated,
        ?string $createdByUserId,
        \DateTimeInterface $createdAt,
        ?string $updatedByUserId,
        ?\DateTimeInterface $updatedAt
    ) {
        $this->contextId = $contextId;
        $this->roleId = $roleId;
        $this->permissionId = $permissionId;
        $this->resource = $resource;
        $this->negated = $negated;
        $this->createdByUserId = $createdByUserId;
        $this->createdAt = $createdAt;
        $this->updatedByUserId = $updatedByUserId;
        $this->updatedAt = $updatedAt;
    }

    public function getRoleId(): string
    {
        return $this->roleId;
    }
}
