<?php

namespace App\Services\Database;

use PDO;
use PDOException;
use Exception;
use InvalidArgumentException;
use App\Services\LoggerService;
use App\Configuration\Config;

/**
 * DatabaseService
 *
 * Provides a layer for handling database connections and query execution using PDO.
 */
class DatabaseService
{
    private $host;
    private $username;
    private $password;
    private $database;
    private $port;
    private $charset;
    private $collation;
    private $prefix;
    private $dbConnection;

    public function __construct($configType = 'standard')
    {
        $databaseConfig = Config::get("databases.$configType", null);

        if ($databaseConfig === null) {
            LoggerService::logError("Database configuration", " type '$configType' not found.");
            throw new InvalidArgumentException("Configuration type '$configType' not found.");
        }

        $this->host = $databaseConfig['DB_HOST'] ?? 'localhost';
        $this->username = $databaseConfig['DB_USERNAME'];
        $this->password = $databaseConfig['DB_PASSWORD'];
        $this->database = $databaseConfig['DB_DATABASE'];
        $this->port = $databaseConfig['DB_PORT'] ?? 3306;
        $this->charset = $databaseConfig['DB_CHARSET'];
        $this->collation = $databaseConfig['DB_COLLATION'];
        $this->prefix = $databaseConfig['PREFIX'] ?? '';
        $this->connect();
    }

    private function connect()
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";
            $this->dbConnection = new PDO($dsn, $this->username, $this->password);
            $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            LoggerService::logError('Database Connect',"Failed to connect to the database: " . $e->getMessage());
            throw new Exception("Database connection error.");
        }
    }

    /**
     * Executes a SQL query with optional parameters.
     *
     * @param string $query The SQL query to execute.
     * @param array $params Optional parameters for prepared statement.
     * @return \PDOStatement|null The PDOStatement object or null if execution fails.
     */
    public function executeQuery(string $query, array $params = []): ?\PDOStatement
    {
        try {
            $stmt = $this->dbConnection->prepare($query);
            foreach ($params as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            LoggerService::logError('executeQuery', "Error executing query: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetches all rows from a query as an associative array.
     *
     * @param string $query SQL query.
     * @param array $params Optional parameters for prepared statement.
     * @return array|null The result set or null on failure.
     */
    public function fetchAll(string $query, array $params = []): ?array
    {
        $stmt = $this->executeQuery($query, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : null;
    }

    /**
     * Fetches a single row from a query as an associative array.
     *
     * @param string $query SQL query.
     * @param array $params Optional parameters for prepared statement.
     * @return array|null The result row or null on failure.
     */
    public function fetchRow(string $query, array $params = []): ?array
    {
        $stmt = $this->executeQuery($query, $params);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    }

    /**
     * Fetches a single column value from the first row.
     *
     * @param string $query SQL query.
     * @param array $params Optional parameters for prepared statement.
     * @return mixed|null The single value or null if no result.
     */
    public function fetchSingleValue(string $query, array $params = [])
    {
        $stmt = $this->executeQuery($query, $params);
        return $stmt ? $stmt->fetchColumn() : null;
    }

    /**
     * Fetches an array of values from a single column in multiple rows.
     *
     * @param string $query SQL query.
     * @param array $params Optional parameters for prepared statement.
     * @return array|null The array of values or null if query fails.
     */
    public function fetchColumn(string $query, array $params = []): ?array
    {
        $stmt = $this->executeQuery($query, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : null;
    }

    /**
     * Retrieves the last inserted ID from the database.
     *
     * @return string The last inserted ID.
     */
    public function getLastInsertId(): string
    {
        return $this->dbConnection->lastInsertId();
    }

    /**
     * Closes the database connection.
     */
    public function closeConnection(): void
    {
        $this->dbConnection = null;
    }
}
