<?php

namespace Core\Database\Migrations;

use PDO;
use Exception;
use Core\Database\DB;
use Core\Logging\Log;

/**
 * Manages database migrations.
 */
class Migrator
{
    /**
     * The migrations directory path.
     *
     * @var string
     */
    protected $path;

    /**
     * The database connection.
     *
     * @var PDO
     */
    protected $db;

    /**
     * Create a new migrator instance.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
        $this->db = DB::getInstance();
        $this->createMigrationsTable();
    }

    /**
     * Create the migrations table if it doesn't exist.
     *
     * @return void
     */
    protected function createMigrationsTable(): void
    {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL;

        try {
            $this->db->exec($sql);
        } catch (\PDOException $e) {
            Log::error("Failed to create migrations table: {$e->getMessage()}", ['exception' => $e]);
            throw new Exception("Could not create migrations table: {$e->getMessage()}");
        }
    }

    /**
     * Run pending migrations.
     *
     * @return void
     */
    public function run(): void
    {
        $files = glob($this->path . '/*.php');
        $migrations = $this->getExecutedMigrations();
        $batch = $this->getNextBatchNumber();
        $toRun = [];

        foreach ($files as $file) {
            $migration = basename($file, '.php');
            if (!in_array($migration, $migrations)) {
                $toRun[] = $file;
            }
        }

        if (empty($toRun)) {
            echo "Nothing to migrate.\n";
            return;
        }

        $this->db->beginTransaction();
        try {
            foreach ($toRun as $file) {
                $migrationName = basename($file, '.php');
                echo "migration::{$migrationName}.php----------------";
                try {
                    $this->runMigration($file, $batch);
                    echo "PASSED\n";
                } catch (\Exception $e) {
                    echo "FAILED\n";
                    echo "Error: {$e->getMessage()} in {$file}\n";
                    throw $e;
                }
            }
            $this->db->commit();
            echo "All migrations completed successfully.\n";
        } catch (\Exception $e) {
            $this->db->rollBack();
            Log::error("Migration batch failed: {$e->getMessage()}", ['exception' => $e]);
            echo "Error: Migration batch failed - {$e->getMessage()}\nCheck logs for details.\n";
            throw $e;
        }
    }

    /**
     * Run a single migration.
     *
     * @param string $file
     * @param int $batch
     * @return void
     * @throws Exception
     */
    protected function runMigration(string $file, int $batch): void
    {
        require_once $file;
        $className = $this->getClassName(basename($file, '.php'));
        if (!class_exists($className)) {
            throw new Exception("Migration class {$className} not found in {$file}");
        }

        $migration = new $className();
        try {
            $migration->up();
            $this->db->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)')->execute([
                basename($file, '.php'),
                $batch
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to run migration {$className}: {$e->getMessage()}", [
                'file' => $file,
                'class' => $className,
                'exception' => $e
            ]);
            throw new Exception("Migration {$className} failed: {$e->getMessage()}");
        }
    }

    /**
     * Rollback the last batch of migrations.
     *
     * @return void
     */
    public function rollback(): void
    {
        $lastBatch = $this->getLastBatchNumber();
        $migrations = $this->getMigrationsByBatch($lastBatch);

        if (empty($migrations)) {
            echo "Nothing to rollback.\n";
            return;
        }

        $this->db->beginTransaction();
        try {
            foreach ($migrations as $migration) {
                echo "migration-------------{$migration}... ";
                try {
                    $this->rollbackMigration($migration);
                    echo "PASSED\n";
                } catch (\Exception $e) {
                    echo "FAILED\n";
                    echo "Error: {$e->getMessage()} in {$this->path}/{$migration}.php\n";
                    throw $e;
                }
            }
            $this->db->commit();
            echo "All rollbacks completed successfully.\n";
        } catch (\Exception $e) {
            $this->db->rollBack();
            Log::error("Rollback failed: {$e->getMessage()}", ['exception' => $e]);
            echo "Error: Rollback failed - {$e->getMessage()}\nCheck logs for details.\n";
            throw $e;
        }
    }

    /**
     * Rollback a single migration.
     *
     * @param string $migration
     * @return void
     * @throws Exception
     */
    protected function rollbackMigration(string $migration): void
    {
        $file = $this->path . '/' . $migration . '.php';
        require_once $file;
        $className = $this->getClassName($migration);
        if (!class_exists($className)) {
            throw new Exception("Migration class {$className} not found in {$file}");
        }

        $instance = new $className();
        try {
            $instance->down();
            $this->db->prepare('DELETE FROM migrations WHERE migration = ?')->execute([$migration]);
        } catch (\Exception $e) {
            Log::error("Failed to rollback migration {$className}: {$e->getMessage()}", [
                'file' => $file,
                'class' => $className,
                'exception' => $e
            ]);
            throw new Exception("Rollback {$className} failed: {$e->getMessage()}");
        }
    }

    /**
     * Get executed migrations.
     *
     * @return array
     */
    protected function getExecutedMigrations(): array
    {
        $stmt = $this->db->query('SELECT migration FROM migrations');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get the next batch number.
     *
     * @return int
     */
    protected function getNextBatchNumber(): int
    {
        $stmt = $this->db->query('SELECT MAX(batch) FROM migrations');
        return (int)$stmt->fetchColumn() + 1;
    }

    /**
     * Get the last batch number.
     *
     * @return int
     */
    protected function getLastBatchNumber(): int
    {
        $stmt = $this->db->query('SELECT MAX(batch) FROM migrations');
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get migrations by batch number.
     *
     * @param int $batch
     * @return array
     */
    protected function getMigrationsByBatch(int $batch): array
    {
        $stmt = $this->db->prepare('SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC');
        $stmt->execute([$batch]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Convert migration name to class name.
     *
     * @param string $migration
     * @return string
     */
    protected function getClassName(string $migration): string
    {
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migration);
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }
}
