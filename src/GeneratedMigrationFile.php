<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

/**
 * @property-read string $path
 * @property-read string $contents
 */
final class GeneratedMigrationFile
{
    use ReadsPrivateProperties;

    private string $path;
    private string $contents;

    public function __construct(
        string $path,
        string $contents
    ) {
        $this->path = $path;
        $this->contents = $contents;
    }
}
