<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/config.php';

checkAdminAuth();

$error = '';
$success = '';
$property = null;
$property_images = [];

// Проверка за валидно ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: properties.php');
    exit();
}

$property_id = (int)$_GET['id'];

// Зареждане на данните за имота
$query = "SELECT * FROM properties WHERE id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    $stmt->close();
    
    if (!$property) {
        header('Location: properties.php');
        exit();
    }
}

// Зареждане на снимките
$image_query = "SELECT * FROM property_images WHERE property_id = ?";
if ($img_stmt = $conn->prepare($image_query)) {
    $img_stmt->bind_param("i", $property_id);
    $img_stmt->execute();
    $result = $img_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $property_images[] = $row;
    }
    $img_stmt->close();
}

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
        // Подготовка на заявката за обновяване
        $query = "UPDATE properties SET 
                    title_bg = ?, title_de = ?, title_ru = ?,
                    description_bg = ?, description_de = ?, description_ru = ?,
                    type = ?, price = ?, area = ?, location = ?,
                    latitude = ?, longitude = ?, status = ?
                 WHERE id = ?";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param(
                "sssssssddsddsі",
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
                $_POST['status'],
                $property_id
            );
            
            if ($stmt->execute()) {
                // Обработка на нови изображения
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
                            $is_main = isset($_POST['main_image']) && $_POST['main_image'] == 'new_' . $key;
                            
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
                
                // Обновяване на главната снимка за съществуващите изображения
                if (isset($_POST['main_image']) && strpos($_POST['main_image'], 'existing_') === 0) {
                    $main_image_id = (int)str_replace('existing_', '', $_POST['main_image']);
                    
                    // Първо нулираме всички is_main
                    $reset_query = "UPDATE property_images SET is_main = 0 WHERE property_id = ?";
                    if ($reset_stmt = $conn->prepare($reset_query)) {
                        $reset_stmt->bind_param("i", $property_id);
                        $reset_stmt->execute();
                        $reset_stmt->close();
                    }
                    
                    // След това задаваме новата главна снимка
                    $main_query = "UPDATE property_images SET is_main = 1 WHERE id = ? AND property_id = ?";
                    if ($main_stmt = $conn->prepare($main_query)) {
                        $main_stmt->bind_param("ii", $main_image_id, $property_id);
                        $main_stmt->execute();
                        $main_stmt->close();
                    }
                }
                
                $success = 'Имотът е обновен успешно!';
                // Презареждаме данните
                header("Location: property_edit.php?id=$property_id&success=1");
                exit();
            } else {
                $error = 'Възникна грешка при обновяване на имота!';
            }
            $stmt->close();
        }
    }
}

