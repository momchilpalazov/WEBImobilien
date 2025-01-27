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

// Проверка за POST заявка
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // Включване на error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Логване на входящите данни
    error_log("POST data: " . print_r($_POST, true));

    // Подготовка на данните
    $data = [
        'title_bg' => $_POST['title_bg'] ?? '',
        'title_de' => $_POST['title_de'] ?? '',
        'title_ru' => $_POST['title_ru'] ?? '',
        'title_en' => $_POST['title_en'] ?? '',
        'price' => floatval($_POST['price'] ?? 0),
        'area' => floatval($_POST['area'] ?? 0),
        'type' => $_POST['type'] ?? '',
        'status' => $_POST['status'] ?? '',
        'location_bg' => $_POST['location_bg'] ?? '',
        'location_de' => $_POST['location_de'] ?? '',
        'location_ru' => $_POST['location_ru'] ?? '',
        'location_en' => $_POST['location_en'] ?? '',
        'address' => $_POST['address'] ?? '',
        'featured' => isset($_POST['featured']) ? 1 : 0,
        'specification_bg' => $_POST['specification_bg'] ?? '',
        'specification_de' => $_POST['specification_de'] ?? '',
        'specification_ru' => $_POST['specification_ru'] ?? '',
        'specification_en' => $_POST['specification_en'] ?? '',
        'description_bg' => $_POST['description_bg'] ?? '',
        'description_de' => $_POST['description_de'] ?? '',
        'description_ru' => $_POST['description_ru'] ?? '',
        'description_en' => $_POST['description_en'] ?? ''
    ];

    // Логване на подготвените данни
    error_log("Prepared data: " . print_r($data, true));

    // Валидация на задължителните полета
    $required_fields = ['title_bg', 'title_de', 'title_ru', 'title_en', 'type', 'status', 'price', 'area'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            error_log("Missing required field: $field");
            throw new Exception("Полето '$field' е задължително");
        }
    }

    // Валидация на типа имот
    $allowed_types = ['industrial', 'logistics', 'office'];
    if (!in_array($data['type'], $allowed_types)) {
        error_log("Invalid property type: " . $data['type']);
        throw new Exception("Невалиден тип имот");
    }

    // Валидация на статуса
    $allowed_statuses = ['available', 'reserved', 'rented', 'sold'];
    if (!in_array($data['status'], $allowed_statuses)) {
        error_log("Invalid property status: " . $data['status']);
        throw new Exception("Невалиден статус на имота");
    }

    // SQL заявка за вмъкване
    $sql = "INSERT INTO properties (
        title_bg, title_de, title_ru, title_en,
        price, area, type, status,
        location_bg, location_de, location_ru, location_en,
        address, featured,
        specification_bg, specification_de, specification_ru, specification_en,
        description_bg, description_de, description_ru, description_en,
        created_at
    ) VALUES (
        :title_bg, :title_de, :title_ru, :title_en,
        :price, :area, :type, :status,
        :location_bg, :location_de, :location_ru, :location_en,
        :address, :featured,
        :specification_bg, :specification_de, :specification_ru, :specification_en,
        :description_bg, :description_de, :description_ru, :description_en,
        NOW()
    )";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        $propertyId = $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        error_log("SQL: " . $sql);
        error_log("Data: " . print_r($data, true));
        throw new Exception("Грешка при запазване в базата данни: " . $e->getMessage());
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Имотът беше добавен успешно',
        'propertyId' => $propertyId
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    
    error_log("Error in add-property.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 