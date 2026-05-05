<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Schema\Blueprint;

class MigrationStorageService
{
    protected $dbPath;
    protected $connectionName = 'migration_temp';

    public function __construct()
    {
        $this->dbPath = storage_path('app/migration_temp.sqlite');
    }

    /**
     * Setup the dynamic SQLite connection
     */
    public function setupConnection()
    {
        // Ensure the file exists
        if (!file_exists($this->dbPath)) {
            touch($this->dbPath);
        }

        Config::set("database.connections.{$this->connectionName}", [
            'driver' => 'sqlite',
            'url' => null,
            'database' => $this->dbPath,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge($this->connectionName);
    }

    /**
     * Get the connection name
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Clear the temporary storage
     */
    public function clear()
    {
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }
        $this->setupConnection();
    }

    /**
     * Ensure a table exists in SQLite
     */
    public function ensureTable(string $tableName, array $columns)
    {
        $this->setupConnection();

        if (!Schema::connection($this->connectionName)->hasTable($tableName)) {
            Schema::connection($this->connectionName)->create($tableName, function (Blueprint $table) use ($columns) {
                // We create all columns as TEXT to be safe and version-agnostic
                // Type conversion happens during transformation phase
                foreach ($columns as $column) {
                    $table->text($column)->nullable();
                }
                $table->index($columns[0]); // Index the first column (usually ID)
            });
        }
    }

    /**
     * Bulk insert data into SQLite
     */
    public function bulkInsert(string $tableName, array $rows)
    {
        if (empty($rows)) return;
        
        $this->setupConnection();
        
        // SQLite has limits on parameter count (usually 999 or 32766)
        // We'll chunk to stay safe
        $chunks = array_chunk($rows, 100);
        foreach ($chunks as $chunk) {
            DB::connection($this->connectionName)->table($tableName)->insert($chunk);
        }
    }
}
