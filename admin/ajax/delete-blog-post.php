<?php
session_start();

// Проверка за достъп
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../../config/database.php';
require_once '../../src/Database.php';

use App\Database;

header('Content-Type: application/json');

// Проверка за POST заявка
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Вземане на данните от заявката
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing post ID']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Изтриване на публикацията
    $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete post']);
    }
    
} catch (Exception $e) {
    error_log("Error deleting blog post: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 