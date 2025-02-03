<?php
session_start();

require_once '../includes/auth.php';
require_once "../../src/Database/Database.php";
require_once "../../config/database.php";

use App\Database\Database;

// Включване на error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Задаване на header-и
header('Content-Type: application/json');

try {
    // Проверка за админ права
    if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
        throw new Exception('Неоторизиран достъп');
    }

    // Проверка за POST заявка
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Невалиден метод на заявка');
    }

    // Вземане на данните от POST заявката
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['image_id']) || !is_numeric($data['image_id'])) {
        throw new Exception('Невалиден ID на снимка');
    }

    $db = Database::getInstance()->getConnection();
    
    // Започваме транзакция
    $db->beginTransaction();

    try {
        // Първо вземаме информация за снимката
        $stmt = $db->prepare("SELECT image_path FROM property_images WHERE id = ?");
        $stmt->execute([$data['image_id']]);
        $image = $stmt->fetch();

        if (!$image) {
            throw new Exception('Снимката не е намерена');
        }

        // Изтриваме файловете
        $baseDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'properties';
        $originalPath = $baseDir . DIRECTORY_SEPARATOR . $image['image_path'];
        $thumbnailPath = $baseDir . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR . $image['image_path'];

        if (file_exists($originalPath)) {
            unlink($originalPath);
        }
        if (file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }

        // Изтриваме записа от базата данни
        $stmt = $db->prepare("DELETE FROM property_images WHERE id = ?");
        if (!$stmt->execute([$data['image_id']])) {
            throw new Exception('Грешка при изтриване на снимката от базата данни');
        }

        // Commit транзакцията
        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Снимката е изтрита успешно'
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log('Delete image error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 