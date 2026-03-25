# Roles and Permissions

Small PHP package for role and permission checks backed by MySQL and PostgreSQL.

## Requirements

- PHP 8.1+
- PDO
- `pdo_mysql` to use the MySQL repositories
- `pdo_pgsql` to use the PostgreSQL repositories

## Installation

```bash
composer require jomisacu/roles-and-permissions
```

## Database setup

Create or select your database first, then apply the schema for your platform:

```bash
mysql -u root -p your_database < database/mysql.sql
psql your_database < database/postgres.sql
```

The schema files assume the target database is already selected.

## Versioned migrations

For production upgrades, use the versioned files in `database/migrations/mysql/` or `database/migrations/postgres/`.

- `0001_initial_schema.sql` bootstraps the original schema
- `0002_harden_relation_constraints.sql` adds the `updated_at` column on actor-role relations and the unique indexes that protect relation integrity

For an existing installation, apply only the pending files in order. Before running `0002_harden_relation_constraints.sql`, make sure your relation tables do not contain duplicate rows that would violate the new unique indexes.

## Migration generator

The package ships a utility command to scaffold migrations for plain PHP, Laravel, or Symfony projects.

```bash
vendor/bin/jomisacu-roles-and-permissions generate-migrations --target-framework=php --target-platform=mysql
```

You can also call the namespaced alias if you prefer:

```bash
vendor/bin/jomisacu-roles-and-permissions jomisacu:roles-and-permissions:generate-migrations --target-framework=symfony --target-platform=mysql --migrations-path=./migrations
```

Available options:

- `--table-prefix=_jomisacu_`
- `--target-framework=php|laravel|symfony`
- `--migrations-path=./some-path` required for `laravel` and `symfony`
- `--target-platform=mysql|postgres`

Notes:

- Plain PHP targets default to `./database/migrations/<platform>` when `migrations-path` is omitted.
- The generator can emit MySQL and PostgreSQL migration templates, and the package ships repository implementations for both platforms.

## Usage

```php
<?php

declare(strict_types=1);

use Jomisacu\RolesAndPermissions\ActorPermissionRelationRepositoryMySql;
use Jomisacu\RolesAndPermissions\ActorRoleRelationRepositoryMySql;
use Jomisacu\RolesAndPermissions\MySqlTableNames;
use Jomisacu\RolesAndPermissions\PermissionChecker;
use Jomisacu\RolesAndPermissions\ResourceMatcher;
use Jomisacu\RolesAndPermissions\RolePermissionRelationRepositoryMySql;

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=your_database', 'user', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tableNames = MySqlTableNames::fromPrefix('_jomisacu_');

$checker = new PermissionChecker(
    new ActorRoleRelationRepositoryMySql($pdo, $tableNames),
    new ResourceMatcher(),
    new ActorPermissionRelationRepositoryMySql($pdo, $tableNames),
    new RolePermissionRelationRepositoryMySql($pdo, $tableNames),
);

$canPublish = $checker->can(
    contextId: 'context-id',
    actorId: 'actor-id',
    permissionId: 'publish-posts',
    resource: 'blog::post::123',
);

// PostgreSQL repositories are also available:
// new ActorRoleRelationRepositoryPostgres($pdo, PostgresTableNames::fromPrefix('_jomisacu_'))
```

## Production notes

- `PermissionChecker` is stateless across calls, so it can be safely reused as a service without keeping stale authorization data in memory.
- The package ships consolidated schemas in `database/mysql.sql` and `database/postgres.sql`, plus versioned upgrade scripts in `database/migrations/mysql/` and `database/migrations/postgres/`.
- The SQL repositories raise package exceptions instead of leaking raw PDO errors: `RepositoryException`, `UniqueConstraintViolationException`, and `ForeignKeyConstraintViolationException`.
- If you generate migrations with a custom table prefix, pass the same prefix to the repositories with `MySqlTableNames::fromPrefix(...)` or `PostgresTableNames::fromPrefix(...)`.

## Tests

The test suite reads MySQL connection settings from `phpunit.xml.dist` by default.

```bash
vendor/bin/phpunit
```
