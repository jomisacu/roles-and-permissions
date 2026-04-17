<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

/**
 * @property-read string $id
 * @property-read string $contextId
 * @property-read string $name
 * @property-read string|null $description
 */
final class Role
{
    use ReadsPrivateProperties;

    private string $id;
    private string $contextId;
    private string $name;
    private ?string $description;

    public function __construct(
        string $id,
        string $contextId,
        string $name,
        ?string $description
    ) {
        $this->id = $id;
        $this->contextId = $contextId;
        $this->name = $name;
        $this->description = $description;
    }
}
