<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class MigrationDefinition
{
    /**
     * @param array<string> $upStatements
     * @param array<string> $downStatements
     */
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly array $upStatements,
        public readonly array $downStatements,
    ) {
    }
}
