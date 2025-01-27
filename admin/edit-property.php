<?php
// Предотвратяване на кеширането
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'includes/auth.php';
require_once "../src/Database/Database.php";
require_once "../config/database.php";

use App\Database\Database;

// Временно добавяне на тестов админ акаунт в сесията
if (!isset($_SESSION['admin_user'])) {
    $_SESSION['admin_user'] = [
        'id' => 1,
        'username' => 'admin',
        'role' => 'admin'
    ];
}

checkAuth();
checkPermission('manage_properties');

$db = Database::getInstance()->getConnection();

$id = $_GET['id'] ?? null;
$property = null;
$images = [];
$documents = [];

// Ако имаме ID, зареждаме съществуващ имот
if ($id) {
    // Зареждане на имота
    $stmt = $db->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $property = $stmt->fetch();
    
    if (!$property) {
        header('Location: properties.php');
        exit;
    }
    
    // Зареждане на снимките
    $stmt = $db->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY sort_order");
    $stmt->execute([$id]);
    $images = $stmt->fetchAll();
    
    // Зареждане на документите
    $stmt = $db->prepare("SELECT * FROM property_documents WHERE property_id = ?");
    $stmt->execute([$id]);
    $documents = $stmt->fetchAll();
}

$page_title = $id ? 'Редактиране на имот' : 'Добавяне на имот';
$form_action = $id ? 'ajax/save-property.php' : 'ajax/add-property.php';

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
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Валидация на типа имот
    $valid_types = [
        'manufacturing', 'logistics', 'office', 'logistics_park',
        'specialized', 'logistics_terminal', 'land', 'food_industry',
        'heavy_industry', 'tech_industry', 'hotels'
    ];

    if (!in_array($type, $valid_types)) {
        die('Невалиден тип имот');
    }

    if ($id) {
        // Обновяване на съществуващ имот
        $stmt = $db->prepare("
            UPDATE properties 
            SET title_bg = ?, title_de = ?, title_ru = ?, title_en = ?,
                price = ?, area = ?, type = ?, status = ?,
                location_bg = ?, location_de = ?, location_ru = ?, location_en = ?,
                address = ?, description_bg = ?, description_de = ?, description_ru = ?, description_en = ?,
                featured = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $title_bg, $title_de, $title_ru, $title_en,
            $price, $area, $type, $status,
            $location_bg, $location_de, $location_ru, $location_en,
            $address, $description_bg, $description_de, $description_ru, $description_en,
            $featured, $id
        ]);
    } else {
        // Добавяне на нов имот
        $stmt = $db->prepare("
            INSERT INTO properties (
                title_bg, title_de, title_ru, title_en,
                price, area, type, status,
                location_bg, location_de, location_ru, location_en,
                address, description_bg, description_de, description_ru, description_en,
                featured, created_at
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, NOW()
            )
        ");
        
        $stmt->execute([
            $title_bg, $title_de, $title_ru, $title_en,
            $price, $area, $type, $status,
            $location_bg, $location_de, $location_ru, $location_en,
            $address, $description_bg, $description_de, $description_ru, $description_en,
            $featured
        ]);
        
        $id = $db->lastInsertId();
    }
}

