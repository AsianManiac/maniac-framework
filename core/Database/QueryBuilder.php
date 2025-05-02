<?php

namespace Core\Database;

use PDO;
use Exception;
use PDOException;
use PDOStatement;
use Core\Logging\Log;

/**
 * Fluent query builder for database operations.
 *
 * Supports building and executing SELECT, INSERT, UPDATE, and DELETE queries
 * with method chaining and prepared statements.
 */
class QueryBuilder
{
    /**
     * The PDO instance.
     *
     * @var PDO
     */
    protected PDO $pdo;

    /**
     * The table to query.
     *
     * @var string
     */
    protected string $from = '';

    /**
     * Columns to select.
     *
     * @var array
     */
    protected array $selects = ['*'];

    /**
     * Where clauses.
     *
     * @var array
     */
    protected array $wheres = [];

    /**
     * Query bindings.
     *
     * @var array
     */
    protected array $bindings = [];

    /**
     * Limit clause.
     *
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * Offset clause.
     *
     * @var int|null
     */
    protected ?int $offset = null;

    /**
     * Order by clauses.
     *
     * @var array
     */
    protected array $orders = [];

    /**
     * Join clauses.
     *
     * @var array
     */
    protected array $joins = [];

    /**
     * Group by clauses.
     *
     * @var array
     */
    protected array $groups = [];

    /**
     * Having clauses.
     *
     * @var array
     */
    protected array $havings = [];

    /**
     * Create a new query builder instance.
     *
     * @param PDO $pdo PDO instance
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Set the table to query.
     *
     * @param string $table Table name
     * @return self
     */
    public function from(string $table): self
    {
        $this->from = $table;
        return $this;
    }

    /**
     * Set the columns to select.
     *
     * @param string|array $columns Columns to select
     * @return self
     */
    public function select(string|array $columns = ['*']): self
    {
        $this->selects = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * Add a where clause.
     *
     * @param string $column Column name
     * @param string $operator Operator
     * @param mixed $value Value
     * @param string $boolean Boolean connector (AND/OR)
     * @return self
     */
    public function where(string $column, string $operator, mixed $value = null, string $boolean = 'AND'): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $validOperators = ['=', '<', '>', '<=', '>=', '<>', '!=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'];
        if (!in_array(strtoupper($operator), $validOperators)) {
            throw new Exception("Invalid SQL operator: {$operator}");
        }

        $placeholder = ":where_" . count($this->bindings);
        $this->wheres[] = compact('column', 'operator', 'placeholder', 'boolean');
        $this->bindings[$placeholder] = $value;

        return $this;
    }

    /**
     * Add an OR where clause.
     *
     * @param string $column Column name
     * @param string $operator Operator
     * @param mixed $value Value
     * @return self
     */
    public function orWhere(string $column, string $operator, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Add a join clause.
     *
     * @param string $table Table to join
     * @param string $first First column
     * @param string $operator Operator
     * @param string $second Second column
     * @param string $type Join type (INNER, LEFT, RIGHT)
     * @return self
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $type = strtoupper($type);
        if (!in_array($type, ['INNER', 'LEFT', 'RIGHT', 'FULL'])) {
            throw new Exception("Invalid join type: {$type}");
        }
        $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }

    /**
     * Add a left join clause.
     *
     * @param string $table Table to join
     * @param string $first First column
     * @param string $operator Operator
     * @param string $second Second column
     * @return self
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Add a right join clause.
     *
     * @param string $table Table to join
     * @param string $first First column
     * @param string $operator Operator
     * @param string $second Second column
     * @return self
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * Add a group by clause.
     *
     * @param string|array $columns Columns to group by
     * @return self
     */
    public function groupBy(string|array $columns): self
    {
        $this->groups = array_merge($this->groups, is_array($columns) ? $columns : func_get_args());
        return $this;
    }

    /**
     * Add a having clause.
     *
     * @param string $column Column name
     * @param string $operator Operator
     * @param mixed $value Value
     * @param string $boolean Boolean connector
     * @return self
     */
    public function having(string $column, string $operator, mixed $value, string $boolean = 'AND'): self
    {
        $placeholder = ":having_" . count($this->bindings);
        $this->havings[] = compact('column', 'operator', 'placeholder', 'boolean');
        $this->bindings[$placeholder] = $value;
        return $this;
    }

    /**
     * Set the query limit.
     *
     * @param int $number Number of rows
     * @return self
     */
    public function limit(int $number): self
    {
        $this->limit = $number;
        return $this;
    }

    /**
     * Set the query offset.
     *
     * @param int $number Offset value
     * @return self
     */
    public function offset(int $number): self
    {
        $this->offset = $number;
        return $this;
    }

