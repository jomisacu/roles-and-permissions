<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\MigrationTemplateFactory;
use Jomisacu\RolesAndPermissions\MySqlTableNames;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

abstract class MySqlIntegrationTestCase extends TestCase
{
    protected function createPdoConnection(): PDO
    {
        $_ENV['DB_TEST_HOST'] ??= '127.0.0.1';
        $_ENV['DB_TEST_PORT'] ??= '3308';
        $_ENV['DB_TEST_NAME'] ??= 'roles_and_permissions';
        $_ENV['DB_TEST_USER'] ??= 'root';
        $_ENV['DB_TEST_PASSWORD'] ??= '';

        try {
            $pdo = new PDO(
                sprintf(
                    'mysql:host=%s;port=%s;dbname=%s',
                    $_ENV['DB_TEST_HOST'],
                    $_ENV['DB_TEST_PORT'],
                    $_ENV['DB_TEST_NAME'],
                ),
                $_ENV['DB_TEST_USER'],
                $_ENV['DB_TEST_PASSWORD'],
            );
        } catch (PDOException $exception) {
            $this->markTestSkipped(sprintf('MySQL test database unavailable: %s', $exception->getMessage()));
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    protected function ensureMySqlSchema(PDO $pdo, string $tablePrefix): void
    {
        $tableNames = MySqlTableNames::fromPrefix($tablePrefix);

        if ($this->tableExists($pdo, $tableNames->permissions())) {
            return;
        }

        $definitions = (new MigrationTemplateFactory())->build('mysql', $tablePrefix);

        foreach ($definitions as $definition) {
            foreach ($definition->upStatements as $statement) {
                $pdo->exec($statement);
            }
        }
    }

    protected function truncateMySqlSchema(PDO $pdo, string $tablePrefix): void
    {
        $tableNames = MySqlTableNames::fromPrefix($tablePrefix);
        $tables = [
            $tableNames->rolePermissionRelations(),
            $tableNames->actorRoleRelations(),
            $tableNames->actorPermissionRelations(),
            $tableNames->roles(),
            $tableNames->permissions(),
        ];

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($tables as $table) {
                if ($this->tableExists($pdo, $table)) {
                    $pdo->exec(sprintf('TRUNCATE TABLE %s', $table));
                }
            }
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    private function tableExists(PDO $pdo, string $tableName): bool
    {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tableName');
        $statement->execute(['tableName' => $tableName]);

        return (int) $statement->fetchColumn() > 0;
    }
}
