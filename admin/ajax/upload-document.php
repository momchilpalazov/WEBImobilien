<?php
require_once '../includes/auth.php';
require_once "../../config/database.php";
use App\Database;

checkAuth();
checkPermission('manage_properties');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$property_id = $_POST['property_id'] ?? null;
if (!$property_id) {
    echo json_encode(['success' => false, 'message' => 'Property ID is required']);
    exit;
}

require_once "../../config/database.php";
$db = Database::getInstance()->getConnection();

try {
    if (!isset($_FILES['file'])) {
        throw new Exception('No file uploaded');
    }
    
    $file = $_FILES['file'];
    $filename = uniqid() . '_' . sanitize_filename($file['name']);
    $upload_path = '../../uploads/documents/';
    
    // Създаване на директорията ако не съществува
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0777, true);
    }
    
    // Проверка на типа на файла
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Invalid file type');
    }
    
    // Качване на файла
    if (!move_uploaded_file($file['tmp_name'], $upload_path . $filename)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    // Запис в базата данни
    $stmt = $db->prepare("
        INSERT INTO property_documents (
            property_id, 
            title_bg, title_de, title_ru,
            file_path,
            file_type,
            file_size
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $property_id,
        $_POST['title_bg'] ?? pathinfo($file['name'], PATHINFO_FILENAME),
        $_POST['title_de'] ?? pathinfo($file['name'], PATHINFO_FILENAME),
        $_POST['title_ru'] ?? pathinfo($file['name'], PATHINFO_FILENAME),
        $filename,
        $file['type'],
        $file['size']
    ]);
    
    echo json_encode([
        'success' => true,
        'file' => [
            'id' => $db->lastInsertId(),
            'name' => $filename,
            'url' => '/uploads/documents/' . $filename,
            'type' => $file['type'],
            'size' => $file['size']
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function sanitize_filename($filename) {
    // Същата функция като в upload-images.php
    // ...
} 