<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once '../src/Database.php';
use App\Database;

$db = Database::getInstance()->getConnection();

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? '';
$property = null;
$images = [];
$documents = [];

// Обработка на на качени снимки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['images'])) {
    $upload_dir = '../uploads/properties/';
    
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === 0) {
            $filename = uniqid() . '_' . $_FILES['images']['name'][$key];
            if (move_uploaded_file($tmp_name, $upload_dir . $filename)) {
                // Добавяне на снимката в базата данни
                $stmt = $db->prepare("INSERT INTO property_images (property_id, image_path) VALUES (?, ?)");
                $stmt->execute([$id, $filename]);
            }
        }
    }
}

// Обновяване на главна снимка
if (isset($_POST['main_image'])) {
    $stmt = $db->prepare("UPDATE property_images SET is_main = 0 WHERE property_id = ?");
    $stmt->execute([$id]);
    
    $stmt = $db->prepare("UPDATE property_images SET is_main = 1 WHERE id = ? AND property_id = ?");
    $stmt->execute([$_POST['main_image'], $id]);
}

// Изтриване на снимка
if (isset($_POST['delete_image'])) {
    $stmt = $db->prepare("SELECT image_path FROM property_images WHERE id = ? AND property_id = ?");
    $stmt->execute([$_POST['delete_image'], $id]);
    $image = $stmt->fetch();
    
    if ($image) {
        unlink('../uploads/properties/' . $image['image_path']);
        $stmt = $db->prepare("DELETE FROM property_images WHERE id = ?");
        $stmt->execute([$_POST['delete_image']]);
    }
}

// Ако е заявка за дупликиране
if ($action === 'duplicate' && $id > 0) {
    $stmt = $db->prepare("
        INSERT INTO properties 
        (title_bg, title_de, title_ru, title_en,
         price, area, type, status,
         location_bg, location_de, location_ru, location_en,
         address, description_bg, description_de, description_ru, description_en,
         featured, created_at)
        SELECT 
         title_bg, title_de, title_ru, title_en,
         price, area, type, 'available',
         location_bg, location_de, location_ru, location_en,
         address, description_bg, description_de, description_ru, description_en,
         featured, NOW()
        FROM properties WHERE id = ?
    ");
    $stmt->execute([$id]);
    
    $new_property_id = $db->lastInsertId();
    
    // Копиране на снимките
    $stmt = $db->prepare("
        INSERT INTO property_images (property_id, image_path, is_main, sort_order)
        SELECT ?, image_path, is_main, sort_order
        FROM property_images WHERE property_id = ?
    ");
    $stmt->execute([$new_property_id, $id]);
    
    header('Location: index.php');
    exit;
}

// Ако формата е изпратена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_bg = $_POST['title_bg'] ?? '';
    $title_de = $_POST['title_de'] ?? '';
    $title_ru = $_POST['title_ru'] ?? '';
    $title_en = $_POST['title_en'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $area = (float)($_POST['area'] ?? 0);
    $type = $_POST['type'] ?? '';
    $status = $_POST['status'] ?? '';
    $location_bg = $_POST['location_bg'] ?? '';
    $location_de = $_POST['location_de'] ?? '';
    $location_ru = $_POST['location_ru'] ?? '';
    $location_en = $_POST['location_en'] ?? '';
    $address = $_POST['address'] ?? '';
    $description_bg = $_POST['description_bg'] ?? '';
    $description_de = $_POST['description_de'] ?? '';
    $description_ru = $_POST['description_ru'] ?? '';
    $description_en = $_POST['description_en'] ?? '';

    if ($id > 0) {
        // Обновяване на съществуващ имот
        $stmt = $db->prepare("
            UPDATE properties 
            SET title_bg = ?, title_de = ?, title_ru = ?, title_en = ?,
                price = ?, area = ?, type = ?, status = ?,
                location_bg = ?, location_de = ?, location_ru = ?, location_en = ?,
                address = ?, description_bg = ?, description_de = ?, description_ru = ?, description_en = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $title_bg, $title_de, $title_ru, $title_en,
            $price, $area, $type, $status,
            $location_bg, $location_de, $location_ru, $location_en,
            $address, $description_bg, $description_de, $description_ru, $description_en,
            $id
        ]);
    } else {
        // Добавяне на нов имот
        $stmt = $db->prepare("
            INSERT INTO properties (
                title_bg, title_de, title_ru, title_en,
                price, area, type, status,
                location_bg, location_de, location_ru, location_en,
                address, description_bg, description_de, description_ru, description_en,
                created_at
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                NOW()
            )
        ");
        
        $stmt->execute([
            $title_bg, $title_de, $title_ru, $title_en,
            $price, $area, $type, $status,
            $location_bg, $location_de, $location_ru, $location_en,
            $address, $description_bg, $description_de, $description_ru, $description_en
        ]);
        
        $id = $db->lastInsertId();
    }

    header('Location: index.php');
    exit;
}

// Зареждане на данните за имота
$stmt = $db->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$id]);
$property = $stmt->fetch();

