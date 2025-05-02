<?php

namespace Core\Database\Seeders;

use Core\Database\DB;
use Core\Logging\Log;

/**
 * Base class for database seeders.
 */
abstract class Seeder
{
    /**
     * The database connection.
     *
     * @var \PDO
     */
    protected $db;

    /**
     * Create a new seeder instance.
     */
    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Run the seeder.
     *
     * @return void
     */
    abstract public function run(): void;

    /**
     * Insert a single record into a table.
     *
     * @param string $table
     * @param array $data
     * @return int The inserted record's ID
     * @throws \Exception
     */
    protected function insert(string $table, array $data): int
    {
        try {
            $columns = implode('`, `', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO `{$table}` (`{$columns}`) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));
            return (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            Log::error("Failed to insert record into {$table}: {$e->getMessage()}", [
                'data' => $data,
                'sql' => $sql,
                'exception' => $e
            ]);
            throw new \Exception("Failed to insert record into {$table}: {$e->getMessage()}");
        }
    }

    /**
     * Insert multiple records into a table.
     *
     * @param string $table
     * @param array $records
     * @return int The number of inserted records
     * @throws \Exception
     */
    protected function insertMany(string $table, array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        try {
            $columns = implode('`, `', array_keys($records[0]));
            $placeholders = implode(', ', array_fill(0, count($records[0]), '?'));
            $sql = "INSERT INTO `{$table}` (`{$columns}`) VALUES " . implode(', ', array_fill(0, count($records), "({$placeholders})"));
            $stmt = $this->db->prepare($sql);
            $values = [];
            foreach ($records as $record) {
                $values = array_merge($values, array_values($record));
            }
            $stmt->execute($values);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            Log::error("Failed to insert multiple records into {$table}: {$e->getMessage()}", [
                'records' => $records,
                'sql' => $sql,
                'exception' => $e
            ]);
            throw new \Exception("Failed to insert multiple records into {$table}: {$e->getMessage()}");
        }
    }

    /**
     * Generate fake data for a field.
     *
     * @param string $type
     * @return mixed
     */
    protected function fake(string $type)
    {
        switch ($type) {
            case 'name':
                return 'User' . rand(1, 1000);
            case 'email':
                return 'user' . rand(1, 1000) . '@example.com';
            case 'password':
                return password_hash('password', PASSWORD_BCRYPT);
            case 'text':
                return substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz ', 10)), 0, 100);
            case 'boolean':
                return rand(0, 1);
            case 'datetime':
                return date('Y-m-d H:i:s', strtotime('-' . rand(1, 365) . ' days'));
            default:
                return null;
        }
    }

    /**
     * Call another seeder.
     *
     * @param string $seeder
     * @return void
     */
    protected function call(string $seeder): void
    {
        $seederInstance = new $seeder();
        $seederInstance->run();
    }
}