// Показване на съобщение за успех след пренасочване
if (isset($_GET['success'])) {
    $success = 'Имотът е обновен успешно!';
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактиране на имот</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/admin_navigation.php'; ?>
        
        <main class="admin-content">
            <div class="content-header">
                <h1>Редактиране на имот</h1>
                <a href="properties.php" class="btn btn-secondary">Назад към списъка</a>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="property-form">
                <!-- Основна информация -->
                <div class="form-section">
                    <h2>Основна информация</h2>
                    
                    <div class="form-group">
                        <label for="title_bg">Заглавие (БГ)</label>
                        <input type="text" id="title_bg" name="title_bg" 
                               value="<?php echo htmlspecialchars($property['title_bg']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="title_de">Заглавие (DE)</label>
                        <input type="text" id="title_de" name="title_de" 
                               value="<?php echo htmlspecialchars($property['title_de']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="title_ru">Заглавие (RU)</label>
                        <input type="text" id="title_ru" name="title_ru" 
                               value="<?php echo htmlspecialchars($property['title_ru']); ?>" required>
                    </div>
                </div>

                <!-- Описания -->
                <div class="form-section">
                    <h2>Описание</h2>
                    
                    <div class="form-group">
                        <label for="description_bg">Описание (БГ)</label>
                        <textarea id="description_bg" name="description_bg" required>
                            <?php echo htmlspecialchars($property['description_bg']); ?>
                        </textarea>
                    </div>

                    <div class="form-group">
                        <label for="description_de">Описание (DE)</label>
                        <textarea id="description_de" name="description_de" required>
                            <?php echo htmlspecialchars($property['description_de']); ?>
                        </textarea>
                    </div>

                    <div class="form-group">
                        <label for="description_ru">Описание (RU)</label>
                        <textarea id="description_ru" name="description_ru" required>
                            <?php echo htmlspecialchars($property['description_ru']); ?>
                        </textarea>
                    </div>
                </div>

                <!-- Детайли за имота -->
                <div class="form-section">
                    <h2>Детайли за имота</h2>
                    
                    <div class="form-group">
                        <label for="type">Тип имот</label>
                        <select id="type" name="type" required>
                            <option value="industrial" <?php echo $property['type'] == 'industrial' ? 'selected' : ''; ?>>
                                Промишлен имот
                            </option>
                            <option value="warehouse" <?php echo $property['type'] == 'warehouse' ? 'selected' : ''; ?>>
                                Склад
                            </option>
                            <option value="logistics" <?php echo $property['type'] == 'logistics' ? 'selected' : ''; ?>>
                                Логистичен център
                            </option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Цена</label>
                            <input type="number" id="price" name="price" step="0.01" 
                                   value="<?php echo $property['price']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="area">Площ (кв.м)</label>
                            <input type="number" id="area" name="area" step="0.01" 
                                   value="<?php echo $property['area']; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="location">Локация</label>
                        <input type="text" id="location" name="location" 
                               value="<?php echo htmlspecialchars($property['location']); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="latitude">Географска ширина</label>
                            <input type="text" id="latitude" name="latitude" 
                                   value="<?php echo $property['latitude']; ?>">
                        </div>

                        <div class="form-group">
                            <label for="longitude">Географска дължина</label>
                            <input type="text" id="longitude" name="longitude" 
                                   value="<?php echo $property['longitude']; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status" required>
                            <option value="available" <?php echo $property['status'] == 'available' ? 'selected' : ''; ?>>
                                Свободен
                            </option>
                            <option value="rented" <?php echo $property['status'] == 'rented' ? 'selected' : ''; ?>>
                                Под наем
                            </option>
                            <option value="sold" <?php echo $property['status'] == 'sold' ? 'selected' : ''; ?>>
                                Продаден
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Снимки -->
                <div class="form-section">
                    <h2>Снимки</h2>
                    
                    <!-- Съществуващи снимки -->
                    <?php if (!empty($property_images)): ?>
                    <div class="existing-images">
                        <h3>Текущи снимки</h3>
                        <div class="image-preview">
                            <?php foreach ($property_images as $image): ?>
                            <div class="preview-item">
                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>">
                                <div class="preview-actions">
                                    <label>
                                        <input type="radio" name="main_image" 
                                               value="existing_<?php echo $image['id']; ?>"
                                               <?php echo $image['is_main'] ? 'checked' : ''; ?>>
                                        Главна снимка
                                    </label>
                                    <a href="delete_image.php?id=<?php echo $image['id']; ?>" 
                                       class="btn btn-small btn-delete"
                                       onclick="return confirm('Сигурни ли сте, че искате да изтриете тази снимка?')">
                                        Изтрий
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Добавяне на нови снимки -->
                    <div class="form-group">
                        <label for="images">Добави нови снимки</label>
                        <input type="file" id="images" name="images[]" multiple accept="image/*">
                    </div>

                    <div id="new-image-preview" class="image-preview"></div>
                </div>

                <button type="submit" class="btn btn-primary">Запази промените</button>
            </form>
        </main>
    </div>

    <script>
        // Превю на новите изображения
        document.getElementById('images').addEventListener('change', function(e) {
            const preview = document.getElementById('new-image-preview');
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
                                <input type="radio" name="main_image" value="new_${index}">
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