if (!$property) {
    header('Location: index.php');
    exit;
}

// Зареждане на снимките на имота
$stmt = $db->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_main DESC");
$stmt->execute([$id]);
$images = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактиране на имот - Industrial Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/rl33op7p1ovbmtd2ewd4q42187w17ttu70cufk3qwe146ufe/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '.tinymce-editor',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pageembed permanentpen footnotes advtemplate advtable advcode editimage tableofcontents mergetags powerpaste tinymcespellchecker autocorrect typography inlinecss',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            tinycomments_mode: 'embedded',
            tinycomments_author: 'Admin',
            mergetags_list: [
                { value: 'First.Name', title: 'First Name' },
                { value: 'Email', title: 'Email' },
            ],
            height: 500,
            language: 'bg',
            branding: false,
            promotion: false
        });
    </script>
    <style>
        .image-preview {
            position: relative;
            margin-bottom: 1rem;
        }
        .image-preview .delete-image {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            padding: 0.25rem;
            cursor: pointer;
        }
        .upload-preview {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="../images/logo.svg" alt="Logo" style="height: 32px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Начало</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="properties.php">Имоти</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inquiries.php">Запитвания</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Изход
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Редактиране на имот</h5>
                        <div>
                            <a href="edit_property.php?id=<?php echo $id; ?>&action=duplicate" 
                               class="btn btn-success btn-sm" 
                               onclick="return confirm('Сигурни ли сте, че искате да дупликирате този имот?')">
                                <i class="bi bi-files"></i> Дупликирай
                            </a>
                            <a href="index.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Назад
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="propertyForm" method="post" action="ajax/save-property.php" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Заглавие (BG)</label>
                                    <input type="text" class="form-control" name="title_bg" value="<?php echo htmlspecialchars($property['title_bg']); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Заглавие (DE)</label>
                                    <input type="text" class="form-control" name="title_de" value="<?php echo htmlspecialchars($property['title_de']); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Заглавие (RU)</label>
                                    <input type="text" class="form-control" name="title_ru" value="<?php echo htmlspecialchars($property['title_ru']); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Заглавие (EN)</label>
                                    <input type="text" class="form-control" name="title_en" value="<?php echo htmlspecialchars($property['title_en']); ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Цена (€)</label>
                                    <input type="number" class="form-control" name="price" value="<?php echo $property['price']; ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Площ (м²)</label>
                                    <input type="number" class="form-control" name="area" value="<?php echo $property['area']; ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Тип имот</label>
                                    <select class="form-select" name="type" required>
                                        <option value="">Изберете тип</option>
                                        <option value="manufacturing" <?php echo $property['type'] === 'manufacturing' ? 'selected' : ''; ?>>Производствени сгради</option>
                                        <option value="logistics" <?php echo $property['type'] === 'logistics' ? 'selected' : ''; ?>>Логистични центрове</option>
                                        <option value="office" <?php echo $property['type'] === 'office' ? 'selected' : ''; ?>>Офис сгради</option>
                                        <option value="logistics_park" <?php echo $property['type'] === 'logistics_park' ? 'selected' : ''; ?>>Логистични паркове</option>
                                        <option value="specialized" <?php echo $property['type'] === 'specialized' ? 'selected' : ''; ?>>Специализирани имоти</option>
                                        <option value="logistics_terminal" <?php echo $property['type'] === 'logistics_terminal' ? 'selected' : ''; ?>>Логистични терминали</option>
                                        <option value="land" <?php echo $property['type'] === 'land' ? 'selected' : ''; ?>>Земя за строеж</option>
                                        <option value="food_industry" <?php echo $property['type'] === 'food_industry' ? 'selected' : ''; ?>>Хранителна индустрия</option>
                                        <option value="heavy_industry" <?php echo $property['type'] === 'heavy_industry' ? 'selected' : ''; ?>>Тежка индустрия</option>
                                        <option value="tech_industry" <?php echo $property['type'] === 'tech_industry' ? 'selected' : ''; ?>>Технологични индустрии</option>
                                        <option value="hotels" <?php echo $property['type'] === 'hotels' ? 'selected' : ''; ?>>Хотели</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Статус</label>
                                    <select class="form-select" name="status" required>
                                        <option value="available" <?php echo $property['status'] === 'available' ? 'selected' : ''; ?>>Свободен</option>
                                        <option value="reserved" <?php echo $property['status'] === 'reserved' ? 'selected' : ''; ?>>Резервиран</option>
                                        <option value="rented" <?php echo $property['status'] === 'rented' ? 'selected' : ''; ?>>Отдаден</option>
                                        <option value="sold" <?php echo $property['status'] === 'sold' ? 'selected' : ''; ?>>Продаден</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Локация (BG)</label>
                                    <input type="text" class="form-control" name="location_bg" value="<?php echo htmlspecialchars($property['location_bg']); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Локация (DE)</label>
                                    <input type="text" class="form-control" name="location_de" value="<?php echo htmlspecialchars($property['location_de']); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Локация (RU)</label>
                                    <input type="text" class="form-control" name="location_ru" value="<?php echo htmlspecialchars($property['location_ru']); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Локация (EN)</label>
                                    <input type="text" class="form-control" name="location_en" value="<?php echo htmlspecialchars($property['location_en']); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Адрес</label>
                                <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($property['address']); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Описание (BG)</label>
                                <textarea class="form-control tinymce-editor" name="description_bg"><?php echo htmlspecialchars($property['description_bg']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Описание (DE)</label>
                                <textarea class="form-control tinymce-editor" name="description_de"><?php echo htmlspecialchars($property['description_de']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Описание (RU)</label>
                                <textarea class="form-control tinymce-editor" name="description_ru"><?php echo htmlspecialchars($property['description_ru']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Описание (EN)</label>
                                <textarea class="form-control tinymce-editor" name="description_en"><?php echo htmlspecialchars($property['description_en']); ?></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="featured" name="featured" value="1" <?php echo ($property['featured'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">
                                        Показвай в началната страница
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Ако е избрано, имотът ще се показва в началната страница
                                    </small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="pdf_flyer">PDF Експозе</label>
                                <?php if (!empty($property['pdf_flyer'])): ?>
                                    <div class="mb-2 d-flex align-items-center">
                                        <a href="/uploads/flyers/<?php echo htmlspecialchars($property['pdf_flyer']); ?>" target="_blank" class="me-2">
                                            Текущо PDF експозе
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removePdfFlyer(<?php echo $property['id']; ?>)">
                                            <i class="fas fa-trash"></i> Изтрий
                                        </button>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="pdf_flyer" name="pdf_flyer" accept=".pdf">
                                <small class="form-text text-muted">Изберете PDF файл за експозе на имота. Максимален размер: 5MB</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Качване на нови снимки</label>
                                <input type="file" class="form-control" name="images[]" multiple accept="image/*">
                                <small class="text-muted">Можете да изберете няколко снимки наведнъж</small>
                            </div>

                            <div class="mb-4">
                                <h6>Текущи снимки</h6>
                                <div class="row g-3">
                                    <?php foreach ($images as $image): ?>
                                    <div class="col-md-3">
                                        <div class="card">
                                            <div class="image-preview">
                                                <img src="../uploads/properties/<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                     class="card-img-top upload-preview" alt="Property Image">
                                                <button type="submit" name="delete_image" value="<?php echo $image['id']; ?>" 
                                                        class="btn btn-danger btn-sm delete-image"
                                                        onclick="return confirm('Сигурни ли сте, че искате да изтриете тази снимка?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" 
                                                           name="main_image" value="<?php echo $image['id']; ?>"
                                                           <?php echo $image['is_main'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">Главна снимка</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Запази промените
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('propertyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Показваме индикатор за зареждане
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="bi bi-hourglass"></i> Запазване...';
            
            // Изпращаме формата
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Показваме съобщение за успех
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Успех!</strong> ${data.message || 'Имотът беше обновен успешно.'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    this.insertBefore(alert, this.firstChild);
                    
                    // Пренасочваме към списъка с имоти след 1 секунда
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Възникна грешка при запазване на имота');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Показваме съобщение за грешка
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Грешка!</strong> ${error.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                this.insertBefore(alert, this.firstChild);
            })
            .finally(() => {
                // Възстановяваме бутона
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });

        // Добавяме обработка на промяна на главна снимка
        document.querySelectorAll('input[name="main_image"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const formData = new FormData();
                formData.append('main_image', this.value);
                formData.append('id', document.querySelector('input[name="id"]').value);
                
                // Показваме индикатор за зареждане
                const imageCard = this.closest('.card');
                imageCard.style.opacity = '0.5';
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        // Обновяваме изгледа
                        document.querySelectorAll('.card').forEach(card => {
                            card.style.opacity = '1';
                        });
                        
                        // Показваме съобщение за успех
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show';
                        alert.innerHTML = `
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Успех!</strong> Главната снимка беше променена успешно.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.querySelector('.card-body').insertBefore(alert, document.querySelector('.card-body').firstChild);
                        
                        // Скриваме съобщението след 3 секунди
                        setTimeout(() => {
                            alert.remove();
                        }, 3000);
                    } else {
                        throw new Error('Възникна грешка при промяна на главната снимка');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    imageCard.style.opacity = '1';
                    
                    // Показваме съобщение за грешка
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger alert-dismissible fade show';
                    alert.innerHTML = `
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Грешка!</strong> ${error.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('.card-body').insertBefore(alert, document.querySelector('.card-body').firstChild);
                });
            });
        });

        function removePdfFlyer(propertyId) {
            if (confirm('Сигурни ли сте, че искате да изтриете PDF експозето?')) {
                fetch('/admin/ajax/remove-pdf-flyer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        property_id: propertyId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Възникна грешка при изтриването на PDF експозето');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Възникна грешка при изтриването на PDF експозето');
                });
            }
        }
    </script>
</body>
</html> 