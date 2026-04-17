<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

/**
 * @property-read string $contextId
 * @property-read string $actorId
 * @property-read string $roleId
 * @property-read string|null $createdByUserId
 * @property-read \DateTimeInterface $createdAt
 * @property-read string|null $updatedByUserId
 * @property-read \DateTimeInterface|null $updatedAt
 */
final class ActorRoleRelation
{
    use ReadsPrivateProperties;

    private string $contextId;
    private string $actorId;
    private string $roleId;
    private ?string $createdByUserId;
    private \DateTimeInterface $createdAt;
    private ?string $updatedByUserId;
    private ?\DateTimeInterface $updatedAt;

    public function __construct(
        string $contextId,
        string $actorId,
        string $roleId,
        ?string $createdByUserId,
        \DateTimeInterface $createdAt,
        ?string $updatedByUserId,
        ?\DateTimeInterface $updatedAt
    ) {
        $this->contextId = $contextId;
        $this->actorId = $actorId;
        $this->roleId = $roleId;
        $this->createdByUserId = $createdByUserId;
        $this->createdAt = $createdAt;
        $this->updatedByUserId = $updatedByUserId;
        $this->updatedAt = $updatedAt;
    }
}
