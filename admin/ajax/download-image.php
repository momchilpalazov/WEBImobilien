<?php
require_once '../includes/auth.php';
require_once "../../config/database.php";
use App\Database;

checkAuth();
checkPermission('manage_properties');

if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Image ID is required');
}

$db = Database::getInstance()->getConnection();

try {
    $stmt = $db->prepare("SELECT image_path FROM property_images WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $image = $stmt->fetch();
    
    if (!$image) {
        throw new Exception('Image not found');
    }
    
    $file_path = "../../uploads/properties/{$image['image_path']}";
    
    if (!file_exists($file_path)) {
        throw new Exception('File not found');
    }
    
    // Определяне на MIME типа
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    
    // Задаване на headers за изтегляне
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $image['image_path'] . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache');
    
    // Изпращане на файла
    readfile($file_path);
    
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    exit($e->getMessage());
} 