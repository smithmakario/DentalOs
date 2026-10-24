<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class TenantDatabaseCleanup
{
    /**
     * Drop a tenant database when it already exists (common after migrate:fresh).
     */
    public function dropIfExists(TenantWithDatabase $tenant): void
    {
        $manager = $tenant->database()->manager();
        $databaseName = $tenant->database()->getName();

        if ($databaseName === null || $databaseName === '') {
            return;
        }

        if (! $manager->databaseExists($databaseName)) {
            return;
        }

        $manager->deleteDatabase($tenant);
    }

    /**
     * Drop leftover tenant databases matching the configured prefix.
     * Safe to run after migrate:fresh when tenant rows are gone but MySQL/SQLite DBs remain.
     *
     * @return list<string>
     */
    public function dropOrphanDatabases(): array
    {
        $prefix = (string) config('tenancy.database.prefix', '');
        $centralDatabase = (string) config('database.connections.'.config('tenancy.database.central_connection').'.database');

        if ($prefix === '') {
            return [];
        }

        $driver = DB::connection(config('tenancy.database.central_connection'))->getDriverName();

        $dropped = $driver === 'sqlite'
            ? $this->dropOrphanSqliteDatabases($prefix, $centralDatabase)
            : $this->dropOrphanSqlDatabases($prefix, $centralDatabase);

        return $dropped;
    }

    /**
     * @return list<string>
     */
    private function dropOrphanSqliteDatabases(string $prefix, string $centralDatabase): array
    {
        $dropped = [];

        foreach (File::files(database_path()) as $file) {
            $name = $file->getFilename();

            if (! str_starts_with($name, $prefix) || $name === $centralDatabase) {
                continue;
            }

            if (Tenant::query()->whereKey(substr($name, strlen($prefix)))->exists()) {
                continue;
            }

            File::delete($file->getPathname());
            $dropped[] = $name;
        }

        return $dropped;
    }

    /**
     * @return list<string>
     */
    private function dropOrphanSqlDatabases(string $prefix, string $centralDatabase): array
    {
        $connection = config('tenancy.database.central_connection');
        $schemas = DB::connection($connection)->select(
            'SELECT SCHEMA_NAME AS name FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME LIKE ?',
            [$prefix.'%'],
        );

        $dropped = [];

        foreach ($schemas as $schema) {
            $name = (string) $schema->name;

            if ($name === $centralDatabase || $name === '') {
                continue;
            }

            $tenantKey = substr($name, strlen($prefix));

            if (Tenant::query()->whereKey($tenantKey)->exists()) {
                continue;
            }

            DB::connection($connection)->statement('DROP DATABASE `'.str_replace('`', '``', $name).'`');
            $dropped[] = $name;
        }

        return $dropped;
    }
}
