<?php
require_once '../includes/auth.php';
require_once "../../config/database.php";
use App\Database;

checkAuth();
checkPermission('manage_users');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)$data['id'];

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();
    
    // Проверка дали потребителят има свързани записи
    $stmt = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM properties WHERE created_by = ?) as properties_count,
            (SELECT COUNT(*) FROM inquiries WHERE assigned_to = ?) as inquiries_count
    ");
    $stmt->execute([$id, $id]);
    $counts = $stmt->fetch();
    
    if ($counts['properties_count'] > 0 || $counts['inquiries_count'] > 0) {
        throw new Exception('Този потребител има свързани записи и не може да бъде изтрит');
    }
    
    // Изтриване на потребителя
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    $db->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 