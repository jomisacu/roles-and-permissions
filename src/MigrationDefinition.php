<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

/**
 * @property-read string $name
 * @property-read string $description
 * @property-read array<string> $upStatements
 * @property-read array<string> $downStatements
 */
final class MigrationDefinition
{
    use ReadsPrivateProperties;

    private string $name;
    private string $description;

    /** @var array<string> */
    private array $upStatements;

    /** @var array<string> */
    private array $downStatements;

    /**
     * @param array<string> $upStatements
     * @param array<string> $downStatements
     */
    public function __construct(
        string $name,
        string $description,
        array $upStatements,
        array $downStatements
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->upStatements = $upStatements;
        $this->downStatements = $downStatements;
    }
}
