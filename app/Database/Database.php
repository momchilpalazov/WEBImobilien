<?php

namespace App\Database;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;
    private $queries = [];
    private $queryTimes = [];
    private $config;
    
    private function __construct() {
        $configPath = __DIR__ . '/../../config/database.php';
        
        if (!file_exists($configPath)) {
            throw new DatabaseException("Configuration file not found at: " . $configPath);
        }
        
        $config = require $configPath;
        
        if (!is_array($config)) {
            throw new DatabaseException("Invalid configuration format");
        }
        
        $this->config = $config;
        
        if (!isset($this->config['host']) || !isset($this->config['database']) || 
            !isset($this->config['username']) || !isset($this->config['password'])) {
            throw new DatabaseException("Missing required database configuration parameters");
        }
        
        try {
            $this->connection = new PDO(
                "mysql:host={$this->config['host']};dbname={$this->config['database']};charset=utf8mb4",
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new DatabaseException("Connection failed: " . $e->getMessage());
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
            throw new DatabaseException("Query failed: " . $e->getMessage(), $sql, $params);
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