<?php
session_start();

// Проверка за логнат потребител
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once '../src/Database.php';

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contacts'])) {
        foreach ($_POST['contacts'] as $type => $values) {
            $stmt = $db->prepare("
                UPDATE contact_information 
                SET 
                    value_bg = :value_bg,
                    value_en = :value_en,
                    value_de = :value_de,
                    value_ru = :value_ru,
                    icon = :icon,
                    link = :link,
                    sort_order = :sort_order,
                    is_active = :is_active
                WHERE type = :type
            ");

            $stmt->execute([
                'value_bg' => $values['value_bg'],
                'value_en' => $values['value_en'],
                'value_de' => $values['value_de'],
                'value_ru' => $values['value_ru'],
                'icon' => $values['icon'] ?? '',
                'link' => $values['link'] ?? '',
                'sort_order' => $values['sort_order'] ?? 0,
                'is_active' => isset($values['is_active']) ? 1 : 0,
                'type' => $type
            ]);
        }

        $_SESSION['success_message'] = 'Контактната информация беше успешно обновена!';
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Грешка при обновяване на контактната информация: ' . $e->getMessage();
}

header('Location: settings.php');
exit; 