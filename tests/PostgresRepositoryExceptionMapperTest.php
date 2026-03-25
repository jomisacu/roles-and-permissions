<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\ForeignKeyConstraintViolationException;
use Jomisacu\RolesAndPermissions\PostgresRepositoryExceptionMapper;
use Jomisacu\RolesAndPermissions\RepositoryException;
use Jomisacu\RolesAndPermissions\UniqueConstraintViolationException;
use PHPUnit\Framework\TestCase;

final class PostgresRepositoryExceptionMapperTest extends TestCase
{
    public function testMapsUniqueConstraintErrors(): void
    {
        $exception = new \PDOException('duplicate key value violates unique constraint');
        $exception->errorInfo = ['23505', 0, 'duplicate key value violates unique constraint'];

        $mapped = PostgresRepositoryExceptionMapper::from('create permission', 'acme_permissions', $exception);

        $this->assertInstanceOf(UniqueConstraintViolationException::class, $mapped);
    }

    public function testMapsForeignKeyErrors(): void
    {
        $exception = new \PDOException('insert or update on table violates foreign key constraint');
        $exception->errorInfo = ['23503', 0, 'insert or update on table violates foreign key constraint'];

        $mapped = PostgresRepositoryExceptionMapper::from('create actor role relation', 'acme_actor_role_relations', $exception);

        $this->assertInstanceOf(ForeignKeyConstraintViolationException::class, $mapped);
    }

    public function testMapsUnknownErrorsToGenericRepositoryException(): void
    {
        $exception = new \PDOException('syntax error');
        $exception->errorInfo = ['42601', 0, 'syntax error'];

        $mapped = PostgresRepositoryExceptionMapper::from('find permissions by context', 'acme_permissions', $exception);

        $this->assertInstanceOf(RepositoryException::class, $mapped);
        $this->assertNotInstanceOf(UniqueConstraintViolationException::class, $mapped);
        $this->assertNotInstanceOf(ForeignKeyConstraintViolationException::class, $mapped);
    }
}
