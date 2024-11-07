<?php
namespace App\Model\Data;

use PDO;
use Exception;

class DatabaseConnectionModel{
    private $dbConnection;
    public function __construct(){
        $this->host = 'localhost';
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->database = DATABASE;
        $this->port = DATABASE_PORT;
        $this->connect();
      }

    private function connect() {
      try {
        
          $dsn = "mysql:host={$this->host};port= {$this->port};dbname={$this->database};charset=utf8mb4";
          $this->dbConnection = new PDO($dsn, $this->username, $this->password);
          // Set PDO error mode to exception
          $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
          throw new Exception("Failed to connect to the database: " . $e->getMessage());
      }
    }

   /**
     * Executes a SQL query with optional parameters.
     *
     * @param string $query The SQL query to execute.
     * @param array $params Optional parameters for prepared statement.
     * @return PDOStatement The PDOStatement object.
     * @throws Exception If query execution fails.
     */
    public function executeQuery(string $query, array $params = []) {
        try {
            $statement = $this->dbConnection->prepare($query);
            $statement->execute($params);
            return $statement;
        } catch (PDOException $e) {
            throw new Exception("Error executing the query: " . $e->getMessage());
        }
    }

    /**
     * Closes the database connection.
     */
    public function closeConnection() {
        $this->dbConnection = null;
    }
}

