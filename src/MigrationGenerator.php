<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

final class MigrationGenerator
{
    public function __construct(
        private readonly MigrationTemplateFactory $migrationTemplateFactory = new MigrationTemplateFactory(),
    ) {
    }

    /**
     * @return array<GeneratedMigrationFile>
     */
    public function generate(MigrationGeneratorOptions $options): array
    {
        $definitions = $this->migrationTemplateFactory->build($options->targetPlatform, $options->tablePrefix);

        return match ($options->targetFramework) {
            'php' => $this->generateSqlFiles($options->migrationsPath, $definitions),
            'laravel' => $this->generateLaravelFiles($options->migrationsPath, $definitions),
            'symfony' => $this->generateSymfonyFiles($options->migrationsPath, $definitions, $options->targetPlatform),
            default => throw new \InvalidArgumentException(sprintf('Unsupported framework "%s".', $options->targetFramework)),
        };
    }

    /**
     * @param array<MigrationDefinition> $definitions
     * @return array<GeneratedMigrationFile>
     */
    private function generateSqlFiles(string $migrationsPath, array $definitions): array
    {
        $files = [];

        foreach ($definitions as $position => $definition) {
            $files[] = new GeneratedMigrationFile(
                sprintf('%s/%04d_%s.sql', $migrationsPath, $position + 1, $definition->name),
                $this->renderSqlStatements($definition->upStatements),
            );
        }

        return $files;
    }

    /**
     * @param array<MigrationDefinition> $definitions
     * @return array<GeneratedMigrationFile>
     */
    private function generateLaravelFiles(string $migrationsPath, array $definitions): array
    {
        $files = [];

        foreach ($definitions as $position => $definition) {
            $sequence = sprintf('%06d', $position + 1);

            $files[] = new GeneratedMigrationFile(
                sprintf('%s/2000_01_01_%s_%s.php', $migrationsPath, $sequence, $definition->name),
                $this->renderLaravelMigration($definition),
            );
        }

        return $files;
    }

    /**
     * @param array<MigrationDefinition> $definitions
     * @return array<GeneratedMigrationFile>
     */
    private function generateSymfonyFiles(string $migrationsPath, array $definitions, string $targetPlatform): array
    {
        $files = [];

        foreach ($definitions as $position => $definition) {
            $version = sprintf('200001010000%02d', $position + 1);

            $files[] = new GeneratedMigrationFile(
                sprintf('%s/Version%s.php', $migrationsPath, $version),
                $this->renderSymfonyMigration($definition, $version, $targetPlatform),
            );
        }

        return $files;
    }

    /**
     * @param array<string> $statements
     */
    private function renderSqlStatements(array $statements): string
    {
        return implode("\n\n", array_map(
            static fn(string $statement): string => rtrim($statement, "\n;") . ';',
            $statements,
        )) . "\n";
    }

    private function renderLaravelMigration(MigrationDefinition $definition): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
{$this->renderLaravelStatements($definition->upStatements)}
    }

    public function down(): void
    {
{$this->renderLaravelStatements($definition->downStatements)}
    }
};
PHP;
    }

    private function renderSymfonyMigration(MigrationDefinition $definition, string $version, string $targetPlatform): string
    {
        $platformName = $targetPlatform === 'postgres' ? 'postgresql' : $targetPlatform;
        $upStatements = $this->renderSymfonyStatements($definition->upStatements);
        $downStatements = $this->renderSymfonyStatements($definition->downStatements);

        return <<<PHP
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version{$version} extends AbstractMigration
{
    public function getDescription(): string
    {
        return '{$definition->description}';
    }

    public function up(Schema \$schema): void
    {
        \$this->abortIf(
            \$this->connection->getDatabasePlatform()->getName() !== '{$platformName}',
            'This migration can only run safely on {$platformName}.',
        );

{$upStatements}
    }

    public function down(Schema \$schema): void
    {
        \$this->abortIf(
            \$this->connection->getDatabasePlatform()->getName() !== '{$platformName}',
            'This migration can only run safely on {$platformName}.',
        );

{$downStatements}
    }
}
PHP;
    }

    /**
     * @param array<string> $statements
     */
    private function renderLaravelStatements(array $statements): string
    {
        $blocks = array_map(function (string $statement): string {
            return <<<PHP
        DB::statement(<<<'SQL'
{$statement}
SQL);
PHP;
        }, $statements);

        return implode("\n\n", $blocks) . "\n";
    }

    /**
     * @param array<string> $statements
     */
    private function renderSymfonyStatements(array $statements): string
    {
        $blocks = array_map(function (string $statement): string {
            return <<<PHP
        \$this->addSql(<<<'SQL'
{$statement}
SQL);
PHP;
        }, $statements);

        return implode("\n\n", $blocks) . "\n";
    }
}
