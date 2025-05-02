<?php

namespace Core\Database;

use PDO;
use Closure;
use Core\Database\DB;
use Core\Database\Schema\Blueprint;

/**
 * Facade for schema operations.
 */
class Schema
{
    /**
     * Create a new table.
     *
     * @param string $table
     * @param Closure $callback
     * @return void
     */
    public static function create(string $table, Closure $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $blueprint->create();
    }

    /**
     * Modify an existing table.
     *
     * @param string $table
     * @param Closure $callback
     * @return void
     */
    public static function table(string $table, Closure $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $blueprint->build();
    }

    /**
     * Drop a table if it exists.
     *
     * @param string $table
     * @return void
     */
    public static function dropIfExists(string $table): void
    {
        $db = DB::getInstance();
        $db->exec("DROP TABLE IF EXISTS {$table}");
    }

    /**
     * Check if a table exists.
     *
     * @param string $table
     * @return bool
     */
    public static function hasTable(string $table): bool
    {
        $db = DB::getInstance();
        $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
        return $stmt->rowCount() > 0;
    }
}
