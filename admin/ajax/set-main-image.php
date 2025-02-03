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

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Image ID is required']);
    exit;
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();
    
    // Първо намираме property_id на снимката
    $stmt = $db->prepare("SELECT property_id FROM property_images WHERE id = ?");
    $stmt->execute([$id]);
    $property_id = $stmt->fetchColumn();
    
    if (!$property_id) {
        throw new Exception('Image not found');
    }
    
    // Премахваме is_main флага от всички снимки на имота
    $db->prepare("UPDATE property_images SET is_main = 0 WHERE property_id = ?")->execute([$property_id]);
    
    // Задаваме текущата снимка като основна
    $db->prepare("UPDATE property_images SET is_main = 1 WHERE id = ?")->execute([$id]);
    
    $db->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 