<?php

namespace Core\Facades;

use Core\Foundation\Facade;

/**
 * Facade for database operations.
 *
 * @method static \PDO getInstance()
 * @method static \PDOStatement|bool query(string $sql, array $params = [])
 * @method static mixed transaction(callable $callback)
 * @method static void beginTransaction()
 * @method static void commit()
 * @method static void rollBack()
 * @method static string lastInsertId()
 */
class DB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'db';
    }
}
