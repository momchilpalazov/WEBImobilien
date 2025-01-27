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
$images = $input['images'] ?? null;

if (!$images || !is_array($images)) {
    echo json_encode(['success' => false, 'message' => 'Invalid image data']);
    exit;
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();
    
    $stmt = $db->prepare("UPDATE property_images SET sort_order = ? WHERE id = ?");
    
    foreach ($images as $order => $id) {
        $stmt->execute([$order, $id]);
    }
    
    $db->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 