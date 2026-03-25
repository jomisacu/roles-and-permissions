<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class MigrationTemplateFactory
{
    private const MIGRATION_BLUEPRINTS = [
        'initial_schema' => 'Bootstrap roles and permissions schema.',
        'harden_relation_constraints' => 'Add relation uniqueness and actor role update tracking.',
    ];

    /**
     * @return array<MigrationDefinition>
     */
    public function build(string $targetPlatform, string $tablePrefix): array
    {
        if (!in_array($targetPlatform, ['mysql', 'postgres'], true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported platform "%s".', $targetPlatform));
        }

        $tables = $this->buildTables($tablePrefix, $targetPlatform);
        $placeholders = $this->buildPlaceholders($tablePrefix, $tables);
        $definitions = [];

        foreach (self::MIGRATION_BLUEPRINTS as $name => $description) {
            $definitions[] = new MigrationDefinition(
                $name,
                $description,
                $this->loadStatements($targetPlatform, $name, 'up', $placeholders),
                $this->loadStatements($targetPlatform, $name, 'down', $placeholders),
            );
        }

        return $definitions;
    }

    /**
     * @param array<string, array<string, string>> $tables
     * @return array<string, string>
     */
    private function buildPlaceholders(string $tablePrefix, array $tables): array
    {
        $placeholders = [
            'table_prefix' => $tablePrefix,
        ];

        foreach ($tables as $group => $values) {
            foreach ($values as $key => $value) {
                $placeholders[$group . '_' . $key] = $value;
            }
        }

        return $placeholders;
    }

    /**
     * @param array<string, string> $placeholders
     * @return array<string>
     */
    private function loadStatements(string $targetPlatform, string $name, string $direction, array $placeholders): array
    {
        $templatePath = sprintf(
            '%s/resources/migration-templates/%s/%s.%s.sql.tpl',
            dirname(__DIR__),
            $targetPlatform,
            $name,
            $direction,
        );

        $templateContents = file_get_contents($templatePath);

        if ($templateContents === false) {
            throw new \RuntimeException(sprintf('Unable to load migration template "%s".', $templatePath));
        }

        $renderedTemplate = strtr(
            $templateContents,
            array_combine(
                array_map(static fn(string $key): string => '{{' . $key . '}}', array_keys($placeholders)),
                array_values($placeholders),
            ) ?: [],
        );

        return $this->parseStatements($renderedTemplate);
    }

    /**
     * @return array<string>
     */
    private function parseStatements(string $templateContents): array
    {
        $statements = preg_split('/;\s*(?:\R+|$)/', trim($templateContents));

        if ($statements === false) {
            throw new \RuntimeException('Unable to parse migration template statements.');
        }

        return array_values(array_filter(array_map(
            static fn(string $statement): string => trim($statement),
            $statements,
        ), static fn(string $statement): bool => $statement !== ''));
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function buildTables(string $tablePrefix, string $targetPlatform): array
    {
        $permissions = $tablePrefix . 'permissions';
        $actorPermissionRelations = $tablePrefix . 'actor_permission_relations';
        $roles = $tablePrefix . 'roles';
        $actorRoleRelations = $tablePrefix . 'actor_role_relations';
        $rolePermissionRelations = $tablePrefix . 'role_permission_relations';

        return [
            'permissions' => [
                'table' => $permissions,
                'context_index' => $this->identifier($permissions . '_context_id_index', $targetPlatform),
            ],
            'actor_permission_relations' => [
                'table' => $actorPermissionRelations,
                'permission_fk' => $this->identifier($actorPermissionRelations . '_' . $permissions . '_id_fk', $targetPlatform),
                'context_actor_index' => $this->identifier($actorPermissionRelations . '_context_id_actor_id_index', $targetPlatform),
                'unique_rule_index' => $this->identifier($actorPermissionRelations . '_unique_rule', $targetPlatform),
            ],
            'roles' => [
                'table' => $roles,
                'context_index' => $this->identifier($roles . '_context_id_index', $targetPlatform),
            ],
            'actor_role_relations' => [
                'table' => $actorRoleRelations,
                'role_fk' => $this->identifier($actorRoleRelations . '_' . $roles . '_id_fk', $targetPlatform),
                'context_role_actor_index' => $this->identifier($actorRoleRelations . '_context_id_role_id_actor_id_index', $targetPlatform),
                'unique_assignment_index' => $this->identifier($actorRoleRelations . '_unique_assignment', $targetPlatform),
            ],
            'role_permission_relations' => [
                'table' => $rolePermissionRelations,
                'permission_fk' => $this->identifier($rolePermissionRelations . '_' . $permissions . '_id_fk', $targetPlatform),
                'role_fk' => $this->identifier($rolePermissionRelations . '_' . $roles . '_id_fk', $targetPlatform),
                'context_role_index' => $this->identifier($rolePermissionRelations . '_context_id_role_id_index', $targetPlatform),
                'unique_rule_index' => $this->identifier($rolePermissionRelations . '_unique_rule', $targetPlatform),
            ],
        ];
    }

    private function identifier(string $value, string $targetPlatform): string
    {
        $maxLength = $targetPlatform === 'mysql' ? 64 : 63;

        if (strlen($value) <= $maxLength) {
            return $value;
        }

        return substr($value, 0, $maxLength - 9) . '_' . substr(sha1($value), 0, 8);
    }
}
