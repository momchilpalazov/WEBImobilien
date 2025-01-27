<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/config.php';

checkAdminAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация на входните данни
    $required_fields = ['title_bg', 'title_de', 'title_ru', 'description_bg', 
                       'description_de', 'description_ru', 'type', 'price', 
                       'area', 'location'];
    
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $error = 'Моля, попълнете всички задължителни полета!';
    } else {
        // Подготовка на заявката
        $query = "INSERT INTO properties (
                    title_bg, title_de, title_ru, 
                    description_bg, description_de, description_ru,
                    type, price, area, location, latitude, longitude, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param(
                "sssssssddsdds",
                $_POST['title_bg'],
                $_POST['title_de'],
                $_POST['title_ru'],
                $_POST['description_bg'],
                $_POST['description_de'],
                $_POST['description_ru'],
                $_POST['type'],
                $_POST['price'],
                $_POST['area'],
                $_POST['location'],
                $_POST['latitude'],
                $_POST['longitude'],
                $_POST['status']
            );
            
            if ($stmt->execute()) {
                $property_id = $conn->insert_id;
                
                // Обработка на изображенията
                if (!empty($_FILES['images']['name'][0])) {
                    $upload_dir = '../uploads/properties/' . $property_id . '/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        $file_name = $_FILES['images']['name'][$key];
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        $new_name = uniqid() . '.' . $file_ext;
                        
                        if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                            $is_main = isset($_POST['main_image']) && $_POST['main_image'] == $key;
                            
                            $image_query = "INSERT INTO property_images (property_id, image_path, is_main) 
                                          VALUES (?, ?, ?)";
                            if ($img_stmt = $conn->prepare($image_query)) {
                                $image_path = 'uploads/properties/' . $property_id . '/' . $new_name;
                                $img_stmt->bind_param("isi", $property_id, $image_path, $is_main);
                                $img_stmt->execute();
                                $img_stmt->close();
                            }
                        }
                    }
                }
                
                $success = 'Имотът е добавен успешно!';
                header("refresh:2;url=properties.php");
            } else {
                $error = 'Възникна грешка при добавяне на имота!';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавяне на нов имот</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/admin_navigation.php'; ?>
        
        <main class="admin-content">
            <div class="content-header">
                <h1>Добавяне на нов имот</h1>
                <a href="properties.php" class="btn btn-secondary">Назад към списъка</a>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="property-form">
                <div class="form-section">
                    <h2>Основна информация</h2>
                    
                    <div class="form-group">
                        <label for="title_bg">Заглавие (БГ)</label>
                        <input type="text" id="title_bg" name="title_bg" required>
                    </div>

                    <div class="form-group">
                        <label for="title_de">Заглавие (DE)</label>
                        <input type="text" id="title_de" name="title_de" required>
                    </div>

                    <div class="form-group">
                        <label for="title_ru">Заглавие (RU)</label>
                        <input type="text" id="title_ru" name="title_ru" required>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Описание</h2>
                    
                    <div class="form-group">
                        <label for="description_bg">Описание (БГ)</label>
                        <textarea id="description_bg" name="description_bg" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="description_de">Описание (DE)</label>
                        <textarea id="description_de" name="description_de" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="description_ru">Описание (RU)</label>
                        <textarea id="description_ru" name="description_ru" required></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Детайли за имота</h2>
                    
                    <div class="form-group">
                        <label for="type">Тип имот</label>
                        <select id="type" name="type" required>
                            <option value="industrial">Промишлен имот</option>
                            <option value="warehouse">Склад</option>
                            <option value="logistics">Логистичен център</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Цена</label>
                            <input type="number" id="price" name="price" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label for="area">Площ (кв.м)</label>
                            <input type="number" id="area" name="area" step="0.01" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="location">Локация</label>
                        <input type="text" id="location" name="location" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="latitude">Географска ширина</label>
                            <input type="text" id="latitude" name="latitude">
                        </div>

                        <div class="form-group">
                            <label for="longitude">Географска дължина</label>
                            <input type="text" id="longitude" name="longitude">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status" required>
                            <option value="available">Свободен</option>
                            <option value="rented">Под наем</option>
                            <option value="sold">Продаден</option>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Снимки</h2>
                    
                    <div class="form-group">
                        <label for="images">Изберете снимки</label>
                        <input type="file" id="images" name="images[]" multiple accept="image/*">
                    </div>

                    <div id="image-preview" class="image-preview"></div>
                </div>

                <button type="submit" class="btn btn-primary">Добави имот</button>
            </form>
        </main>
    </div>

    <script>
        // Превю на изображенията
        document.getElementById('images').addEventListener('change', function(e) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            
            [...e.target.files].forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}">
                        <div class="preview-actions">
                            <label>
                                <input type="radio" name="main_image" value="${index}">
                                Главна снимка
                            </label>
                        </div>
                    `;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        });
    </script>
</body>
</html> 