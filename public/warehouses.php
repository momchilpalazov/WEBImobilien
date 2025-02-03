<?php
session_start();

require_once "../config/database.php";
use App\Database;
require_once "../includes/functions.php";

// Зареждане на езиковите файлове
$default_lang = 'bg';
$allowed_languages = ['bg', 'de', 'ru'];
$current_lang = isset($_GET['lang']) && in_array($_GET['lang'], $allowed_languages) ? $_GET['lang'] : $default_lang;

require_once "../languages/{$current_lang}.php";

$db = Database::getInstance()->getConnection();

// Филтриране
$where = "type IN ('warehouse', 'logistics')";
$params = [];

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where .= " AND status = :status";
    $params[':status'] = $_GET['status'];
}

if (isset($_GET['min_area']) && !empty($_GET['min_area'])) {
    $where .= " AND area >= :min_area";
    $params[':min_area'] = (float)$_GET['min_area'];
}

if (isset($_GET['max_area']) && !empty($_GET['max_area'])) {
    $where .= " AND area <= :max_area";
    $params[':max_area'] = (float)$_GET['max_area'];
}

// Пагинация
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Общ брой складове
$countStmt = $db->prepare("SELECT COUNT(*) FROM properties WHERE $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

// Вземане на складове
$stmt = $db->prepare("
    SELECT p.*, 
           (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image
    FROM properties p 
    WHERE $where
    ORDER BY p.created_at DESC 
    LIMIT :offset, :perPage
");

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$warehouses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['menu_warehouses']; ?> - <?php echo $lang['site_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="warehouses-page">
        <!-- Hero секция -->
        <section class="page-hero">
            <div class="container">
                <h1><?php echo $lang['logistics_title']; ?></h1>
                <p><?php echo $lang['logistics_subtitle']; ?></p>
            </div>
        </section>

        <!-- Филтри -->
        <section class="filters-section">
            <div class="container">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label for="status">Статус</label>
                        <select name="status" id="status">
                            <option value="">Всички</option>
                            <option value="sale" <?php echo isset($_GET['status']) && $_GET['status'] === 'sale' ? 'selected' : ''; ?>><?php echo $lang['sale']; ?></option>
                            <option value="rent" <?php echo isset($_GET['status']) && $_GET['status'] === 'rent' ? 'selected' : ''; ?>><?php echo $lang['rent']; ?></option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label><?php echo $lang['property_area']; ?></label>
                        <div class="range-inputs">
                            <input type="number" name="min_area" placeholder="Min m²" value="<?php echo $_GET['min_area'] ?? ''; ?>">
                            <input type="number" name="max_area" placeholder="Max m²" value="<?php echo $_GET['max_area'] ?? ''; ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Търси</button>
                </form>
            </div>
        </section>

        <!-- Резултати -->
        <section class="warehouses-section">
            <div class="container">
                <div class="warehouses-grid">
                    <?php foreach ($warehouses as $warehouse): ?>
                    <div class="warehouse-card">
                        <?php if ($warehouse['main_image']): ?>
                        <div class="warehouse-image">
                            <img src="uploads/properties/<?php echo htmlspecialchars($warehouse['main_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($warehouse['title_' . $current_lang]); ?>">
                            <span class="property-status status-<?php echo $warehouse['status']; ?>">
                                <?php echo $lang[$warehouse['status']]; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="warehouse-content">
                            <h3><?php echo htmlspecialchars($warehouse['title_' . $current_lang]); ?></h3>
                            <div class="warehouse-details">
                                <span class="area"><?php echo $warehouse['area']; ?> m²</span>
                                <span class="type"><?php echo $lang[$warehouse['type']]; ?></span>
                            </div>
                            <div class="warehouse-location">
                                <i class="icon-location"></i>
                                <?php echo htmlspecialchars($warehouse['location_' . $current_lang]); ?>
                            </div>
                            <a href="property.php?id=<?php echo $warehouse['id']; ?>" class="btn btn-outline">
                                <?php echo $lang['view_more']; ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Пагинация -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?>" 
                           class="<?php echo $page === $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Предимства -->
        <section class="features-section">
            <div class="container">
                <div class="features-grid">
                    <div class="feature-item">
                        <img src="assets/images/icons/location.svg" alt="Location">
                        <h3>Стратегическо местоположение</h3>
                        <p>Близост до ключови транспортни артерии</p>
                    </div>
                    <div class="feature-item">
                        <img src="assets/images/icons/security.svg" alt="Security">
                        <h3>24/7 Охрана</h3>
                        <p>Пълна сигурност на вашата собственост</p>
                    </div>
                    <div class="feature-item">
                        <img src="assets/images/icons/support.svg" alt="Support">
                        <h3>Професионална поддръжка</h3>
                        <p>Денонощна техническа поддръжка</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html> 