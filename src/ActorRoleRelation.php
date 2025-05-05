<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class ActorRoleRelation
{
    public function __construct(
        public readonly string $contextId,
        public readonly string $actorId,
        public readonly string $roleId,
        public readonly ?string $createdByUserId,
        public readonly \DateTimeInterface $createdAt,
        public readonly ?string $updatedByUserId,
        public readonly ?\DateTimeInterface $updatedAt,
    ) {
    }
}
