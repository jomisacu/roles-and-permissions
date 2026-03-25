<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\MigrationTemplateFactory;
use Jomisacu\RolesAndPermissions\PostgresTableNames;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

abstract class PostgresIntegrationTestCase extends TestCase
{
    protected function createPdoConnection(): PDO
    {
        $_ENV['PG_TEST_HOST'] ??= '127.0.0.1';
        $_ENV['PG_TEST_PORT'] ??= '5433';
        $_ENV['PG_TEST_NAME'] ??= 'roles_and_permissions_pg_test';
        $_ENV['PG_TEST_USER'] ??= 'postgres';
        $_ENV['PG_TEST_PASSWORD'] ??= '';
        $_ENV['PG_TEST_ADMIN_DB'] ??= 'postgres';

        try {
            $this->ensurePostgresDatabaseExists();

            $pdo = new PDO(
                sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $_ENV['PG_TEST_HOST'],
                    $_ENV['PG_TEST_PORT'],
                    $_ENV['PG_TEST_NAME'],
                ),
                $_ENV['PG_TEST_USER'],
                $_ENV['PG_TEST_PASSWORD'],
            );
        } catch (PDOException $exception) {
            $this->markTestSkipped(sprintf('PostgreSQL test database unavailable: %s', $exception->getMessage()));
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    protected function ensurePostgresSchema(PDO $pdo, string $tablePrefix): void
    {
        $tableNames = PostgresTableNames::fromPrefix($tablePrefix);

        if ($this->tableExists($pdo, $tableNames->permissions())) {
            return;
        }

        $definitions = (new MigrationTemplateFactory())->build('postgres', $tablePrefix);

        foreach ($definitions as $definition) {
            foreach ($definition->upStatements as $statement) {
                $pdo->exec($statement);
            }
        }
    }

    protected function truncatePostgresSchema(PDO $pdo, string $tablePrefix): void
    {
        $tableNames = PostgresTableNames::fromPrefix($tablePrefix);
        $tables = array_values(array_filter([
            $this->tableExists($pdo, $tableNames->rolePermissionRelations()) ? $tableNames->rolePermissionRelations() : null,
            $this->tableExists($pdo, $tableNames->actorRoleRelations()) ? $tableNames->actorRoleRelations() : null,
            $this->tableExists($pdo, $tableNames->actorPermissionRelations()) ? $tableNames->actorPermissionRelations() : null,
            $this->tableExists($pdo, $tableNames->roles()) ? $tableNames->roles() : null,
            $this->tableExists($pdo, $tableNames->permissions()) ? $tableNames->permissions() : null,
        ]));

        if ($tables === []) {
            return;
        }

        $pdo->exec(sprintf('TRUNCATE TABLE %s RESTART IDENTITY CASCADE', implode(', ', $tables)));
    }

    private function ensurePostgresDatabaseExists(): void
    {
        $adminPdo = new PDO(
            sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $_ENV['PG_TEST_HOST'],
                $_ENV['PG_TEST_PORT'],
                $_ENV['PG_TEST_ADMIN_DB'],
            ),
            $_ENV['PG_TEST_USER'],
            $_ENV['PG_TEST_PASSWORD'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );

        $statement = $adminPdo->prepare('SELECT COUNT(*) FROM pg_database WHERE datname = :databaseName');
        $statement->execute(['databaseName' => $_ENV['PG_TEST_NAME']]);

        if ((int) $statement->fetchColumn() > 0) {
            return;
        }

        $adminPdo->exec(sprintf('CREATE DATABASE "%s"', str_replace('"', '""', $_ENV['PG_TEST_NAME'])));
    }

    private function tableExists(PDO $pdo, string $tableName): bool
    {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = current_schema() AND table_name = :tableName');
        $statement->execute(['tableName' => $tableName]);

        return (int) $statement->fetchColumn() > 0;
    }
}
