<?php

namespace Core\Mvc;

use PDO;
use Exception;
use Core\Database\DB;
use Core\Database\QueryBuilder;
use Core\Logging\Log;

/**
 * Base model class for database interactions.
 *
 * Provides an ORM for CRUD operations, mass assignment, relationships, and query building.
 */
abstract class Model
{
    /**
     * The query builder instance (singleton per request).
     *
     * @var QueryBuilder|null
     */
    protected static ?QueryBuilder $queryBuilderInstance = null;

    /**
     * Attributes that are mass assignable.
     *
     * @var string[]
     */
    protected array $fillable = [];

    /**
     * The table associated with the model. Guessed if not set.
     *
     * @var string|null
     */
    protected ?string $table = null;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Attributes container for the current instance.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Original attributes loaded from the database (for dirty checking).
     *
     * @var array
     */
    protected array $original = [];

    /**
     * Indicates if the model exists in the database.
     *
     * @var bool
     */
    public bool $exists = false;

    /**
     * Create a new model instance.
     *
     * @param array $attributes Attributes to fill the model
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Get the query builder instance for the model.
     *
     * @return QueryBuilder
     */
    protected static function getQueryBuilder(): QueryBuilder
    {
        if (static::$queryBuilderInstance === null) {
            static::$queryBuilderInstance = new QueryBuilder(DB::getInstance());
        }
        $instance = new static();
        static::$queryBuilderInstance->from($instance->getTable());
        return static::$queryBuilderInstance;
    }

    // --- Static Query Methods ---

    /**
     * Begin a fluent query against the model.
     *
     * @return QueryBuilder
     */
    public static function query(): QueryBuilder
    {
        return static::getQueryBuilder();
    }

    /**
     * Select specific columns for the query.
     *
     * @param string|array $columns Columns to select
     * @return QueryBuilder
     */
    public static function select(string|array $columns = ['*']): QueryBuilder
    {
        return static::getQueryBuilder()->select($columns);
    }

    /**
     * Retrieve all records for the model.
     *
     * @param array $columns Columns to select
     * @return static[]
     */
    public static function all(array $columns = ['*']): array
    {
        return static::hydrate(static::getQueryBuilder()->select($columns)->get());
    }

    /**
     * Find a record by its primary key.
     *
     * @param int|string $id Primary key value
     * @param array $columns Columns to select
     * @return static|null
     */
    public static function find(int|string $id, array $columns = ['*']): ?self
    {
        $result = static::getQueryBuilder()->where('id', '=', $id)->select($columns)->first();
        return $result ? static::hydrate([$result])[0] : null;
    }

    /**
     * Find a record by its primary key or throw an exception.
     *
     * @param int|string $id Primary key value
     * @param array $columns Columns to select
     * @return static
     * @throws Exception
     */
    public static function findOrFail(int|string $id, array $columns = ['*']): self
    {
        $model = static::find($id, $columns);
        if (!$model) {
            throw new Exception(static::class . " with ID {$id} not found.");
        }
        return $model;
    }

