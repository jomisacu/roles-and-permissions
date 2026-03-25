<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class GenerateMigrationsCommand
{
    private const COMMAND_NAMES = [
        'generate-migrations',
        'jomisacu:roles-and-permissions:generate-migrations',
    ];

    public function __construct(
        private readonly MigrationGenerator $migrationGenerator = new MigrationGenerator(),
        private readonly MigrationWriter $migrationWriter = new MigrationWriter(),
    ) {
    }

    /**
     * @param array<string> $argv
     */
    public function run(array $argv, string $currentWorkingDirectory): int
    {
        $commandName = $argv[1] ?? null;

        if ($commandName === null || in_array($commandName, ['-h', '--help', 'help'], true)) {
            $this->writeUsage();

            return 0;
        }

        if (!in_array($commandName, self::COMMAND_NAMES, true)) {
            fwrite(STDERR, sprintf("Unknown command \"%s\".\n\n", $commandName));
            $this->writeUsage(STDERR);

            return 1;
        }

        if (array_intersect(array_slice($argv, 2), ['-h', '--help', 'help']) !== []) {
            $this->writeUsage();

            return 0;
        }

        try {
            $options = MigrationGeneratorOptions::fromCommandArguments(array_slice($argv, 2), $currentWorkingDirectory);
            $files = $this->migrationGenerator->generate($options);
            $this->migrationWriter->write($files);
        } catch (\Throwable $throwable) {
            fwrite(STDERR, $throwable->getMessage() . PHP_EOL);

            return 1;
        }

        fwrite(STDOUT, sprintf(
            "Generated %d migration files in %s\n",
            count($files),
            $options->migrationsPath,
        ));

        foreach ($files as $file) {
            fwrite(STDOUT, sprintf("- %s\n", $file->path));
        }

        return 0;
    }

    /**
     * @param resource $stream
     */
    private function writeUsage($stream = STDOUT): void
    {
        fwrite($stream, <<<TEXT
Usage:
  vendor/bin/jomisacu-roles-and-permissions generate-migrations [options]
  vendor/bin/jomisacu-roles-and-permissions jomisacu:roles-and-permissions:generate-migrations [options]

Options:
  --table-prefix=_jomisacu_
  --target-framework=php|laravel|symfony
  --migrations-path=./some-path
  --target-platform=mysql|postgres

Notes:
  - migrations-path is required for laravel and symfony targets.
  - php targets default to ./database/migrations/<platform> when migrations-path is omitted.

TEXT);
    }
}
