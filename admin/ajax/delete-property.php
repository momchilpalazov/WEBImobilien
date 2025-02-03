<?php
require_once '../includes/auth.php';
require_once "../../config/database.php";
require_once "../../src/Database/Database.php";
use App\Database\Database;

checkAuth();
checkPermission('manage_properties');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Property ID is required']);
    exit;
}

$db = Database::getInstance()->getConnection();

try {
    // Започваме транзакция
    $db->beginTransaction();
    
    // Вземаме информация за снимките
    $images = $db->prepare("SELECT image_path FROM property_images WHERE property_id = ?");
    $images->execute([$id]);
    $imageFiles = $images->fetchAll(PDO::FETCH_COLUMN);
    
    // Изтриваме физическите файлове
    foreach ($imageFiles as $image) {
        $mainImage = "../../uploads/properties/{$image}";
        $thumbnail = "../../uploads/properties/thumbnails/{$image}";
        
        if (file_exists($mainImage)) {
            unlink($mainImage);
        }
        if (file_exists($thumbnail)) {
            unlink($thumbnail);
        }
    }
    
    // Изтриваме свързаните записи
    $db->prepare("DELETE FROM property_images WHERE property_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM property_documents WHERE property_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM inquiries WHERE property_id = ?")->execute([$id]);
    
    // Изтриваме самия имот
    $db->prepare("DELETE FROM properties WHERE id = ?")->execute([$id]);
    
    // Потвърждаваме транзакцията
    $db->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Връщаме промените при грешка
    $db->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => 'Error deleting property: ' . $e->getMessage()
    ]);
} 