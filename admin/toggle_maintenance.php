<?php
session_start();

// Проверка за достъп
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';
require_once '../src/Database.php';
use App\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Вземаме текущия статус
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
    $stmt->execute();
    $current_mode = $stmt->fetchColumn();
    
    // Превключваме стойността
    $new_mode = ($current_mode === 'true') ? 'false' : 'true';
    
    // Обновяваме стойността в базата данни
    $stmt = $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'maintenance_mode'");
    $stmt->execute([$new_mode]);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'maintenance_mode' => $new_mode
    ]);
    
} catch (Exception $e) {
    error_log("Maintenance mode toggle error: " . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 