    /**
     * Add a where clause to the query.
     *
     * @param string $column Column name
     * @param string $operator Operator (e.g., '=', '<', '>')
     * @param mixed $value Value to compare
     * @return QueryBuilder
     */
    public static function where(string $column, string $operator, mixed $value = null): QueryBuilder
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        return static::getQueryBuilder()->where($column, $operator, $value);
    }

    /**
     * Add a LIKE where clause to the query.
     *
     * @param string $column Column name
     * @param string $value Value to match
     * @return QueryBuilder
     */
    public static function whereLike(string $column, string $value): QueryBuilder
    {
        return static::getQueryBuilder()->where($column, 'LIKE', $value);
    }

    /**
     * Paginate the query results.
     *
     * @param int $perPage Number of items per page
     * @param array $columns Columns to select
     * @param string $pageName Query string parameter name for page
     * @param int|null $page Current page number
     * @return array
     */
    public static function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): array
    {
        $page = $page ?? (int)($_GET[$pageName] ?? 1);
        $page = max(1, $page);

        $query = static::getQueryBuilder()->select($columns);
        $totalQuery = clone $query;
        $total = $totalQuery->count();

        $results = $query->limit($perPage)->offset(($page - 1) * $perPage)->get();

        return [
            'data' => static::hydrate($results),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Create a new record in the database.
     *
     * @param array $data Attributes to insert
     * @return static
     */
    public static function create(array $data): self
    {
        $instance = new static($data);
        $instance->save();
        return $instance;
    }

    /**
     * Insert multiple records into the database.
     *
     * @param array $records Array of attribute sets
     * @return int Number of inserted records
     */
    public static function insertMany(array $records): int
    {
        if (empty($records)) {
            return 0;
        }
        return static::getQueryBuilder()->insertMany($records);
    }

    /**
     * Update a record by ID.
     *
     * @param int|string $id Primary key value
     * @param array $data Attributes to update
     * @return bool
     */
    public static function updateById(int|string $id, array $data): bool
    {
        $instance = static::find($id);
        if (!$instance) {
            return false;
        }
        return $instance->fill($data)->save();
    }

    /**
     * Update or create a record based on attributes.
     *
     * @param array $attributes Attributes to match
     * @param array $values Attributes to update or create
     * @return static
     */
    public static function updateOrCreate(array $attributes, array $values): self
    {
        $instance = static::where($attributes)->first();
        if ($instance) {
            /** @var \Core\Mvc\Model $instance */
            $instance->fill($values)->save();
            return $instance;
        }
        return static::create(array_merge($attributes, $values));
    }

    /**
     * Find or create a record based on attributes.
     *
     * @param array $attributes Attributes to match
     * @param array $values Additional attributes for creation
     * @return static
     */
    public static function firstOrCreate(array $attributes, array $values = []): self
    {
        /** @var \Core\Mvc\Model $instance */
        $instance = static::where($attributes)->first();
        if ($instance) {
            return $instance;
        }
        return static::create(array_merge($attributes, $values));
    }

    /**
     * Find or instantiate a new model instance.
     *
     * @param array $attributes Attributes to match
     * @param array $values Additional attributes for instantiation
     * @return static
     */
    public static function firstOrNew(array $attributes, array $values = []): self
    {
        /** @var \Core\Mvc\Model $instance */
        $instance = static::where($attributes)->first();
        if ($instance) {
            return $instance;
        }
        return new static(array_merge($attributes, $values));
    }

    /**
     * Pluck a single column from the results.
     *
     * @param string $column Column to pluck
     * @param array $conditions Where conditions
     * @return array
     */
    public static function pluck(string $column, array $conditions = []): array
    {
        $query = static::getQueryBuilder()->select($column);
        foreach ($conditions as $key => $value) {
            $query->where($key, '=', $value);
        }
        return $query->getColumn();
    }

    /**
     * Get a random record.
     *
     * @return static|null
     */
    public static function random(): ?self
    {
        $result = static::getQueryBuilder()->orderBy('RAND()')->first();
        return $result ? static::hydrate([$result])[0] : null;
    }

    /**
     * Get distinct values for a column.
     *
     * @param string $column Column to select distinct values
     * @return array
     */
    public static function distinct(string $column): array
    {
        return static::getQueryBuilder()->select($column)->distinct()->getColumn();
    }

    /**
     * Increment a column's value by a given amount.
     *
     * @param string $column Column to increment
     * @param int $amount Amount to increment by
     * @return int Number of affected rows
     */
    public static function increment(string $column, int $amount = 1): int
    {
        return static::getQueryBuilder()->increment($column, $amount);
    }

    /**
     * Decrement a column's value by a given amount.
     *
     * @param string $column Column to decrement
     * @param int $amount Amount to decrement by
     * @return int Number of affected rows
     */
    public static function decrement(string $column, int $amount = 1): int
    {
        return static::getQueryBuilder()->decrement($column, $amount);
    }

    // --- Instance Methods ---

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes Attributes to fill
     * @return self
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    /**
     * Save the model to the database.
     *
     * @return bool
     */
    public function save(): bool
    {
        $query = static::getQueryBuilder();
        $attributesToSave = $this->getDirtyAttributes();

        if (empty($attributesToSave)) {
            return true;
        }

        try {
            if ($this->exists) {
                if (count($attributesToSave) > 0) {
                    $result = $query->where($this->primaryKey, '=', $this->getKey())->update($attributesToSave);
                    if ($result) {
                        $this->syncOriginal();
                    }
                    return (bool) $result;
                }
                return true;
            } else {
                $insertAttributes = array_intersect_key($this->attributes, array_flip($this->fillable));
                $id = $query->insertGetId($insertAttributes);
                if ($id) {
                    $this->setAttribute($this->primaryKey, $id);
                    $this->exists = true;
                    $this->syncOriginal();
                    return true;
                }
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Failed to save model in {$this->getTable()}: {$e->getMessage()}", [
                'attributes' => $attributesToSave,
                'exception' => $e
            ]);
            throw new Exception("Failed to save model: {$e->getMessage()}");
        }
    }

    /**
     * Update the model instance with new attributes.
     *
     * @param array $attributes Attributes to update
     * @return bool
     */
    public function update(array $attributes): bool
    {
        if (!$this->exists) {
            return false;
        }
        $this->fill($attributes);
        return $this->save();
    }

    /**
     * Delete the model from the database.
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return true;
        }
        $query = static::getQueryBuilder();
        $result = $query->where($this->primaryKey, '=', $this->getKey())->delete();
        if ($result) {
            $this->exists = false;
        }
        return (bool) $result;
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    // --- Attribute Handling ---

    /**
     * Set an attribute on the model.
     *
     * @param string $key Attribute name
     * @param mixed $value Attribute value
     * @return void
     */
    public function setAttribute(string $key, mixed $value): void
    {
        $setterMethod = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute';
        if (method_exists($this, $setterMethod)) {
            $this->$setterMethod($value);
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key Attribute name
     * @return mixed
     */
    public function getAttribute(string $key): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            $getterMethod = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute';
            if (method_exists($this, $getterMethod)) {
                return $this->$getterMethod($this->attributes[$key]);
            }
            return $this->attributes[$key];
        }
        return null;
    }

    /**
     * Get the primary key value.
     *
     * @return mixed
     */
    public function getKey(): mixed
    {
        return $this->getAttribute($this->primaryKey);
    }

    /**
     * Get changed attributes for dirty checking.
     *
     * @return array
     */
    protected function getDirtyAttributes(): array
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                if ($this->isFillable($key)) {
                    $dirty[$key] = $value;
                }
            }
        }
        return $dirty;
    }

    /**
     * Update the original attribute snapshot.
     *
     * @return void
     */
    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }

    /**
     * Dynamically retrieve attributes.
     *
     * @param string $key Attribute name
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes.
     *
     * @param string $key Attribute name
     * @param mixed $value Attribute value
     * @return void
     * @throws Exception
     */
    public function __set(string $key, mixed $value): void
    {
        if ($this->isFillable($key)) {
            $this->setAttribute($key, $value);
        } else {
            throw new Exception("Attribute '{$key}' is not fillable.");
        }
    }

    /**
     * Check if an attribute is set.
     *
     * @param string $key Attribute name
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Unset an attribute.
     *
     * @param string $key Attribute name
     * @return void
     */
    public function __unset(string $key): void
    {
        unset($this->attributes[$key], $this->original[$key]);
    }

    // --- Helpers ---

    /**
     * Check if an attribute is mass assignable.
     *
     * @param string $key Attribute name
     * @return bool
     */
    protected function isFillable(string $key): bool
    {
        return empty($this->fillable) || in_array($key, $this->fillable);
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable(): string
    {
        if ($this->table) {
            return $this->table;
        }
        $className = substr(strrchr(static::class, '\\'), 1);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
    }

    /**
     * Hydrate raw data into model instances.
     *
     * @param array $items Raw data
     * @return static[]
     */
    public static function hydrate(array $items): array
    {
        $instances = [];
        foreach ($items as $item) {
            $instance = new static();
            $instance->attributes = $item;
            $instance->original = $item;
            $instance->exists = true;
            $instances[] = $instance;
        }
        return $instances;
    }

    // --- Relationships ---

    /**
     * Define a one-to-many relationship.
     *
     * @param string $related Related model class
     * @param string $foreignKey Foreign key on the related table
     * @return static[]
     */
    protected function hasMany(string $related, string $foreignKey): array
    {
        $query = (new $related())->query();
        return static::hydrate($query->where($foreignKey, '=', $this->getKey())->get());
    }

    /**
     * Define a one-to-one or many-to-one relationship.
     *
     * @param string $related Related model class
     * @param string $foreignKey Foreign key on the current model
     * @return static|null
     */
    protected function belongsTo(string $related, string $foreignKey): ?self
    {
        $query = (new $related())->query();
        $result = $query->where('id', '=', $this->getAttribute($foreignKey))->first();
        return $result ? (new $related())->hydrate([$result])[0] : null;
    }

    /**
     * Define a many-to-many relationship.
     *
     * @param string $related Related model class
     * @param string $pivotTable Pivot table name
     * @param string $foreignKey Foreign key for the current model
     * @param string $relatedKey Foreign key for the related model
     * @return static[]
     */
    protected function belongsToMany(string $related, string $pivotTable, string $foreignKey, string $relatedKey): array
    {
        $relatedInstance = new $related();
        $query = $relatedInstance->query();
        $query->join($pivotTable, "{$pivotTable}.{$relatedKey}", '=', "{$relatedInstance->getTable()}.id")
            ->where("{$pivotTable}.{$foreignKey}", '=', $this->getKey());
        return static::hydrate($query->get());
    }
}
