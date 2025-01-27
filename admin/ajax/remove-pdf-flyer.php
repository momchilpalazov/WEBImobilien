<?php
session_start();

require_once '../includes/auth.php';
require_once "../../src/Database/Database.php";
require_once "../../config/database.php";

use App\Database\Database;

header('Content-Type: application/json');

// Проверка за админ права
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Получаване на данните от заявката
    $input = json_decode(file_get_contents('php://input'), true);
    $propertyId = $input['property_id'] ?? null;

    if (!$propertyId) {
        throw new Exception('Невалидно ID на имот');
    }

    $db = Database::getInstance()->getConnection();
    
    // Взимане на текущото PDF
    $stmt = $db->prepare("SELECT pdf_flyer FROM properties WHERE id = ?");
    $stmt->execute([$propertyId]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        throw new Exception('Имотът не беше намерен');
    }

    // Изтриване на файла, ако съществува
    if (!empty($property['pdf_flyer'])) {
        $file_path = '../../uploads/flyers/' . $property['pdf_flyer'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Нулиране на стойността в базата данни
    $stmt = $db->prepare("UPDATE properties SET pdf_flyer = NULL WHERE id = ?");
    $stmt->execute([$propertyId]);

    echo json_encode(['success' => true, 'message' => 'PDF експозето беше изтрито успешно']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 