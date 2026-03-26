<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\GenerateMigrationsCommand;
use Jomisacu\RolesAndPermissions\MigrationGenerator;
use Jomisacu\RolesAndPermissions\MigrationGeneratorOptions;
use Jomisacu\RolesAndPermissions\MigrationWriter;
use PHPUnit\Framework\TestCase;

final class MigrationGeneratorTest extends TestCase
{
    public function testGeneratesPlainPhpMySqlMigrationFiles(): void
    {
        $generator = new MigrationGenerator();
        $options = new MigrationGeneratorOptions(
            '_jomisacu_',
            'php',
            'mysql',
            '/tmp/generated-migrations',
        );

        $files = $generator->generate($options);

        $this->assertCount(3, $files);
        $this->assertSame('/tmp/generated-migrations/0001_initial_schema.sql', $files[0]->path);
        $this->assertSame('/tmp/generated-migrations/0002_harden_relation_constraints.sql', $files[1]->path);
        $this->assertSame('/tmp/generated-migrations/0003_add_resource_hash.sql', $files[2]->path);
        $this->assertStringContainsString('create table _jomisacu_permissions', $files[0]->contents);
        $this->assertStringContainsString('create unique index _jomisacu_actor_permission_relations_unique_rule', $files[1]->contents);
        $this->assertStringContainsString('resource_hash', $files[2]->contents);
    }

    public function testGeneratesLaravelMigrationFiles(): void
    {
        $generator = new MigrationGenerator();
        $options = new MigrationGeneratorOptions(
            'acme_',
            'laravel',
            'mysql',
            '/tmp/generated-laravel-migrations',
        );

        $files = $generator->generate($options);

        $this->assertCount(3, $files);
        $this->assertSame('/tmp/generated-laravel-migrations/2000_01_01_000001_initial_schema.php', $files[0]->path);
        $this->assertStringContainsString('use Illuminate\\Database\\Migrations\\Migration;', $files[0]->contents);
        $this->assertStringContainsString('create table acme_permissions', $files[0]->contents);
        $this->assertStringContainsString('DB::statement', $files[1]->contents);
        $this->assertStringContainsString('resource_hash', $files[2]->contents);
    }

    public function testGeneratesSymfonyPostgresMigrationFilesThroughCommand(): void
    {
        $tempDirectory = sys_get_temp_dir() . '/roles-and-permissions-' . uniqid('', true);
        $command = new GenerateMigrationsCommand(new MigrationGenerator(), new MigrationWriter());

        try {
            $exitCode = $command->run([
                'jomisacu-roles-and-permissions',
                'jomisacu:roles-and-permissions:generate-migrations',
                'target-framework=symfony',
                'target-platform=postgres',
                'table-prefix=acme_',
                'migrations-path=./migrations',
            ], $tempDirectory);

            $this->assertSame(0, $exitCode);
            $this->assertFileExists($tempDirectory . '/migrations/Version20000101000001.php');
            $this->assertFileExists($tempDirectory . '/migrations/Version20000101000002.php');
            $this->assertFileExists($tempDirectory . '/migrations/Version20000101000003.php');

            $firstMigration = file_get_contents($tempDirectory . '/migrations/Version20000101000001.php');
            $secondMigration = file_get_contents($tempDirectory . '/migrations/Version20000101000002.php');

            $this->assertNotFalse($firstMigration);
            $this->assertNotFalse($secondMigration);
            $this->assertStringContainsString('namespace DoctrineMigrations;', $firstMigration);
            $this->assertStringContainsString('create table acme_permissions', $firstMigration);
            $this->assertStringContainsString('postgresql', $firstMigration);
            $this->assertStringContainsString('create unique index acme_actor_permission_relations_unique_rule', $secondMigration);
        } finally {
            $this->removeDirectory($tempDirectory);
        }
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }

            unlink($item->getPathname());
        }

        rmdir($path);
    }
}
