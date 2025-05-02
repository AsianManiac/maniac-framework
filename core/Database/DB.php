<?php

namespace Core\Database;

use PDO;
use Exception;
use PDOException;
use Core\Logging\Log;

/**
 * Database connection manager.
 *
 * Provides a singleton PDO instance and utility methods for database operations.
 */
class DB
{
    /**
     * The singleton PDO instance.
     *
     * @var PDO|null
     */
    private static ?PDO $instance = null;

    /**
     * Database configuration.
     *
     * @var array
     */
    private static array $config;

    /**
     * Prevent instantiation.
     */
    private function __construct() {}

    /**
     * Prevent cloning.
     */
    private function __clone() {}

    /**
     * Prevent unserialization.
     *
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Initialize the database connection configuration.
     *
     * @param array $config Database configuration
     * @return void
     * @throws Exception
     */
    public static function init(array $config): void
    {
        self::$config = $config;
        $default = $config['default'];
        $connectionConfig = $config['connections'][$default] ?? null;

        if ($connectionConfig && $connectionConfig['driver'] === 'mysql') {
            try {
                $dsn = "mysql:host={$connectionConfig['host']};port={$connectionConfig['port']};charset={$connectionConfig['charset']}";
                $pdo = new PDO($dsn, $connectionConfig['username'], $connectionConfig['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $dbName = "`{$connectionConfig['database']}`";
                $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbName");
            } catch (PDOException $e) {
                Log::error('Failed to create database: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception("Failed to create database: {$e->getMessage()}");
            }
        }
    }

    /**
     * Get the singleton PDO instance.
     *
     * @return PDO
     * @throws Exception
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $default = self::$config['default'] ?? 'mysql';
            $config = self::$config['connections'][$default] ?? null;

            if (!$config) {
                throw new Exception('No database configuration found for connection: ' . $default);
            }

            try {
                $dsn = match ($config['driver']) {
                    'mysql' => "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
                    default => throw new Exception("Unsupported database driver: {$config['driver']}")
                };

                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options'] ?? [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                Log::error('Database connection failed: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception("Database connection failed: {$e->getMessage()}");
            }
        }

        return self::$instance;
    }

    /**
     * Execute a query with optional parameters.
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return \PDOStatement|bool
     */
    public static function query(string $sql, array $params = []): \PDOStatement|bool
    {
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Log::error("Query failed: {$e->getMessage()}", ['sql' => $sql, 'params' => $params, 'exception' => $e]);
            throw new Exception("Query failed: {$e->getMessage()}");
        }
    }

    /**
     * Execute a transaction with a callback.
     *
     * @param callable $callback Callback to execute within the transaction
     * @return mixed
     * @throws Exception
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::getInstance();
        try {
            $pdo->beginTransaction();
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Exception $e) {
            $pdo->rollBack();
            Log::error("Transaction failed: {$e->getMessage()}", ['exception' => $e]);
            throw $e;
        }
    }

    /**
     * Begin a transaction.
     *
     * @return void
     */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /**
     * Roll back a transaction.
     *
     * @return void
     */
    public static function rollBack(): void
    {
        self::getInstance()->rollBack();
    }

    /**
     * Get the last inserted ID.
     *
     * @return string
     */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    private static function buildDsn(array $config): string
    {
        // Basic DSN builder, expand for other drivers (pgsql, sqlite)
        if ($config['driver'] === 'mysql') {
            return "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        }
        // Add other drivers here
        throw new Exception("Unsupported database driver: {$config['driver']}");
    }

    // Convenience method for direct PDO access if needed
    public static function pdo(): PDO
    {
        return self::getInstance();
    }
}
