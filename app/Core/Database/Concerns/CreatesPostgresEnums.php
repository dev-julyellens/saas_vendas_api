<?php

declare(strict_types=1);

namespace App\Core\Database\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tipos ENUM nativos no PostgreSQL; demais drivers usam string + CHECK na migration.
 */
trait CreatesPostgresEnums
{
    protected function isPostgres(): bool
    {
        return Schema::getConnection()->getDriverName() === 'pgsql';
    }

    /**
     * @param  list<string>  $values
     */
    protected function createPostgresEnum(string $name, array $values): void
    {
        if (! $this->isPostgres()) {
            return;
        }

        $quoted = implode(', ', array_map(
            fn (string $v) => "'" . str_replace("'", "''", $v) . "'",
            $values
        ));

        DB::unprepared("
            DO \$\$ BEGIN
                CREATE TYPE {$name} AS ENUM ({$quoted});
            EXCEPTION
                WHEN duplicate_object THEN NULL;
            END \$\$;
        ");
    }

    protected function dropPostgresEnum(string $name): void
    {
        if (! $this->isPostgres()) {
            return;
        }

        DB::statement("DROP TYPE IF EXISTS {$name} CASCADE");
    }

    protected function addCheckConstraint(string $table, string $name, string $expression): void
    {
        if (! $this->isPostgres()) {
            return;
        }

        DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$expression})");
    }
}
