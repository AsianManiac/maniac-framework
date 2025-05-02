<?php

namespace Core\Database\Schema;

/**
 * Defines a foreign key constraint.
 */
class ForeignKeyDefinition
{
    /**
     * The column for the foreign key.
     *
     * @var string
     */
    protected $column;

    /**
     * The referenced table.
     *
     * @var string|null
     */
    protected $referencesTable;

    /**
     * The referenced column.
     *
     * @var string|null
     */
    protected $referencesColumn;

    /**
     * The ON DELETE action.
     *
     * @var string|null
     */
    protected $onDelete;

    /**
     * The ON UPDATE action.
     *
     * @var string|null
     */
    protected $onUpdate;

    /**
     * Create a new foreign key definition.
     *
     * @param string $column
     */
    public function __construct(string $column)
    {
        $this->column = $column;
    }

    /**
     * Specify the referenced table and column.
     *
     * @param string $column
     * @param string $table
     * @return $this
     */
    public function references(string $column, string $table): self
    {
        $this->referencesColumn = $column;
        $this->referencesTable = $table;
        return $this;
    }

    /**
     * Specify the ON DELETE action.
     *
     * @param string $action
     * @return $this
     */
    public function onDelete(string $action): self
    {
        $this->onDelete = $action;
        return $this;
    }

    /**
     * Specify the ON UPDATE action.
     *
     * @param string $action
     * @return $this
     */
    public function onUpdate(string $action): self
    {
        $this->onUpdate = $action;
        return $this;
    }

    /**
     * Convert to SQL.
     *
     * @return string
     */
    public function toSql(): string
    {
        $sql = "FOREIGN KEY (`{$this->column}`) REFERENCES `{$this->referencesTable}` (`{$this->referencesColumn}`)";
        if ($this->onDelete) {
            $sql .= " ON DELETE {$this->onDelete}";
        }
        if ($this->onUpdate) {
            $sql .= " ON UPDATE {$this->onUpdate}";
        }
        return "CONSTRAINT `fk_{$this->referencesTable}_{$this->column}` $sql";
    }
}
