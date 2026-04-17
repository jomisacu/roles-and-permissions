<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

/**
 * @property-read string $tablePrefix
 * @property-read string $targetFramework
 * @property-read string $targetPlatform
 * @property-read string $migrationsPath
 */
final class MigrationGeneratorOptions
{
    use ReadsPrivateProperties;

    private string $tablePrefix;
    private string $targetFramework;
    private string $targetPlatform;
    private string $migrationsPath;

    public function __construct(
        string $tablePrefix,
        string $targetFramework,
        string $targetPlatform,
        string $migrationsPath
    ) {
        $this->tablePrefix = $tablePrefix;
        $this->targetFramework = $targetFramework;
        $this->targetPlatform = $targetPlatform;
        $this->migrationsPath = $migrationsPath;
    }

    /**
     * @param array<string> $arguments
     */
    public static function fromCommandArguments(array $arguments, string $currentWorkingDirectory): self
    {
        $options = [];

        foreach ($arguments as $argument) {
            $normalizedArgument = substr($argument, 0, 2) === '--' ? substr($argument, 2) : $argument;

            if (strpos($normalizedArgument, '=') === false) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid argument "%s". Use key=value or --key=value.', $argument),
                );
            }

            [$key, $value] = explode('=', $normalizedArgument, 2);
            $options[$key] = $value;
        }

        $tablePrefix = $options['table-prefix'] ?? '_jomisacu_';
        $targetFramework = strtolower($options['target-framework'] ?? 'php');
        $targetPlatform = strtolower($options['target-platform'] ?? 'mysql');

        self::validateTablePrefix($tablePrefix);
        self::validateTargetFramework($targetFramework);
        self::validateTargetPlatform($targetPlatform);

        $migrationsPath = $options['migrations-path'] ?? null;

        if (in_array($targetFramework, ['laravel', 'symfony'], true) && ($migrationsPath === null || trim($migrationsPath) === '')) {
            throw new \InvalidArgumentException('The migrations-path option is required for laravel and symfony targets.');
        }

        $resolvedMigrationsPath = self::resolveMigrationsPath(
            $migrationsPath ?? './database/migrations/' . $targetPlatform,
            $currentWorkingDirectory,
        );

        return new self($tablePrefix, $targetFramework, $targetPlatform, $resolvedMigrationsPath);
    }

    private static function validateTablePrefix(string $tablePrefix): void
    {
        if (!preg_match('/^[a-z0-9_]*$/', $tablePrefix)) {
            throw new \InvalidArgumentException('The table-prefix option must contain only lowercase letters, numbers, and underscores.');
        }
    }

    private static function validateTargetFramework(string $targetFramework): void
    {
        if (!in_array($targetFramework, ['php', 'laravel', 'symfony'], true)) {
            throw new \InvalidArgumentException('The target-framework option must be one of: php, laravel, symfony.');
        }
    }

    private static function validateTargetPlatform(string $targetPlatform): void
    {
        if (!in_array($targetPlatform, ['mysql', 'postgres'], true)) {
            throw new \InvalidArgumentException('The target-platform option must be one of: mysql, postgres.');
        }
    }

    private static function resolveMigrationsPath(string $migrationsPath, string $currentWorkingDirectory): string
    {
        $trimmedPath = rtrim($migrationsPath, DIRECTORY_SEPARATOR);

        if ($trimmedPath === '') {
            throw new \InvalidArgumentException('The migrations-path option cannot be empty.');
        }

        if (self::isAbsolutePath($trimmedPath)) {
            return $trimmedPath;
        }

        $relativePath = substr($trimmedPath, 0, 2) === './' ? substr($trimmedPath, 2) : $trimmedPath;
        $relativePath = substr($relativePath, 0, 2) === '.\\' ? substr($relativePath, 2) : $relativePath;

        return rtrim($currentWorkingDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relativePath;
    }

    private static function isAbsolutePath(string $path): bool
    {
        return strpos($path, DIRECTORY_SEPARATOR) === 0
            || preg_match('/^[A-Za-z]:\\\\/', $path) === 1
            || preg_match('/^[A-Za-z]:\//', $path) === 1;
    }
}
