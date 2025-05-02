<?php

namespace Core\Facades;

use Core\Foundation\Facade;

/**
 * Facade for schema operations.
 *
 * @method static void create(string $table, \Closure $callback)
 * @method static void table(string $table, \Closure $callback)
 * @method static void dropIfExists(string $table)
 * @method static bool hasTable(string $table)
 */
class Schema extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Core\Database\Schema::class;
    }
}
