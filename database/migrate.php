<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Database.php';

use App\Database;

try {
    $db = Database::getInstance()->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Масив със SQL файловете в правилния ред
    $sqlFiles = [
        __DIR__ . '/update_structure.sql',
        __DIR__ . '/add_columns.sql',
        __DIR__ . '/add_indexes.sql',
        __DIR__ . '/update_data.sql'
    ];

    // Изпълняване на всеки SQL файл
    foreach ($sqlFiles as $file) {
        echo "Изпълнение на " . basename($file) . "...\n";
        
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new Exception("Грешка при четене на файл: " . $file);
        }

        // Разделяне на SQL командите по ;
        $commands = array_filter(
            array_map('trim', 
                explode(';', $sql)
            ),
            'strlen'
        );

        // Изпълняване на всяка команда поотделно
        foreach ($commands as $command) {
            try {
                $db->exec($command);
                echo "✓ Успешно изпълнена команда\n";
            } catch (PDOException $e) {
                // Игнориране на грешки за вече съществуващи индекси/колони
                if (strpos($e->getMessage(), "Duplicate column name") !== false ||
                    strpos($e->getMessage(), "Duplicate key name") !== false) {
                    echo "⚠ Колоната/индексът вече съществува\n";
                    continue;
                }
                throw $e;
            }
        }
        echo "✓ " . basename($file) . " изпълнен успешно\n\n";
    }

    echo "✓ Всички миграции са изпълнени успешно!\n";

} catch (Exception $e) {
    echo "❌ Грешка: " . $e->getMessage() . "\n";
    exit(1);
} 