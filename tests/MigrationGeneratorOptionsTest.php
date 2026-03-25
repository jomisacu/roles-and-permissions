<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\MigrationGeneratorOptions;
use PHPUnit\Framework\TestCase;

final class MigrationGeneratorOptionsTest extends TestCase
{
    public function testPhpTargetUsesDefaultPathForPostgres(): void
    {
        $options = MigrationGeneratorOptions::fromCommandArguments(
            ['target-platform=postgres'],
            '/tmp/roles-and-permissions',
        );

        $this->assertSame('_jomisacu_', $options->tablePrefix);
        $this->assertSame('php', $options->targetFramework);
        $this->assertSame('postgres', $options->targetPlatform);
        $this->assertSame('/tmp/roles-and-permissions/database/migrations/postgres', $options->migrationsPath);
    }

    public function testTargetPlatformRejectsTypos(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The target-platform option must be one of: mysql, postgres.');

        MigrationGeneratorOptions::fromCommandArguments(
            ['target-platform=posgres'],
            '/tmp/roles-and-permissions',
        );
    }

    public function testLaravelTargetRequiresMigrationPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The migrations-path option is required for laravel and symfony targets.');

        MigrationGeneratorOptions::fromCommandArguments(
            ['target-framework=laravel'],
            '/tmp/roles-and-permissions',
        );
    }

    public function testTablePrefixValidationRejectsUnsafeCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table-prefix option must contain only lowercase letters, numbers, and underscores.');

        MigrationGeneratorOptions::fromCommandArguments(
            ['table-prefix=acme-prefix'],
            '/tmp/roles-and-permissions',
        );
    }
}