// Ако е заявка за дупликиране
if ($action === 'duplicate' && $property_id > 0) {
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
    $stmt->execute([$property_id]);
    
    $new_property_id = $db->lastInsertId();
    
    // Копиране на снимките
    $stmt = $db->prepare("
        INSERT INTO property_images (property_id, image_path, is_main, sort_order)
        SELECT ?, image_path, is_main, sort_order
        FROM property_images WHERE property_id = ?
    ");
    $stmt->execute([$new_property_id, $property_id]);
    
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панел - <?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/admin.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css">
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
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-xxl">
            <a class="navbar-brand" href="index.php">
                <img src="../images/logo.svg" alt="Industrial Properties" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-speedometer2 me-2"></i>Табло
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link active" href="properties.php">
                            <i class="bi bi-building me-2"></i>Имоти
                        </a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link" href="inquiries.php">
                            <i class="bi bi-envelope me-2"></i>Запитвания
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-2"></i>Към сайта
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Изход
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid flex-grow-1 py-4">
        <div class="container-xxl">
            <?php if (isset($_GET['new']) && $_GET['new'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Имотът е създаден успешно!</strong> Сега можете да качите снимки за имота.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0"><?php echo $page_title; ?></h1>
                <div>
                    <?php if ($id): ?>
                    <a href="edit-property.php?id=<?php echo $id; ?>&action=duplicate" 
                       class="btn btn-success" 
                       onclick="return confirm('Сигурни ли сте, че искате да дупликирате този имот?')">
                        <i class="bi bi-files me-2"></i>Дупликирай
                    </a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Назад
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form id="propertyForm" action="<?php echo $form_action; ?>" method="post" enctype="multipart/form-data" data-validate>
                        <?php if ($id): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h4 class="mb-3">Основна информация</h4>
                                
                                <div class="mb-3">
                                    <label class="form-label">Заглавие</label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="title_bg" placeholder="Заглавие (BG)" required
                                                   value="<?php echo htmlspecialchars($property['title_bg'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="title_de" placeholder="Заглавие (DE)" required
                                                   value="<?php echo htmlspecialchars($property['title_de'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="title_ru" placeholder="Заглавие (RU)" required
                                                   value="<?php echo htmlspecialchars($property['title_ru'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="title_en" placeholder="Заглавие (EN)" required
                                                   value="<?php echo htmlspecialchars($property['title_en'] ?? ''); ?>">
                                </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Тип имот</label>
                                    <select class="form-select" name="type" required>
                                        <option value="">Изберете тип</option>
                                        <option value="manufacturing" <?php echo ($property['type'] ?? '') === 'manufacturing' ? 'selected' : ''; ?>>Производствени сгради</option>
                                        <option value="logistics" <?php echo ($property['type'] ?? '') === 'logistics' ? 'selected' : ''; ?>>Логистични центрове</option>
                                        <option value="office" <?php echo ($property['type'] ?? '') === 'office' ? 'selected' : ''; ?>>Офис сгради</option>
                                        <option value="logistics_park" <?php echo ($property['type'] ?? '') === 'logistics_park' ? 'selected' : ''; ?>>Логистични паркове</option>
                                        <option value="specialized" <?php echo ($property['type'] ?? '') === 'specialized' ? 'selected' : ''; ?>>Специализирани имоти</option>
                                        <option value="logistics_terminal" <?php echo ($property['type'] ?? '') === 'logistics_terminal' ? 'selected' : ''; ?>>Логистични терминали</option>
                                        <option value="land" <?php echo ($property['type'] ?? '') === 'land' ? 'selected' : ''; ?>>Земя за строеж</option>
                                        <option value="food_industry" <?php echo ($property['type'] ?? '') === 'food_industry' ? 'selected' : ''; ?>>Хранителна индустрия</option>
                                        <option value="heavy_industry" <?php echo ($property['type'] ?? '') === 'heavy_industry' ? 'selected' : ''; ?>>Тежка индустрия</option>
                                        <option value="tech_industry" <?php echo ($property['type'] ?? '') === 'tech_industry' ? 'selected' : ''; ?>>Технологични индустрии</option>
                                        <option value="hotels" <?php echo ($property['type'] ?? '') === 'hotels' ? 'selected' : ''; ?>>Хотели</option>
                                    </select>
                                    </div>
                                    
                                <div class="mb-3">
                                    <label class="form-label">Статус</label>
                                    <select class="form-select" name="status" required>
                                        <option value="">Изберете статус</option>
                                        <option value="available" <?php echo ($property['status'] ?? '') === 'available' ? 'selected' : ''; ?>>Свободен</option>
                                        <option value="reserved" <?php echo ($property['status'] ?? '') === 'reserved' ? 'selected' : ''; ?>>Резервиран</option>
                                        <option value="rented" <?php echo ($property['status'] ?? '') === 'rented' ? 'selected' : ''; ?>>Отдаден</option>
                                        <option value="sold" <?php echo ($property['status'] ?? '') === 'sold' ? 'selected' : ''; ?>>Продаден</option>
                                    </select>
                                    </div>
                                    
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Цена (€)</label>
                                        <input type="number" class="form-control" name="price" required min="0" step="0.01"
                                               value="<?php echo $property['price'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Площ (м²)</label>
                                        <input type="number" class="form-control" name="area" required min="0" step="0.01"
                                               value="<?php echo $property['area'] ?? ''; ?>">
                                    </div>
                                    </div>
                                    
                                <div class="mb-3">
                                    <label class="form-label">Локация</label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="location_bg" placeholder="Локация (BG)" required
                                                   value="<?php echo htmlspecialchars($property['location_bg'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="location_de" placeholder="Локация (DE)" required
                                                   value="<?php echo htmlspecialchars($property['location_de'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="location_ru" placeholder="Локация (RU)" required
                                                   value="<?php echo htmlspecialchars($property['location_ru'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="location_en" placeholder="Локация (EN)" required
                                                   value="<?php echo htmlspecialchars($property['location_en'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Адрес</label>
                                    <input type="text" class="form-control" name="address"
                                           value="<?php echo htmlspecialchars($property['address'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="featured" id="featured"
                                               <?php echo ($property['featured'] ?? false) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="featured">Featured имот</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h4 class="mb-3">Описание</h4>
                                
                                <div class="mb-3">
                                    <label class="form-label">Описание (BG)</label>
                                    <textarea class="form-control tinymce" name="description_bg" rows="5"><?php echo htmlspecialchars($property['description_bg'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Описание (DE)</label>
                                    <textarea class="form-control tinymce" name="description_de" rows="5"><?php echo htmlspecialchars($property['description_de'] ?? ''); ?></textarea>
                                    </div>
                                    
                                <div class="mb-3">
                                    <label class="form-label">Описание (RU)</label>
                                    <textarea class="form-control tinymce" name="description_ru" rows="5"><?php echo htmlspecialchars($property['description_ru'] ?? ''); ?></textarea>
                                    </div>
                                    
                                <div class="mb-3">
                                    <label class="form-label">Описание (EN)</label>
                                    <textarea class="form-control tinymce" name="description_en" rows="5"><?php echo htmlspecialchars($property['description_en'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                        <div class="card mt-4">
                                <div class="card-header">
                                <h4 class="mb-0">Снимки</h4>
                            </div>
                            <div class="card-body">
                                <?php if (!$id): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Първо запазете имота, за да можете да качвате снимки.
                                </div>
                                <?php else: ?>
                                <div id="imageUpload" class="dropzone mb-4">
                                    <div class="dz-message">
                                        Плъзнете снимки тук или кликнете за да изберете
                                    </div>
                                        </div>
                                        
                                <?php if (!empty($images)): ?>
                                <div class="row g-4">
                                    <?php foreach ($images as $image): ?>
                                    <div class="col-md-4 col-lg-3">
                                        <div class="image-preview">
                                            <img src="../uploads/properties/thumbnails/<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                 class="img-fluid rounded upload-preview" 
                                                 alt="Property image">
                                            <button type="button" class="btn btn-danger btn-sm delete-image"
                                                    onclick="deleteImage(<?php echo $image['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-muted mb-0">Все още няма качени снимки.</p>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i><?php echo $id ? 'Запази' : 'Добави'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/rl33op7p1ovbmtd2ewd4q42187w17ttu70cufk3qwe146ufe/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script src="js/admin.js?v=<?php echo time(); ?>"></script>
    <script>
        tinymce.init({
            selector: '.tinymce',
            height: 300,
        menubar: false,
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pageembed permanentpen footnotes advtemplate advtable advcode editimage tableofcontents mergetags powerpaste tinymcespellchecker autocorrect typography inlinecss',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        language: 'bg',
        branding: false,
        promotion: false,
        height: 500,
        setup: function(editor) {
            editor.on('change', function() {
                editor.save(); // Запазва съдържанието в textarea
                tinymce.triggerSave(); // Запазва всички редактори
            });
        },
        init_instance_callback: function(editor) {
            editor.on('blur', function() {
                editor.save();
                tinymce.triggerSave();
            });
        }
    });

    Dropzone.autoDiscover = false;

    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($id): ?>
        const dropzone = new Dropzone("#imageUpload", {
            url: "ajax/upload-images.php",
            paramName: "image",
            maxFilesize: 5,
            acceptedFiles: "image/*",
            addRemoveLinks: true,
            dictDefaultMessage: "Плъзнете снимки тук или кликнете за да изберете",
            dictRemoveFile: "Изтрий",
            dictCancelUpload: "Откажи",
            dictUploadCanceled: "Качването е отказано",
            dictFileTooBig: "Файлът е твърде голям ({{filesize}}MB). Максимален размер: {{maxFilesize}}MB.",
            params: {
                property_id: <?php echo $id; ?>
            },
            init: function() {
                this.on("success", function(file, response) {
                    console.log('Server response:', response);
                    try {
                        if (response.success) {
                            showNotification('Снимката е качена успешно', 'success');
                            // Изчакваме малко преди да презаредим страницата
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showNotification(response.message || 'Възникна грешка при качване', 'error');
                            this.removeFile(file);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showNotification('Грешка при обработка на отговора от сървъра', 'error');
                        this.removeFile(file);
                    }
                });

                this.on("error", function(file, errorMessage) {
                    console.error('Upload error:', errorMessage);
                    let message = 'Възникна грешка при качване';
                    if (typeof errorMessage === 'string') {
                        message = errorMessage;
                    } else if (errorMessage.message) {
                        message = errorMessage.message;
                    }
                    showNotification(message, 'error');
                    this.removeFile(file);
                });
            }
        });
        <?php endif; ?>
    });

    function deleteImage(imageId) {
        if (confirm('Сигурни ли сте, че искате да изтриете тази снимка?')) {
            fetch('ajax/delete-image.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ image_id: imageId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Снимката е изтрита успешно', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'Възникна грешка при изтриване на снимката', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Възникна грешка при изтриване на снимката', 'error');
            });
        }
    }
    </script>
</body>
</html> 