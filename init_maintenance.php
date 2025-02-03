<?php
require_once 'config/database.php';
require_once 'src/Database.php';
use App\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Проверяваме дали съществува записът
    $stmt = $db->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = 'maintenance_mode'");
    $stmt->execute();
    $exists = $stmt->fetchColumn() > 0;
    
    if (!$exists) {
        // Ако не съществува, създаваме го
        $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('maintenance_mode', 'false')");
        $stmt->execute();
        echo "Създаден е нов запис за maintenance_mode със стойност 'false'\n";
    } else {
        // Ако съществува, обновяваме го
        $stmt = $db->prepare("UPDATE site_settings SET setting_value = 'false' WHERE setting_key = 'maintenance_mode'");
        $stmt->execute();
        echo "Обновен е съществуващият запис за maintenance_mode със стойност 'false'\n";
    }
    
    // Проверяваме текущата стойност
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
    $stmt->execute();
    $value = $stmt->fetchColumn();
    echo "Текуща стойност: " . $value . "\n";
    
} catch (Exception $e) {
    echo "Грешка: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 