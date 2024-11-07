<?php
namespace App\Models\Data;

use PDO as PDO;
use Exception as Exception;

class DatabaseConnectionUtf8Model{
    private $dbService;
    private $host;
    private $username;
    private $password;
    private $database;
    private $port;


    public function __construct(){
      $this->host = 'localhost';
      $this->username = USERNAME;
      $this->password = PASSWORD;
      $this->database = DATABASE;
      $this->port = 3306;
      $this->connect();

    }
    private function connect() {
      try {
        
          $dsn = "mysql:host={$this->host};port= {$this->port};dbname={$this->database};charset=utf8";
          $this->databaseService = new PDO($dsn, $this->username, $this->password);
          // Set PDO error mode to exception
          $this->databaseService->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
          throw new Exception("Failed to connect to the database: " . $e->getMessage());
      }
    }

    public function executeQuery(string $query, array $params = []) {
        try {
            $results = $this->databaseService->prepare($query);
            if (empty($params)) {
                $results->execute();
            } else {
                $results->execute($params);
            }
            return $results;
        } catch (PDOException $e) {
            throw new Exception("Error executing the query: " . $e->getMessage());
        }
    }

    public function closeConnection() {
        $this->databaseService = null;
    }
}
