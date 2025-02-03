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
$where = "1=1";
$params = [];

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where .= " AND type = :type";
    $params[':type'] = $_GET['type'];
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where .= " AND status = :status";
    $params[':status'] = $_GET['status'];
}

if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $where .= " AND price >= :min_price";
    $params[':min_price'] = (float)$_GET['min_price'];
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where .= " AND price <= :max_price";
    $params[':max_price'] = (float)$_GET['max_price'];
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

// Общ брой имоти
$countStmt = $db->prepare("SELECT COUNT(*) FROM properties WHERE $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

// Вземане на имотите
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
$properties = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['menu_properties']; ?> - <?php echo $lang['site_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="properties-page">
        <!-- Филтри -->
        <section class="filters-section">
            <div class="container">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label for="type"><?php echo $lang['property_type']; ?></label>
                        <select name="type" id="type">
                            <option value=""><?php echo $lang['all_types']; ?></option>
                            <option value="industrial" <?php echo isset($_GET['type']) && $_GET['type'] === 'industrial' ? 'selected' : ''; ?>><?php echo $lang['industrial']; ?></option>
                            <option value="warehouse" <?php echo isset($_GET['type']) && $_GET['type'] === 'warehouse' ? 'selected' : ''; ?>><?php echo $lang['warehouse']; ?></option>
                            <option value="logistics" <?php echo isset($_GET['type']) && $_GET['type'] === 'logistics' ? 'selected' : ''; ?>><?php echo $lang['logistics']; ?></option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="status">Статус</label>
                        <select name="status" id="status">
                            <option value="">Всички</option>
                            <option value="sale" <?php echo isset($_GET['status']) && $_GET['status'] === 'sale' ? 'selected' : ''; ?>><?php echo $lang['sale']; ?></option>
                            <option value="rent" <?php echo isset($_GET['status']) && $_GET['status'] === 'rent' ? 'selected' : ''; ?>><?php echo $lang['rent']; ?></option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label><?php echo $lang['property_price']; ?></label>
                        <div class="range-inputs">
                            <input type="number" name="min_price" placeholder="Min €" value="<?php echo $_GET['min_price'] ?? ''; ?>">
                            <input type="number" name="max_price" placeholder="Max €" value="<?php echo $_GET['max_price'] ?? ''; ?>">
                        </div>
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
        <section class="properties-section">
            <div class="container">
                <div class="properties-grid">
                    <?php foreach ($properties as $property): ?>
                    <div class="property-card">
                        <?php if ($property['main_image']): ?>
                        <div class="property-image">
                            <img src="uploads/properties/<?php echo htmlspecialchars($property['main_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($property['title_' . $current_lang]); ?>">
                            <span class="property-status status-<?php echo $property['status']; ?>">
                                <?php echo $lang[$property['status']]; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="property-content">
                            <h3><?php echo htmlspecialchars($property['title_' . $current_lang]); ?></h3>
                            <div class="property-details">
                                <span class="price"><?php echo number_format($property['price'], 0, ',', ' '); ?> €</span>
                                <span class="area"><?php echo $property['area']; ?> m²</span>
                            </div>
                            <div class="property-location">
                                <i class="icon-location"></i>
                                <?php echo htmlspecialchars($property['location_' . $current_lang]); ?>
                            </div>
                            <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-outline">
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
                        <a href="?page=<?php echo $i; ?><?php echo isset($_GET['type']) ? '&type=' . htmlspecialchars($_GET['type']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?>" 
                           class="<?php echo $page === $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html> 