<?php
$config = [
    'host' => '127.0.0.1',
    'dbname' => 'industrial_properties',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

try {
    $db = new PDO(
        "mysql:host={$config['host']}",
        $config['username'],
        $config['password']
    );
    
    // Създаване на базата данни ако не съществува
    $db->exec("CREATE DATABASE IF NOT EXISTS {$config['dbname']}");
    $db->exec("USE {$config['dbname']}");
    
    // Изпълнение на миграциите
    $migrations = glob(__DIR__ . '/../database/migrations/*.sql');
    sort($migrations); // Подреждане по име
    
    foreach ($migrations as $migration) {
        $sql = file_get_contents($migration);
        echo "Executing migration: " . basename($migration) . "\n";
        
        try {
            $db->exec($sql);
            echo "Success!\n";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "All migrations completed!\n";
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
} 