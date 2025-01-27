<?php
session_start();

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
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Обработка на логото
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES['site_logo']['type'], $allowed_types)) {
            throw new Exception('Невалиден формат на файла. Разрешени са само JPG, PNG и GIF.');
        }
        
        if ($_FILES['site_logo']['size'] > $max_size) {
            throw new Exception('Файлът е твърде голям. Максималният размер е 2MB.');
        }
        
        // Създаване на директория ако не съществува
        $upload_dir = '../uploads/logo/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Изтриване на старото лого ако има такова
        $stmt = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'site_logo'");
        $old_logo = $stmt->fetchColumn();
        if ($old_logo && file_exists($upload_dir . $old_logo)) {
            unlink($upload_dir . $old_logo);
        }
        
        // Генериране на уникално име
        $file_ext = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
        $new_filename = 'logo_' . time() . '.' . $file_ext;
        
        // Преместване на файла
        if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $upload_dir . $new_filename)) {
            $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) 
                                VALUES ('site_logo', ?) 
                                ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$new_filename, $new_filename]);
        } else {
            throw new Exception('Грешка при качване на файла.');
        }
    }
    
    // Премахване на логото
    if (isset($_POST['remove_logo'])) {
        $stmt = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'site_logo'");
        $old_logo = $stmt->fetchColumn();
        if ($old_logo) {
            $upload_dir = '../uploads/logo/';
            if (file_exists($upload_dir . $old_logo)) {
                unlink($upload_dir . $old_logo);
            }
            $db->exec("UPDATE site_settings SET setting_value = '' WHERE setting_key = 'site_logo'");
        }
    }
    
    // Обновяване на останалите настройки
    $settings_to_update = ['site_name', 'footer_text', 'google_maps_api_key', 'recaptcha_site_key', 'recaptcha_secret_key'];
    
    foreach ($settings_to_update as $key) {
        if (isset($_POST[$key])) {
            $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) 
                                VALUES (?, ?) 
                                ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $_POST[$key], $_POST[$key]]);
        }
    }
    
    $_SESSION['success_message'] = 'Настройките бяха успешно обновени!';
    
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Грешка при обновяване на настройките: ' . $e->getMessage();
}

header('Location: settings.php');
exit; 