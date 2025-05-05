<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class Role
{
    public function __construct(
        public readonly string $id,
        public readonly string $contextId,
        public readonly string $name,
        public readonly ?string $description
    ) {
    }
}
