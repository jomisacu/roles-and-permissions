<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions\Tests;

use Jomisacu\RolesAndPermissions\ForeignKeyConstraintViolationException;
use Jomisacu\RolesAndPermissions\MySqlRepositoryExceptionMapper;
use Jomisacu\RolesAndPermissions\RepositoryException;
use Jomisacu\RolesAndPermissions\UniqueConstraintViolationException;
use PHPUnit\Framework\TestCase;

final class MySqlRepositoryExceptionMapperTest extends TestCase
{
    public function testMapsDuplicateKeyErrors(): void
    {
        $exception = new \PDOException('Duplicate entry');
        $exception->errorInfo = ['23000', 1062, 'Duplicate entry'];

        $mapped = MySqlRepositoryExceptionMapper::from('create permission', '_jomisacu_permissions', $exception);

        $this->assertInstanceOf(UniqueConstraintViolationException::class, $mapped);
        $this->assertSame(1062, $mapped->getCode());
    }

    public function testMapsForeignKeyErrors(): void
    {
        $exception = new \PDOException('Cannot add or update a child row');
        $exception->errorInfo = ['23000', 1452, 'Cannot add or update a child row'];

        $mapped = MySqlRepositoryExceptionMapper::from('create actor role relation', '_jomisacu_actor_role_relations', $exception);

        $this->assertInstanceOf(ForeignKeyConstraintViolationException::class, $mapped);
        $this->assertSame(1452, $mapped->getCode());
    }

    public function testMapsUnknownErrorsToGenericRepositoryException(): void
    {
        $exception = new \PDOException('Syntax error');
        $exception->errorInfo = ['42000', 1064, 'Syntax error'];

        $mapped = MySqlRepositoryExceptionMapper::from('find permissions by context', '_jomisacu_permissions', $exception);

        $this->assertInstanceOf(RepositoryException::class, $mapped);
        $this->assertNotInstanceOf(UniqueConstraintViolationException::class, $mapped);
        $this->assertNotInstanceOf(ForeignKeyConstraintViolationException::class, $mapped);
        $this->assertSame(1064, $mapped->getCode());
    }
}
