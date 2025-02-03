<?php
require_once 'includes/header.php';
use App\Database;

$db = Database::getInstance()->getConnection();
$property = null;
$images = [];
$features = [];

// Ако редактираме съществуващ имот
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Вземане на данните за имота
    $stmt = $db->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $property = $stmt->fetch();
    
    if ($property) {
        // Вземане на снимките
        $imgStmt = $db->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY sort_order");
        $imgStmt->execute([$id]);
        $images = $imgStmt->fetchAll();
        
        // Вземане на характеристиките
        $featStmt = $db->prepare("SELECT * FROM property_features WHERE property_id = ?");
        $featStmt->execute([$id]);
        $features = $featStmt->fetchAll();
    }
}

// Обработка на формата
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        $data = [
            'type' => $_POST['type'],
            'status' => $_POST['status'],
            'title_bg' => $_POST['title_bg'],
            'title_de' => $_POST['title_de'],
            'title_ru' => $_POST['title_ru'],
            'description_bg' => $_POST['description_bg'],
            'description_de' => $_POST['description_de'],
            'description_ru' => $_POST['description_ru'],
            'price' => $_POST['price'],
            'area' => $_POST['area'],
            'location_bg' => $_POST['location_bg'],
            'location_de' => $_POST['location_de'],
            'location_ru' => $_POST['location_ru'],
            'latitude' => $_POST['latitude'],
            'longitude' => $_POST['longitude'],
            'featured' => isset($_POST['featured']) ? 1 : 0
        ];
        
        if (isset($_GET['id'])) {
            // Обновяване на съществуващ имот
            $sql = "UPDATE properties SET 
                    type = :type, status = :status,
                    title_bg = :title_bg, title_de = :title_de, title_ru = :title_ru,
                    description_bg = :description_bg, description_de = :description_de, description_ru = :description_ru,
                    price = :price, area = :area,
                    location_bg = :location_bg, location_de = :location_de, location_ru = :location_ru,
                    latitude = :latitude, longitude = :longitude,
                    featured = :featured
                    WHERE id = :id";
            $data['id'] = $_GET['id'];
            
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
            $propertyId = $_GET['id'];
        } else {
            // Добавяне на нов имот
            $sql = "INSERT INTO properties 
                    (type, status, title_bg, title_de, title_ru, description_bg, description_de, description_ru,
                     price, area, location_bg, location_de, location_ru, latitude, longitude, featured)
                    VALUES 
                    (:type, :status, :title_bg, :title_de, :title_ru, :description_bg, :description_de, :description_ru,
                     :price, :area, :location_bg, :location_de, :location_ru, :latitude, :longitude, :featured)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
            $propertyId = $db->lastInsertId();
        }
        
        // Обработка на качените снимки
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = '../uploads/properties/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $filename = uniqid() . '_' . $_FILES['images']['name'][$key];
                    $uploadFile = $uploadDir . $filename;
                    
                    if (move_uploaded_file($tmp_name, $uploadFile)) {
                        $isMain = isset($_POST['main_image']) && $_POST['main_image'] == $key;
                        $stmt = $db->prepare("INSERT INTO property_images (property_id, image_path, is_main) VALUES (?, ?, ?)");
                        $stmt->execute([$propertyId, $filename, $isMain]);
                    }
                }
            }
        }
        
        // Обработка на характеристиките
        if (!empty($_POST['features'])) {
            // Изтриване на старите характеристики
            $db->prepare("DELETE FROM property_features WHERE property_id = ?")->execute([$propertyId]);
            
            foreach ($_POST['features'] as $feature) {
                if (!empty($feature['name_bg'])) {
                    $stmt = $db->prepare("INSERT INTO property_features 
                        (property_id, feature_name_bg, feature_name_de, feature_name_ru, 
                         feature_value_bg, feature_value_de, feature_value_ru)
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $propertyId,
                        $feature['name_bg'], $feature['name_de'], $feature['name_ru'],
                        $feature['value_bg'], $feature['value_de'], $feature['value_ru']
                    ]);
                }
            }
        }
        
        $db->commit();
        $success = "Имотът беше успешно " . (isset($_GET['id']) ? "обновен" : "добавен");
        
        if (!isset($_GET['id'])) {
            header("Location: property-edit.php?id=" . $propertyId);
            exit;
        }
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Възникна грешка: " . $e->getMessage();
    }
}
?>

