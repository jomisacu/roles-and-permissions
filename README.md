# Roles and Permissions

[![CI](https://github.com/jomisacu/roles-and-permissions/actions/workflows/ci.yml/badge.svg)](https://github.com/jomisacu/roles-and-permissions/actions)

A framework-agnostic PHP library for managing roles and permissions with multi-tenant support. Zero runtime dependencies — just PHP 8.1+ and PDO.

## Features

- **Multi-tenant** — all data is scoped by a `contextId`, so one installation serves multiple apps, orgs, or workspaces.
- **Resource-level permissions** — grant or deny permissions on specific resources with wildcard matching (`blog::post::*`).
- **Permission negation** — explicitly deny a permission, even if granted through a role.
- **MySQL and PostgreSQL** support.
- **Framework-agnostic** — works with Laravel, Symfony, or plain PHP. Migration generators included for all three.
- **In-memory caching** — optional `CachedPermissionChecker` decorator to avoid repeated queries within a request.

## Requirements

- PHP 8.1+
- `ext-pdo`
- `ext-pdo_mysql` (for MySQL) or `ext-pdo_pgsql` (for PostgreSQL)

## Installation

```bash
composer require jomisacu/roles-and-permissions
```

## Database Setup

Generate migrations for your platform and framework:

```bash
# Plain PHP + MySQL (default)
vendor/bin/jomisacu-roles-and-permissions generate-migrations

# Laravel + MySQL
vendor/bin/jomisacu-roles-and-permissions generate-migrations \
  --target-framework=laravel \
  --target-platform=mysql \
  --migrations-path=database/migrations

# Symfony + PostgreSQL
vendor/bin/jomisacu-roles-and-permissions generate-migrations \
  --target-framework=symfony \
  --target-platform=postgres \
  --migrations-path=migrations

# Custom table prefix
vendor/bin/jomisacu-roles-and-permissions generate-migrations \
  --table-prefix=myapp_
```

Then run the generated migrations with your usual tool (`php artisan migrate`, `doctrine:migrations:migrate`, etc.).

Alternatively, apply the consolidated schema directly:

```bash
mysql -u root -p your_database < database/mysql.sql
psql your_database < database/postgres.sql
```

## Quick Start

```php
use Jomisacu\RolesAndPermissions\{
    PermissionChecker,
    CachedPermissionChecker,
    ResourceMatcher,
    ActorRoleRelationRepositoryMySql,
    ActorPermissionRelationRepositoryMySql,
    RolePermissionRelationRepositoryMySql,
};

$pdo = new PDO('mysql:host=127.0.0.1;dbname=myapp', 'user', 'pass');

// Permission checker (wrap with CachedPermissionChecker for production)
$checker = new CachedPermissionChecker(
    new PermissionChecker(
        new ActorRoleRelationRepositoryMySql($pdo),
        new ResourceMatcher(),
        new ActorPermissionRelationRepositoryMySql($pdo),
        new RolePermissionRelationRepositoryMySql($pdo),
    )
);
```

## Checking Permissions

```php
$contextId = 'tenant-uuid';
$actorId   = 'user-uuid';

// Single permission check
$checker->can($contextId, $actorId, 'edit-posts', 'blog::post::123');

// Any of several permissions
$checker->canAny($contextId, $actorId, ['edit-posts', 'delete-posts'], 'blog::post::123');

// All permissions required
$checker->canAll($contextId, $actorId, ['edit-posts', 'publish-posts'], 'blog::post::123');
```

## Checking Roles

```php
$checker->is($contextId, $actorId, 'admin');
$checker->isAny($contextId, $actorId, ['admin', 'editor']);
$checker->isAll($contextId, $actorId, ['admin', 'editor']);
```

## Resource Wildcards

Resources use `::` as separator. The `*` wildcard matches any single segment, and `**` matches any number of remaining segments.

| Given permission | Requested resource | Match |
|---|---|---|
| `blog::post::*` | `blog::post::123` | ✅ |
| `blog::*::edit` | `blog::post::edit` | ✅ |
| `blog::**` | `blog::post::123::comments` | ✅ |
| `blog::post::*` | `blog::post::123::comments` | ❌ |

## Custom Table Prefix

Pass the same prefix to both the migration generator and the repositories:

```php
use Jomisacu\RolesAndPermissions\MySqlTableNames;

$tableNames = MySqlTableNames::fromPrefix('myapp_');

$permissions = new PermissionRepositoryMySql($pdo, $tableNames);
$roles       = new RoleRepositoryMySql($pdo, $tableNames);
// ... same for all other repositories
```

## PostgreSQL

Replace `MySql` classes with their `Postgres` equivalents:

```php
use Jomisacu\RolesAndPermissions\{
    PermissionRepositoryPostgres,
    RoleRepositoryPostgres,
    PostgresTableNames,
};

$tableNames = PostgresTableNames::fromPrefix('myapp_');
$permissions = new PermissionRepositoryPostgres($pdo, $tableNames);
```

## Production Notes

- Wrap `PermissionChecker` with `CachedPermissionChecker` to avoid repeated DB queries within a single request.
- `PermissionChecker` is stateless across calls, so it can be safely reused as a long-lived service.
- SQL repositories raise domain exceptions (`RepositoryException`, `UniqueConstraintViolationException`, `ForeignKeyConstraintViolationException`) instead of leaking raw PDO errors.

## Tests

```bash
vendor/bin/phpunit        # unit + integration tests
vendor/bin/phpstan analyse # static analysis (level 8)
```

Integration tests require MySQL and PostgreSQL. Connection settings are in `phpunit.xml.dist`. Tests that cannot connect to a database are automatically skipped.

## License

MIT — see [LICENSE](LICENSE).
