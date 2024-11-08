<?php

namespace App\Services\Database;

use \PDO;
use PDOException;
use Exception;
use InvalidArgumentException;

/**
 * DatabaseService
 *
 * Purpose: Provides a layer for handling database connections and query execution using PDO.
 * 
 * Responsibilities:
 * - Establish a database connection
 * - Execute SQL queries and updates
 * - Retrieve the last inserted ID
 * - Close the database connection
 */
class DatabaseService {

    private $host;
    private $username;
    private $password;
    private $database;
    private $port;
    private $charset;
    private $collation;
    private $prefix;
    private $dbConnection;

    /**
     * Constructor that initializes database configuration and establishes a connection.
     *
     * @param string $configType The type of database configuration to use (default: 'standard').
     * @throws InvalidArgumentException If the specified configuration type is not found.
     * @throws Exception If the database connection fails.
     */
    public function __construct($configType = 'standard') {
        if (!isset(DATABASES[$configType])) {
            writeLog('DatabaseService-17', 'Configuration type not found');
            throw new InvalidArgumentException("Configuration type '$configType' not found.");
        }

        $config = DATABASES[$configType];
        $this->host = $config['DB_HOST'] ?? 'localhost';
        $this->username = $config['DB_USERNAME'];
        $this->password = $config['DB_PASSWORD'];
        $this->database = $config['DB_DATABASE'];
        $this->port = $config['DB_PORT'] ?? 3306;
        $this->charset = $config['DB_CHARSET'];
        $this->collation = $config['DB_COLLATION'];
        $this->prefix = $config['PREFIX'] ?? '';

        $this->connect();
    }

    /**
     * Establishes a connection to the database using PDO.
     *
     * @throws Exception If the connection to the database fails.
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";
            $this->dbConnection = new PDO($dsn, $this->username, $this->password);
            $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Failed to connect to the database: " . $e->getMessage());
        }
    }

    /**
     * Executes a SQL query with optional parameters and binds them based on their type.
     * 
     * This method prepares the SQL query and then binds the provided parameters, ensuring that integers 
     * are bound as `PDO::PARAM_INT` to avoid issues with clauses like `LIMIT`. Strings are bound as 
     * `PDO::PARAM_STR` by default. It then executes the query and returns the PDO statement object.
     *
     * @param string $query The SQL query to execute.
     * @param array $params Optional parameters for prepared statement. Parameters are automatically 
     *                      bound as integers if their value is an integer, otherwise they are bound as strings.
     *                      Example: [':emails_per_que' => 1, ':days_between_emails' => 2].
     * @return \PDOStatement The PDOStatement object after query execution.
     * @throws Exception If query execution fails.
     */
    public function executeQuery(string $query, array $params = []) {
        try {
            // Prepare the query using the PDO connection
            $results = $this->dbConnection->prepare($query);
            
            // Iterate over the provided parameters and bind each based on its type
            foreach ($params as $key => $value) {
                // Bind as integer if the value is an integer, otherwise bind as string
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $results->bindValue($key, $value, $paramType);
            }

            // Execute the prepared statement
            $results->execute();
            
            // Return the PDO statement object to allow fetching results or row count
            return $results;
        } catch (PDOException $e) {
            // Throw an exception if the query execution fails, including the error message
            throw new Exception("Error executing the query: " . $e->getMessage());
        }
    }


    /**
     * Executes a SQL update (INSERT, UPDATE, DELETE) with optional parameters.
     *
     * @param string $query The SQL update query to execute.
     * @param array $params Optional parameters for the prepared statement.
     * @return int The number of rows affected.
     * @throws Exception If the update query execution fails.
     */
    public function executeUpdate(string $query, array $params = []) {
        try {
            $results = $this->dbConnection->prepare($query);
            $results->execute($params);
            return $results->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Error executing the update: " . $e->getMessage());
        }
    }

    /**
 * Executes a SQL query and returns an array of values from a single column.
 *
 * @param string $query The SQL query to execute.
 * @param array $params Optional parameters for prepared statement.
 * @return array An array of values from the specified column.
 * @throws Exception If query execution fails.
 */
public function fetchColumn(string $query, array $params = []): array {
    try {
        $results = $this->dbConnection->prepare($query);
        $results->execute($params);
        return $results->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        throw new Exception("Error executing the query: " . $e->getMessage());
    }
}
/**
 * Executes a SQL query and returns a single value (first column of the first row).
 *
 * @param string $query The SQL query to execute.
 * @param array $params Optional parameters for prepared statement.
 * @return mixed The single value result (could be string, integer, etc.), or null if no rows are returned.
 * @throws Exception If query execution fails.
 */
public function fetchSingleValue(string $query, array $params = [])
{
    try {
        $results = $this->dbConnection->prepare($query);
        $results->execute($params);
        return $results->fetchColumn();
    } catch (PDOException $e) {
        throw new Exception("Error executing the query: " . $e->getMessage());
    }
}
/**
 * Executes a SQL query and returns all rows as an associative array.
 *
 * @param string $query The SQL query to execute.
 * @param array $params Optional parameters for prepared statement (for binding values like email IDs).
 * @return array The result set as an associative array.
 * @throws Exception If query execution fails.
 */
public function fetchAll(string $query, array $params = []): array
{
    try {
        $results = $this->dbConnection->prepare($query);
        $results->execute($params);
        return $results->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new Exception("Error executing the query: " . $e->getMessage());
    }
}


    /**
     * Retrieves the last inserted ID from the database.
     *
     * @return string The last inserted ID.
     */
    public function getLastInsertId(): string {
        return $this->dbConnection->lastInsertId();
    }

    /**
     * Closes the database connection.
     */
    public function closeConnection() {
        $this->dbConnection = null;
    }
}
