<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class MigrationWriter
{
    /**
     * @param array<GeneratedMigrationFile> $files
     */
    public function write(array $files): void
    {
        foreach ($files as $file) {
            $directory = dirname($file->path);

            if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('Unable to create directory "%s".', $directory));
            }

            if (file_exists($file->path)) {
                throw new \RuntimeException(sprintf('Refusing to overwrite existing migration "%s".', $file->path));
            }

            if (file_put_contents($file->path, $file->contents) === false) {
                throw new \RuntimeException(sprintf('Unable to write migration "%s".', $file->path));
            }
        }
    }
}
