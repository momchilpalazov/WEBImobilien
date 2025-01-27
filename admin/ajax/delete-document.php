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
    echo json_encode(['success' => false, 'message' => 'Document ID is required']);
    exit;
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();
    
    // Вземаме информация за документа
    $stmt = $db->prepare("SELECT file_path FROM property_documents WHERE id = ?");
    $stmt->execute([$id]);
    $document = $stmt->fetch();
    
    if (!$document) {
        throw new Exception('Document not found');
    }
    
    // Изтриваме физическия файл
    $filePath = "../../uploads/documents/{$document['file_path']}";
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    // Изтриваме записа от базата данни
    $db->prepare("DELETE FROM property_documents WHERE id = ?")->execute([$id]);
    
    $db->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 