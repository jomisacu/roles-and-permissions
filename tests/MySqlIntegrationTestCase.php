<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

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
}
