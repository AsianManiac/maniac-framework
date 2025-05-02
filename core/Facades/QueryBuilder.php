<?php

namespace Core\Facades;

use Core\Foundation\Facade;

/**
 * Facade for query builder operations.
 *
 * @method static QueryBuilder from(string $table)
 * @method static QueryBuilder select(string|array $columns = ['*'])
 * @method static QueryBuilder where(string $column, string $operator, mixed $value = null, string $boolean = 'AND')
 * @method static QueryBuilder orWhere(string $column, string $operator, mixed $value = null)
 * @method static QueryBuilder join(string $table, string $first, string $operator, string $second, string $type = 'INNER')
 * @method static QueryBuilder leftJoin(string $table, string $first, string $operator, string $second)
 * @method static QueryBuilder rightJoin(string $table, string $first, string $operator, string $second)
 * @method static QueryBuilder groupBy(string|array $columns)
 * @method static QueryBuilder having(string $column, string $operator, mixed $value, string $boolean = 'AND')
 * @method static QueryBuilder limit(int $number)
 * @method static QueryBuilder offset(int $number)
 * @method static QueryBuilder orderBy(string $column, string $direction = 'ASC')
 * @method static QueryBuilder distinct()
 * @method static array get()
 * @method static array getColumn()
 * @method static array|null first()
 * @method static int count()
 * @method static bool exists()
 * @method static bool doesntExist()
 * @method static bool insert(array $data)
 * @method static int insertMany(array $records)
 * @method static string|false insertGetId(array $data)
 * @method static int update(array $data)
 * @method static int increment(string $column, int $amount = 1)
 * @method static int decrement(string $column, int $amount = 1)
 * @method static int delete()
 */
class QueryBuilder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'query_builder';
    }
}
