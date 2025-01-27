<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Проверка за админ права
// checkAdminAccess();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        die('ID на имота е задължително');
    }

    // Взимаме текущите данни за имота
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();

    if (!$property) {
        die('Имотът не е намерен');
    }

    // Събиране на данните от формата
    $title = $_POST['title'] ?? $property['title'];
    $description = $_POST['description'] ?? $property['description'];
    $description_en = $_POST['description_en'] ?? $property['description_en'];
    $description_de = $_POST['description_de'] ?? $property['description_de'];
    $description_ru = $_POST['description_ru'] ?? $property['description_ru'];
    
    // Обработка на PDF файла
    $pdf_flyer = $property['pdf_flyer']; // Запазваме текущата стойност по подразбиране
    
    if (isset($_FILES['pdf_flyer']) && $_FILES['pdf_flyer']['error'] == 0) {
        $allowed = ['pdf' => 'application/pdf'];
        $filename = $_FILES['pdf_flyer']['name'];
        $filetype = $_FILES['pdf_flyer']['type'];
        $filesize = $_FILES['pdf_flyer']['size'];

        // Проверка на разширението
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            die('Грешка: Моля, изберете PDF файл.');
        }

        // Проверка на размера - максимум 5MB
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            die('Грешка: Размерът на файла е твърде голям.');
        }

        // Създаване на директорията, ако не съществува
        $upload_dir = '../uploads/flyers/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Генериране на уникално име
        $new_filename = uniqid() . '_' . $filename;
        $upload_path = $upload_dir . $new_filename;

        // Изтриване на стария файл
        if (!empty($property['pdf_flyer'])) {
            $old_file = $upload_dir . $property['pdf_flyer'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        // Качване на новия файл
        if (move_uploaded_file($_FILES['pdf_flyer']['tmp_name'], $upload_path)) {
            $pdf_flyer = $new_filename;
        } else {
            die('Грешка при качване на файла.');
        }
    }

    // Подготовка на SQL заявката
    $sql = "UPDATE properties SET 
            title = ?, 
            description = ?,
            description_en = ?,
            description_de = ?,
            description_ru = ?,
            pdf_flyer = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", 
        $title, 
        $description,
        $description_en,
        $description_de,
        $description_ru,
        $pdf_flyer,
        $id
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Имотът беше успешно обновен']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Грешка при обновяване на имота: ' . $conn->error]);
    }

    $stmt->close();
} else {
    die('Невалиден метод на заявка');
}

$conn->close(); 