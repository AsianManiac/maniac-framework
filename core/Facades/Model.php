<?php

namespace Core\Facades;

use Core\Foundation\Facade;

/**
 * Facade for model operations.
 *
 * @method static \Core\Database\Model create(array $data)
 * @method static int insertMany(array $records)
 * @method static bool updateById(int $id, array $data)
 * @method static \Core\Database\Model updateOrCreate(array $attributes, array $values)
 * @method static bool delete(int $id)
 * @method static \Core\Database\Model|null find(int $id)
 * @method static \Core\Database\Model|null findById(int $id)
 * @method static array all()
 * @method static array get(array $conditions = [])
 * @method static array pluck(string $column, array $conditions = [])
 * @method static \Core\Database\Model|null random()
 * @method static array distinct(string $column)
 */
class Model extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'model';
    }
}
