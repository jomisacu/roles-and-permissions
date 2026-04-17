<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class PostgresTableNames
{
    private string $permissions;
    private string $roles;
    private string $actorRoleRelations;
    private string $actorPermissionRelations;
    private string $rolePermissionRelations;

    public function __construct(
        string $permissions,
        string $roles,
        string $actorRoleRelations,
        string $actorPermissionRelations,
        string $rolePermissionRelations
    ) {
        $this->permissions = $permissions;
        $this->roles = $roles;
        $this->actorRoleRelations = $actorRoleRelations;
        $this->actorPermissionRelations = $actorPermissionRelations;
        $this->rolePermissionRelations = $rolePermissionRelations;

        self::validateIdentifier($this->permissions, 'permissions');
        self::validateIdentifier($this->roles, 'roles');
        self::validateIdentifier($this->actorRoleRelations, 'actorRoleRelations');
        self::validateIdentifier($this->actorPermissionRelations, 'actorPermissionRelations');
        self::validateIdentifier($this->rolePermissionRelations, 'rolePermissionRelations');
    }

    public static function default(): self
    {
        return self::fromPrefix('_jomisacu_');
    }

    public static function fromPrefix(string $tablePrefix): self
    {
        if (!preg_match('/^[A-Za-z0-9_]*$/', $tablePrefix)) {
            throw new \InvalidArgumentException('The PostgreSQL table prefix must contain only letters, numbers, and underscores.');
        }

        return new self(
            $tablePrefix . 'permissions',
            $tablePrefix . 'roles',
            $tablePrefix . 'actor_role_relations',
            $tablePrefix . 'actor_permission_relations',
            $tablePrefix . 'role_permission_relations',
        );
    }

    public function permissions(): string
    {
        return $this->permissions;
    }

    public function roles(): string
    {
        return $this->roles;
    }

    public function actorRoleRelations(): string
    {
        return $this->actorRoleRelations;
    }

    public function actorPermissionRelations(): string
    {
        return $this->actorPermissionRelations;
    }

    public function rolePermissionRelations(): string
    {
        return $this->rolePermissionRelations;
    }

    private static function validateIdentifier(string $identifier, string $name): void
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new \InvalidArgumentException(sprintf(
                'The PostgreSQL table name for %s must contain only letters, numbers, and underscores.',
                $name,
            ));
        }
    }
}
