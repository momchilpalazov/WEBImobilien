<?php

namespace App\Database;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;
    private $queries = [];
    private $queryTimes = [];
    
    private function __construct() {
        $config = require __DIR__ . '/../../config/database.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $options = $config['options'] ?? [];

        try {
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $options);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query(string $sql, array $params = []): \PDOStatement {
        $start = microtime(true);
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            $end = microtime(true);
            $this->logQuery($sql, $params, $end - $start);
            
            return $stmt;
        } catch (PDOException $e) {
            throw new DatabaseException("Query failed: " . $e->getMessage());
        }
    }
    
    private function logQuery(string $sql, array $params, float $time): void {
        $this->queries[] = [
            'sql' => $sql,
            'params' => $params,
            'time' => $time
        ];
    }
    
    public function getQueryLog(): array {
        return $this->queries;
    }
    
    public function getTotalQueryTime(): float {
        return array_sum(array_column($this->queries, 'time'));
    }
    
    private function __clone() {}
    
    public function __wakeup() {
        throw new DatabaseException("Cannot unserialize singleton");
    }
}