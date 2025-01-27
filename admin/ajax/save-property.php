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
    
    // Включване на error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Проверка дали колоната pdf_flyer съществува
    $checkColumn = $db->query("SHOW COLUMNS FROM properties LIKE 'pdf_flyer'");
    if ($checkColumn->rowCount() == 0) {
        // Колоната не съществува, създаваме я
        $db->exec("ALTER TABLE properties ADD COLUMN pdf_flyer VARCHAR(255) DEFAULT NULL");
    }

    // Проверка за ID
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('Invalid property ID');
    }

    // Взимане на текущите данни
    $checkStmt = $db->prepare("SELECT * FROM properties WHERE id = ?");
    $checkStmt->execute([$_POST['id']]);
    $currentData = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentData) {
        throw new Exception('Имотът не беше намерен');
    }

    // Подготовка на данните
    $data = [
        'id' => $_POST['id'],
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

    // Обработка на PDF файла
    if (isset($_FILES['pdf_flyer']) && $_FILES['pdf_flyer']['error'] == 0) {
        $allowed = ['pdf' => 'application/pdf'];
        $filename = $_FILES['pdf_flyer']['name'];
        $filetype = $_FILES['pdf_flyer']['type'];
        $filesize = $_FILES['pdf_flyer']['size'];

        // Проверка на разширението
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            throw new Exception('Грешка: Моля, изберете PDF файл.');
        }

        // Проверка на размера - максимум 5MB
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            throw new Exception('Грешка: Размерът на файла е твърде голям.');
        }

        // Създаване на директорията, ако не съществува
        $upload_dir = '../../uploads/flyers/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Генериране на уникално име
        $new_filename = uniqid() . '_' . $filename;
        $upload_path = $upload_dir . $new_filename;

        // Изтриване на стария файл
        if (!empty($currentData['pdf_flyer'])) {
            $old_file = $upload_dir . $currentData['pdf_flyer'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        // Качване на новия файл
        if (move_uploaded_file($_FILES['pdf_flyer']['tmp_name'], $upload_path)) {
            $data['pdf_flyer'] = $new_filename;
        } else {
            throw new Exception('Грешка при качване на файла.');
        }
    } else {
        $data['pdf_flyer'] = $currentData['pdf_flyer'] ?? null;
    }

    $db->beginTransaction();

    // SQL заявка за обновяване
    $sql = "UPDATE properties SET
            title_bg = :title_bg, title_de = :title_de, title_ru = :title_ru, title_en = :title_en,
            price = :price, area = :area, type = :type, status = :status,
            location_bg = :location_bg, location_de = :location_de, location_ru = :location_ru, location_en = :location_en,
            address = :address, featured = :featured,
            specification_bg = :specification_bg, specification_de = :specification_de, 
            specification_ru = :specification_ru, specification_en = :specification_en,
            description_bg = :description_bg, description_de = :description_de,
            description_ru = :description_ru, description_en = :description_en,
            pdf_flyer = :pdf_flyer
            WHERE id = :id";

    $stmt = $db->prepare($sql);
    $stmt->execute($data);

    if ($stmt->rowCount() === 0) {
        $db->rollBack();
        throw new Exception('Имотът не беше обновен. Моля, проверете дали са направени промени.');
    }

    $db->commit();
    
    echo json_encode(['success' => true, 'message' => 'Имотът беше обновен успешно']);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 