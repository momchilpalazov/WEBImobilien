<?php
require_once __DIR__ . '/../config/database.php';
use App\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Започване на rollback...\n";
    
    // Вземаме всички SQL файлове за rollback, сортирани по име в обратен ред
    $migrations = glob(__DIR__ . '/migrations/rollback/*.sql');
    rsort($migrations); // Обратен ред, за да спазим foreign key constraints
    
    foreach ($migrations as $migration) {
        $filename = basename($migration);
        echo "Изпълнение на {$filename}...\n";
        
        // Четем и изпълняваме SQL файла
        $sql = file_get_contents($migration);
        $db->exec($sql);
    }
    
    echo "Rollback завърши успешно!\n";
    
} catch (Exception $e) {
    echo "Грешка при rollback: " . $e->getMessage() . "\n";
    exit(1);
} 