    /**
     * Add an order by clause.
     *
     * @param string $column Column to order by
     * @param string $direction Order direction (ASC/DESC)
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    /**
     * Set the query to select distinct rows.
     *
     * @return self
     */
    public function distinct(): self
    {
        $this->selects = array_merge(['DISTINCT'], $this->selects);
        return $this;
    }

    /**
     * Get the query results.
     *
     * @return array
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $stmt = $this->execute($sql, $this->bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single column's values.
     *
     * @return array
     */
    public function getColumn(): array
    {
        $sql = $this->toSql();
        $stmt = $this->execute($sql, $this->bindings);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get the first result.
     *
     * @return array|null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $sql = $this->toSql();
        $stmt = $this->execute($sql, $this->bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get the count of records.
     *
     * @return int
     */
    public function count(): int
    {
        $originalSelects = $this->selects;
        $originalLimit = $this->limit;
        $originalOffset = $this->offset;
        $originalOrders = $this->orders;

        $this->selects = ['COUNT(*) as aggregate'];
        $this->limit = null;
        $this->offset = null;
        $this->orders = [];

        $sql = $this->toSql();
        $stmt = $this->execute($sql, $this->bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->selects = $originalSelects;
        $this->limit = $originalLimit;
        $this->offset = $originalOffset;
        $this->orders = $originalOrders;

        return $result ? (int)$result['aggregate'] : 0;
    }

    /**
     * Check if any records exist.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Check if no records exist.
     *
     * @return bool
     */
    public function doesntExist(): bool
    {
        return !$this->exists();
    }

    /**
     * Insert a single record.
     *
     * @param array $data Data to insert
     * @return bool
     */
    public function insert(array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($key) => ":insert_{$key}", array_keys($data)));
        $sql = "INSERT INTO {$this->from} ({$columns}) VALUES ({$placeholders})";

        $bindings = [];
        foreach ($data as $key => $value) {
            $bindings[":insert_{$key}"] = $value;
        }

        $stmt = $this->execute($sql, $bindings);
        return $stmt->rowCount() > 0;
    }

    /**
     * Insert multiple records.
     *
     * @param array $records Array of records to insert
     * @return int Number of inserted records
     */
    public function insertMany(array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        $columns = implode(', ', array_keys($records[0]));
        $placeholders = implode(', ', array_fill(0, count($records[0]), '?'));
        $sql = "INSERT INTO {$this->from} ({$columns}) VALUES " . implode(', ', array_fill(0, count($records), "({$placeholders})"));

        $bindings = [];
        foreach ($records as $record) {
            $bindings = array_merge($bindings, array_values($record));
        }

        $stmt = $this->execute($sql, $bindings);
        return $stmt->rowCount();
    }

    /**
     * Insert a record and get its ID.
     *
     * @param array $data Data to insert
     * @return string|false
     */
    public function insertGetId(array $data): string|false
    {
        if ($this->insert($data)) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Update records.
     *
     * @param array $data Data to update
     * @return int Number of affected rows
     */
    public function update(array $data): int
    {
        if (empty($data)) {
            return 0;
        }
        $setParts = [];
        $updateBindings = [];
        foreach ($data as $key => $value) {
            $placeholder = ":update_{$key}";
            $setParts[] = "{$key} = {$placeholder}";
            $updateBindings[$placeholder] = $value;
        }
        $setClause = implode(', ', $setParts);

        $bindings = array_merge($updateBindings, $this->bindings);
        $whereClause = $this->buildWhereClause();

        if (empty($whereClause)) {
            throw new Exception("Update without a WHERE clause is not allowed.");
        }

        $sql = "UPDATE {$this->from} SET {$setClause} {$whereClause}";
        $stmt = $this->execute($sql, $bindings);
        return $stmt->rowCount();
    }

    /**
     * Increment a column's value.
     *
     * @param string $column Column to increment
     * @param int $amount Amount to increment by
     * @return int Number of affected rows
     */
    public function increment(string $column, int $amount = 1): int
    {
        $sql = "UPDATE {$this->from} SET {$column} = {$column} + :amount {$this->buildWhereClause()}";
        $bindings = array_merge([':amount' => $amount], $this->bindings);
        $stmt = $this->execute($sql, $bindings);
        return $stmt->rowCount();
    }

    /**
     * Decrement a column's value.
     *
     * @param string $column Column to decrement
     * @param int $amount Amount to decrement by
     * @return int Number of affected rows
     */
    public function decrement(string $column, int $amount = 1): int
    {
        $sql = "UPDATE {$this->from} SET {$column} = {$column} - :amount {$this->buildWhereClause()}";
        $bindings = array_merge([':amount' => $amount], $this->bindings);
        $stmt = $this->execute($sql, $bindings);
        return $stmt->rowCount();
    }

    /**
     * Delete records.
     *
     * @return int Number of affected rows
     */
    public function delete(): int
    {
        $whereClause = $this->buildWhereClause();
        if (empty($whereClause)) {
            throw new Exception("Delete without a WHERE clause is not allowed.");
        }
        $sql = "DELETE FROM {$this->from} {$whereClause}";
        $stmt = $this->execute($sql, $this->bindings);
        return $stmt->rowCount();
    }

    /**
     * Generate the SQL query.
     *
     * @return string
     * @throws Exception
     */
    public function toSql(): string
    {
        if (empty($this->from)) {
            throw new Exception("No table specified.");
        }

        $select = implode(', ', $this->selects);
        $sql = "SELECT {$select} FROM {$this->from}";

        $sql .= $this->buildJoinClause();
        $sql .= $this->buildWhereClause();
        $sql .= $this->buildGroupByClause();
        $sql .= $this->buildHavingClause();
        $sql .= $this->buildOrderByClause();
        $sql .= $this->buildLimitClause();
        $sql .= $this->buildOffsetClause();

        return $sql;
    }

    /**
     * Build the join clause.
     *
     * @return string
     */
    protected function buildJoinClause(): string
    {
        if (empty($this->joins)) {
            return '';
        }

        $sql = '';
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        return $sql;
    }

    /**
     * Build the where clause.
     *
     * @return string
     */
    protected function buildWhereClause(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $sql = ' WHERE ';
        $first = true;
        foreach ($this->wheres as $where) {
            $boolean = $first ? '' : " {$where['boolean']} ";
            if (strtoupper($where['operator']) === 'IN' || strtoupper($where['operator']) === 'NOT IN') {
                $value = $this->bindings[$where['placeholder']];
                if (!is_array($value)) {
                    throw new Exception("Value for IN/NOT IN must be an array.");
                }
                if (empty($value)) {
                    $sql .= $boolean . (strtoupper($where['operator']) === 'IN' ? '0=1' : '1=1');
                    unset($this->bindings[$where['placeholder']]);
                } else {
                    $inPlaceholders = [];
                    foreach ($value as $idx => $val) {
                        $inPlaceholder = $where['placeholder'] . '_' . $idx;
                        $inPlaceholders[] = $inPlaceholder;
                        $this->bindings[$inPlaceholder] = $val;
                    }
                    unset($this->bindings[$where['placeholder']]);
                    $sql .= $boolean . $where['column'] . ' ' . $where['operator'] . ' (' . implode(', ', $inPlaceholders) . ')';
                }
            } else {
                $sql .= $boolean . $where['column'] . ' ' . $where['operator'] . ' ' . $where['placeholder'];
            }
            $first = false;
        }
        return $sql;
    }

    /**
     * Build the group by clause.
     *
     * @return string
     */
    protected function buildGroupByClause(): string
    {
        if (empty($this->groups)) {
            return '';
        }
        return ' GROUP BY ' . implode(', ', $this->groups);
    }

    /**
     * Build the having clause.
     *
     * @return string
     */
    protected function buildHavingClause(): string
    {
        if (empty($this->havings)) {
            return '';
        }

        $sql = ' HAVING ';
        $first = true;
        foreach ($this->havings as $having) {
            $boolean = $first ? '' : " {$having['boolean']} ";
            $sql .= $boolean . $having['column'] . ' ' . $having['operator'] . ' ' . $having['placeholder'];
            $first = false;
        }
        return $sql;
    }

    /**
     * Build the order by clause.
     *
     * @return string
     */
    protected function buildOrderByClause(): string
    {
        if (empty($this->orders)) {
            return '';
        }
        $parts = array_map(fn($order) => "{$order['column']} {$order['direction']}", $this->orders);
        return ' ORDER BY ' . implode(', ', $parts);
    }

    /**
     * Build the limit clause.
     *
     * @return string
     */
    protected function buildLimitClause(): string
    {
        return $this->limit !== null ? " LIMIT " . (int)$this->limit : '';
    }

    /**
     * Build the offset clause.
     *
     * @return string
     */
    protected function buildOffsetClause(): string
    {
        return $this->offset !== null && $this->limit !== null ? " OFFSET " . (int)$this->offset : '';
    }

    /**
     * Execute the query.
     *
     * @param string $sql SQL query
     * @param array $bindings Query bindings
     * @return PDOStatement
     * @throws Exception
     */
    protected function execute(string $sql, array $bindings = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($bindings as $placeholder => $value) {
                $type = PDO::PARAM_STR;
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                }
                $stmt->bindValue($placeholder, $value, $type);
            }
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            Log::error("Query failed: {$e->getMessage()}", ['sql' => $sql, 'bindings' => $bindings, 'exception' => $e]);
            throw new Exception("Database query failed: {$e->getMessage()}");
        }
    }

    /**
     * Clone the query builder.
     */
    public function __clone(): void
    {
        $this->bindings = array_map(fn($value) => is_object($value) ? clone $value : $value, $this->bindings);
    }
}
