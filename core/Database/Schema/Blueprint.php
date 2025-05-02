<?php

namespace Core\Database\Schema;

use PDO;
use Closure;
use Core\Database\DB;
use Core\Logging\Log;
use InvalidArgumentException;

/**
 * Fluent interface for defining table schemas.
 */
class Blueprint
{
    /**
     * The table name.
     *
     * @var string
     */
    protected $table;

    /**
     * The columns to add or modify.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * The indexes to add.
     *
     * @var array
     */
    protected $indexes = [];

    /**
     * The foreign keys to add.
     *
     * @var array
     */
    protected $foreignKeys = [];

    /**
     * The last column added, for chaining modifiers.
     *
     * @var array|null
     */
    protected $lastColumn = null;

    /**
     * The storage engine for the table.
     *
     * @var string
     */
    protected $engine = 'InnoDB';

    /**
     * Create a new blueprint instance.
     *
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * Set the storage engine for the table.
     *
     * @param string $engine
     * @return $this
     */
    public function engine(string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * Add an auto-incrementing primary key ID column.
     *
     * @return $this
     */
    public function id(): self
    {
        $this->lastColumn = ['name' => 'id', 'type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'primary' => true];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a string column.
     *
     * @param string $name
     * @param int $length
     * @return $this
     */
    public function string(string $name, int $length = 255): self
    {
        $this->lastColumn = ['name' => $name, 'type' => "VARCHAR($length)"];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a text column.
     *
     * @param string $name
     * @return $this
     */
    public function text(string $name): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'TEXT'];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a medium text column.
     *
     * @param string $name
     * @return $this
     */
    public function mediumText(string $name): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'MEDIUMTEXT'];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a long text column.
     *
     * @param string $name
     * @return $this
     */
    public function longText(string $name): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'LONGTEXT'];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a boolean column.
     *
     * @param string $name
     * @return $this
     */
    public function boolean(string $name): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'TINYINT(1)'];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a decimal column.
     *
     * @param string $name
     * @param int $total
     * @param int $places
     * @return $this
     */
    public function decimal(string $name, int $total = 8, int $places = 2): self
    {
        $this->lastColumn = ['name' => $name, 'type' => "DECIMAL($total,$places)"];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a float column.
     *
     * @param string $name
     * @return $this
     */
    public function float(string $name): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'FLOAT'];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a double column.
     *
     * @param string $name
     * @return $this
     */
    public function double(string $name): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'DOUBLE'];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a JSON column.
     *
     * @param string $name
     * @return $this
     */
    public function json(string $name): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'JSON'];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a JSONB column (PostgreSQL-specific).
     *
     * @param string $name
     * @return $this
     */
    public function jsonb(string $name): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'JSONB'];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add an enum column.
     *
     * @param string $name
     * @param array $allowed
     * @return $this
     */
    public function enum(string $name, array $allowed): self
    {
        $allowed = array_map(fn($value) => "'{$value}'", $allowed);
        $this->lastColumn = ['name' => $name, 'type' => "ENUM(" . implode(',', $allowed) . ")"];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a date column.
     *
     * @param string $name
     * @return $this
     */
    public function date(string $name): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'DATE'];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a datetime column.
     *
     * @param string $name
     * @return $this
     */
    public function datetime(string $name): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'DATETIME'];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add an integer column.
     *
     * @param string $name
     * @param bool $unsigned
     * @return $this
     */
    public function integer(string $name, bool $unsigned = false): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'INT', 'unsigned' => $unsigned];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a big integer column.
     *
     * @param string $name
     * @param bool $unsigned
     * @return $this
     */
    public function bigInteger(string $name, bool $unsigned = false): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'BIGINT', 'unsigned' => $unsigned];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add a timestamp column.
     *
     * @param string $name
     * @return $this
     */
    public function timestamp(string $name): self
    {
        $this->lastColumn = ['name' => $name, 'type' => 'TIMESTAMP', 'nullable' => true];
        $this->columns[] = $this->lastColumn;
        return $this;
    }

    /**
     * Add created_at and updated_at timestamp columns.
     *
     * @return $this
     */
    public function timestamps(): self
    {
        $this->timestamp('created_at')->nullable();
        $this->timestamp('updated_at')->nullable();
        return $this;
    }

    /**
     * Mark the last column as nullable.
     *
     * @return $this
     */
    public function nullable(): self
    {
        if ($this->lastColumn) {
            $this->lastColumn['nullable'] = true;
            $this->columns[array_key_last($this->columns)] = $this->lastColumn;
        }
        return $this;
    }

    /**
     * Mark the last column as unsigned.
     *
     * @return $this
     */
    public function unsigned(): self
    {
        if ($this->lastColumn) {
            $this->lastColumn['unsigned'] = true;
            $this->columns[array_key_last($this->columns)] = $this->lastColumn;
        }
        return $this;
    }

    /**
     * Add a default value to the last column.
     *
     * @param mixed $value
     * @return $this
     */
    public function default(mixed $value): self
    {
        if ($this->lastColumn) {
            // Convert boolean to 0/1 for TINYINT
            if (is_bool($value) && strpos($this->lastColumn['type'], 'TINYINT') !== false) {
                $value = $value ? 1 : 0;
            }
            $this->lastColumn['default'] = $value;
            $this->columns[array_key_last($this->columns)] = $this->lastColumn;
        }
        return $this;
    }

    /**
     * Add a unique index on the last column.
     *
     * @param string|null $name
     * @return $this
     */
    public function unique(?string $name = null): self
    {
        if ($this->lastColumn) {
            $this->indexes[] = [
                'type' => 'UNIQUE',
                'columns' => [$this->lastColumn['name']],
                'name' => $name ?? "{$this->table}_{$this->lastColumn['name']}_unique"
            ];
        }
        return $this;
    }

    /**
     * Add a regular index on the last column or specified columns.
     *
     * @param string|array|null $columns
     * @param string|null $name
     * @return $this
     */
    public function index($columns = null, ?string $name = null): self
    {
        $columns = $columns ?? ($this->lastColumn ? [$this->lastColumn['name']] : []);
        if (!empty($columns)) {
            $this->indexes[] = [
                'type' => 'INDEX',
                'columns' => (array)$columns,
                'name' => $name ?? "{$this->table}_" . implode('_', (array)$columns) . '_index'
            ];
        }
        return $this;
    }

    /**
     * Add a fulltext index on the last column or specified columns.
     *
     * @param string|array|null $columns
     * @param string|null $name
     * @return $this
     */
    public function fullText($columns = null, ?string $name = null): self
    {
        $columns = $columns ?? ($this->lastColumn ? [$this->lastColumn['name']] : []);
        if (!empty($columns)) {
            $this->indexes[] = [
                'type' => 'FULLTEXT',
                'columns' => (array)$columns,
                'name' => $name ?? "{$this->table}_" . implode('_', (array)$columns) . '_fulltext'
            ];
        }
        return $this;
    }

    /**
     * Add a spatial index on the last column or specified columns.
     *
     * @param string|array|null $columns
     * @param string|null $name
     * @return $this
     */
    public function spatial($columns = null, ?string $name = null): self
    {
        $columns = $columns ?? ($this->lastColumn ? [$this->lastColumn['name']] : []);
        if (!empty($columns)) {
            $this->indexes[] = [
                'type' => 'SPATIAL',
                'columns' => (array)$columns,
                'name' => $name ?? "{$this->table}_" . implode('_', (array)$columns) . '_spatial'
            ];
        }
        return $this;
    }

    /**
     * Add a foreign key constraint.
     *
     * @param string $column
     * @return ForeignKeyDefinition
     */
    public function foreign(string $column): ForeignKeyDefinition
    {
        $foreign = new ForeignKeyDefinition($column);
        $this->foreignKeys[] = $foreign;
        return $foreign;
    }

    /**
     * Create the table.
     *
     * @return void
     * @throws \Exception
     */
    public function create(): void
    {
        $sql = $this->toCreateSql();
        try {
            DB::getInstance()->exec($sql);
        } catch (\PDOException $e) {
            Log::error("Failed to create table '{$this->table}': {$e->getMessage()}", [
                'sql' => $sql,
                'exception' => $e
            ]);
            throw new \Exception("Failed to create table '{$this->table}': {$e->getMessage()}");
        }
    }

    /**
     * Modify the table.
     *
     * @return void
     * @throws \Exception
     */
    public function build(): void
    {
        $sql = $this->toAlterSql();
        try {
            DB::getInstance()->exec($sql);
        } catch (\PDOException $e) {
            Log::error("Failed to alter table '{$this->table}': {$e->getMessage()}", [
                'sql' => $sql,
                'exception' => $e
            ]);
            throw new \Exception("Failed to alter table '{$this->table}': {$e->getMessage()}");
        }
    }

    /**
     * Generate the CREATE TABLE SQL.
     *
     * @return string
     */
    protected function toCreateSql(): string
    {
        $definitions = [];

        foreach ($this->columns as $column) {
            $def = "`{$column['name']}` {$column['type']}";
            if ($column['unsigned'] ?? false) {
                $def .= ' UNSIGNED';
            }
            if ($column['nullable'] ?? false) {
                $def .= ' NULL';
            } else {
                $def .= ' NOT NULL';
            }
            if (isset($column['default'])) {
                $value = $column['default'];
                if (is_string($value)) {
                    $value = "'{$value}'";
                } elseif (is_bool($value)) {
                    $value = $value ? '1' : '0';
                } elseif (is_null($value)) {
                    $value = 'NULL';
                }
                $def .= " DEFAULT {$value}";
            }
            if ($column['auto_increment'] ?? false) {
                $def .= ' AUTO_INCREMENT';
            }
            $definitions[] = $def;
        }

        foreach ($this->columns as $column) {
            if ($column['primary'] ?? false) {
                $definitions[] = "PRIMARY KEY (`{$column['name']}`)";
            }
        }

        foreach ($this->indexes as $index) {
            // Skip FULLTEXT for InnoDB if MySQL version is < 5.6
            if ($index['type'] === 'FULLTEXT' && $this->engine === 'InnoDB') {
                $mysqlVersion = DB::getInstance()->getAttribute(PDO::ATTR_SERVER_VERSION);
                if (version_compare($mysqlVersion, '5.6.0', '<')) {
                    Log::warning("Skipping FULLTEXT index '{$index['name']}' on '{$this->table}' as InnoDB does not support it in MySQL < 5.6");
                    continue;
                }
            }
            $definitions[] = $this->indexToSql($index);
        }

        foreach ($this->foreignKeys as $foreign) {
            $definitions[] = $foreign->toSql();
        }

        // Filter out empty definitions to prevent trailing commas
        $definitions = array_filter($definitions);
        return "CREATE TABLE `{$this->table}` (" . implode(', ', $definitions) . ") ENGINE={$this->engine} DEFAULT CHARSET=utf8mb4";
    }

    /**
     * Generate the ALTER TABLE SQL.
     *
     * @return string
     */
    protected function toAlterSql(): string
    {
        $statements = [];

        foreach ($this->columns as $column) {
            $def = "ADD `{$column['name']}` {$column['type']}";
            if ($column['unsigned'] ?? false) {
                $def .= ' UNSIGNED';
            }
            if ($column['nullable'] ?? false) {
                $def .= ' NULL';
            } else {
                $def .= ' NOT NULL';
            }
            if (isset($column['default'])) {
                $value = $column['default'];
                if (is_string($value)) {
                    $value = "'{$value}'";
                } elseif (is_bool($value)) {
                    $value = $value ? '1' : '0';
                } elseif (is_null($value)) {
                    $value = 'NULL';
                }
                $def .= " DEFAULT {$value}";
            }
            $statements[] = $def;
        }

        foreach ($this->indexes as $index) {
            // Skip FULLTEXT for InnoDB if MySQL version is < 5.6
            if ($index['type'] === 'FULLTEXT' && $this->engine === 'InnoDB') {
                $mysqlVersion = DB::getInstance()->getAttribute(PDO::ATTR_SERVER_VERSION);
                if (version_compare($mysqlVersion, '5.6.0', '<')) {
                    Log::warning("Skipping FULLTEXT index '{$index['name']}' on '{$this->table}' as InnoDB does not support it in MySQL < 5.6");
                    continue;
                }
            }
            $statements[] = 'ADD ' . $this->indexToSql($index);
        }

        foreach ($this->foreignKeys as $foreign) {
            $statements[] = 'ADD ' . $foreign->toSql();
        }

        // Filter out empty statements
        $statements = array_filter($statements);
        return "ALTER TABLE `{$this->table}` " . implode(', ', $statements);
    }

    /**
     * Convert an index definition to SQL.
     *
     * @param array $index
     * @return string
     */
    protected function indexToSql(array $index): string
    {
        $columns = implode('`, `', (array)$index['columns']);
        $name = $index['name'];
        $type = $index['type'] === 'INDEX' ? '' : $index['type'];
        return "{$type} `{$name}` (`{$columns}`)";
    }
}
