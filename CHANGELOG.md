# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `CachedPermissionChecker` — in-memory decorator that caches permission/role check results within a single request lifecycle.
- `PermissionRepositoryInterface::findById()` — find a single permission by its ID (MySQL and Postgres implementations).
- `resource_hash` column (SHA-256) on `actor_permission_relations` and `role_permission_relations` tables for full-length unique constraint on resources (migration 0003).
- PHPStan static analysis at level 8 with zero errors.
- GitHub Actions CI workflow with tests across PHP 7.4–8.4 and static analysis.
- Angular frontend helpers with permission directives and a TypeScript permission service in `Angular/`.
- This changelog.

### Changed
- Unique indexes on permission relation tables now use `resource_hash` instead of `resource(191)` prefix, guaranteeing uniqueness over the full resource value.
- Integration tests are now self-contained: all MySQL tests call `ensureMySqlSchema()` to auto-create the schema if missing.
- Improved PHPStan type annotations across repository classes.
- Package runtime, tests, and development dependencies are now compatible with PHP 7.4.

### Migration guide
- Run migration `0003_add_resource_hash` to add the `resource_hash` column and update unique indexes. The migration automatically populates the hash for existing rows.
