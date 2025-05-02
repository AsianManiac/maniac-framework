<?php

namespace Core\Facades;

use Core\Foundation\Facade;

/**
 * Facade for database operations.
 *
 * @method static \PDO getInstance()
 * @method static \PDOStatement|false query(string $sql, array $params = [])
 */
class DB extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'db';
    }
}