<div class="property-edit-page">
    <div class="page-header">
        <h1><?php echo isset($_GET['id']) ? 'Редактиране на имот' : 'Добавяне на нов имот'; ?></h1>
        <a href="properties.php" class="btn btn-secondary">Назад към списъка</a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="property-form">
        <!-- Основна информация -->
        <div class="form-section">
            <h2>Основна информация</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Тип имот</label>
                    <select name="type" id="type" required>
                        <option value="industrial" <?php echo isset($property) && $property['type'] === 'industrial' ? 'selected' : ''; ?>>Промишлен</option>
                        <option value="warehouse" <?php echo isset($property) && $property['type'] === 'warehouse' ? 'selected' : ''; ?>>Склад</option>
                        <option value="logistics" <?php echo isset($property) && $property['type'] === 'logistics' ? 'selected' : ''; ?>>Логистичен</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Статус</label>
                    <select name="status" id="status" required>
                        <option value="sale" <?php echo isset($property) && $property['status'] === 'sale' ? 'selected' : ''; ?>>Продажба</option>
                        <option value="rent" <?php echo isset($property) && $property['status'] === 'rent' ? 'selected' : ''; ?>>Наем</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Цена (€)</label>
                    <input type="number" step="0.01" name="price" id="price" required
                           value="<?php echo isset($property) ? $property['price'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="area">Площ (м²)</label>
                    <input type="number" step="0.01" name="area" id="area" required
                           value="<?php echo isset($property) ? $property['area'] : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1" 
                           <?php echo isset($property) && $property['featured'] ? 'checked' : ''; ?>>
                    Препоръчан имот
                </label>
            </div>
        </div>

        <!-- Многоезично съдържание -->
        <div class="form-section">
            <h2>Съдържание</h2>
            
            <!-- Табове за езици -->
            <div class="language-tabs">
                <button type="button" class="tab-btn active" data-lang="bg">Български</button>
                <button type="button" class="tab-btn" data-lang="de">Deutsch</button>
                <button type="button" class="tab-btn" data-lang="ru">Русский</button>
            </div>

            <!-- Съдържание за всеки език -->
            <div class="tab-content active" data-lang="bg">
                <div class="form-group">
                    <label for="title_bg">Заглавие (BG)</label>
                    <input type="text" name="title_bg" id="title_bg" required
                           value="<?php echo isset($property) ? $property['title_bg'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description_bg">Описание (BG)</label>
                    <textarea name="description_bg" id="description_bg" class="tinymce" required>
                        <?php echo isset($property) ? $property['description_bg'] : ''; ?>
                    </textarea>
                </div>
                
                <div class="form-group">
                    <label for="location_bg">Локация (BG)</label>
                    <input type="text" name="location_bg" id="location_bg" required
                           value="<?php echo isset($property) ? $property['location_bg'] : ''; ?>">
                </div>
            </div>

            <!-- Повторете за немски и руски -->
            <!-- ... -->
        </div>

        <!-- Снимки -->
        <div class="form-section">
            <h2>Снимки</h2>
            
            <div class="form-group">
                <label>Добави снимки</label>
                <input type="file" name="images[]" multiple accept="image/*">
            </div>

            <?php if (!empty($images)): ?>
            <div class="current-images">
                <?php foreach ($images as $image): ?>
                <div class="image-item">
                    <img src="../uploads/properties/<?php echo htmlspecialchars($image['image_path']); ?>" 
                         alt="Property image">
                    <label>
                        <input type="radio" name="main_image" value="<?php echo $image['id']; ?>"
                               <?php echo $image['is_main'] ? 'checked' : ''; ?>>
                        Главна снимка
                    </label>
                    <button type="button" class="btn btn-small btn-danger delete-image" 
                            data-id="<?php echo $image['id']; ?>">Изтрий</button>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Характеристики -->
        <div class="form-section">
            <h2>Характеристики</h2>
            
            <div id="features-container">
                <?php foreach ($features as $index => $feature): ?>
                <div class="feature-item">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="features[<?php echo $index; ?>][name_bg]" 
                                   placeholder="Име (BG)" value="<?php echo $feature['feature_name_bg']; ?>">
                        </div>
                        <div class="form-group">
                            <input type="text" name="features[<?php echo $index; ?>][value_bg]" 
                                   placeholder="Стойност (BG)" value="<?php echo $feature['feature_value_bg']; ?>">
                        </div>
                    </div>
                    <!-- Повторете за немски и руски -->
                    <button type="button" class="btn btn-small btn-danger remove-feature">Премахни</button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="btn btn-secondary" id="add-feature">Добави характеристика</button>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?php echo isset($_GET['id']) ? 'Запази промените' : 'Добави имот'; ?>
            </button>
        </div>
    </form>
</div>

<script>
// JavaScript за управление на табовете и динамично добавяне на характеристики
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация на TinyMCE
    tinymce.init({
        selector: '.tinymce',
        height: 300,
        plugins: 'lists link image table',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image'
    });

    // Табове
    const tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const lang = this.dataset.lang;
            
            // Активиране на таба
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Показване на съответното съдържание
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
                if (content.dataset.lang === lang) {
                    content.classList.add('active');
                }
            });
        });
    });

    // Добавяне на характеристика
    let featureIndex = <?php echo count($features); ?>;
    document.getElementById('add-feature').addEventListener('click', function() {
        const container = document.getElementById('features-container');
        const template = `
            <div class="feature-item">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="features[${featureIndex}][name_bg]" placeholder="Име (BG)">
                    </div>
                    <div class="form-group">
                        <input type="text" name="features[${featureIndex}][value_bg]" placeholder="Стойност (BG)">
                    </div>
                </div>
                <button type="button" class="btn btn-small btn-danger remove-feature">Премахни</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        featureIndex++;
    });

    // Премахване на характеристика
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-feature')) {
            e.target.closest('.feature-item').remove();
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 