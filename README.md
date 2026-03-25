# Roles and Permissions

Small PHP package for role and permission checks backed by MySQL.

## Requirements

- PHP 8.1+
- PDO
- `pdo_mysql` to use the MySQL repositories

## Installation

```bash
composer require jomisacu/roles-and-permissions
```

## MySQL setup

Create or select your database first, then apply the latest schema:

```bash
mysql -u root -p your_database < database/mysql.sql
```

The schema file assumes the target database is already selected.

## Versioned migrations

For production upgrades, use the versioned files in `database/migrations/mysql/`.

- `0001_initial_schema.sql` bootstraps the original schema
- `0002_harden_relation_constraints.sql` adds the `updated_at` column on actor-role relations and the unique indexes that protect relation integrity

For an existing installation, apply only the pending files in order. Before running `0002_harden_relation_constraints.sql`, make sure your relation tables do not contain duplicate rows that would violate the new unique indexes.

## Usage

```php
<?php

declare(strict_types=1);

use Jomisacu\RolesAndPermissions\ActorPermissionRelationRepositoryMySql;
use Jomisacu\RolesAndPermissions\ActorRoleRelationRepositoryMySql;
use Jomisacu\RolesAndPermissions\PermissionChecker;
use Jomisacu\RolesAndPermissions\ResourceMatcher;
use Jomisacu\RolesAndPermissions\RolePermissionRelationRepositoryMySql;

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=your_database', 'user', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$checker = new PermissionChecker(
    new ActorRoleRelationRepositoryMySql($pdo),
    new ResourceMatcher(),
    new ActorPermissionRelationRepositoryMySql($pdo),
    new RolePermissionRelationRepositoryMySql($pdo),
);

$canPublish = $checker->can(
    contextId: 'context-id',
    actorId: 'actor-id',
    permissionId: 'publish-posts',
    resource: 'blog::post::123',
);
```

## Production notes

- `PermissionChecker` is stateless across calls, so it can be safely reused as a service without keeping stale authorization data in memory.
- The package ships a consolidated schema in `database/mysql.sql` and versioned upgrade scripts in `database/migrations/mysql/`.
- The MySQL repositories raise package exceptions instead of leaking raw PDO errors: `RepositoryException`, `UniqueConstraintViolationException`, and `ForeignKeyConstraintViolationException`.

## Tests

The test suite reads MySQL connection settings from `phpunit.xml.dist` by default.

```bash
vendor/bin/phpunit
```
