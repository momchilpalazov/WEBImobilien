<?php
require_once "../config/database.php";
use App\Database;
require_once "../includes/header.php";

$db = Database::getInstance()->getConnection();

// Параметри за търсене
$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$min_area = $_GET['min_area'] ?? '';
$max_area = $_GET['max_area'] ?? '';
$location = $_GET['location'] ?? '';

// SQL заявка с филтри
$sql = "
    SELECT p.*, pi.image_path, COUNT(*) OVER (PARTITION BY p.type) as type_count 
    FROM properties p
    LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1
    WHERE 1=1
";
$params = [];

if ($type) {
    $sql .= " AND p.type = ?";
    $params[] = $type;
}

if ($status) {
    $sql .= " AND p.status = ?";
    $params[] = $status;
}

if ($min_price) {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
}

if ($max_price) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
}

if ($min_area) {
    $sql .= " AND p.area >= ?";
    $params[] = $min_area;
}

if ($max_area) {
    $sql .= " AND p.area <= ?";
    $params[] = $max_area;
}

if ($location) {
    $sql .= " AND (p.location_bg LIKE ? OR p.address LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
}

$sql .= " ORDER BY p.type, p.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();
?>

<div class="container mt-5">
    <!-- Търсачка -->
    <div class="search-section bg-light p-4 rounded mb-5">
        <h2 class="mb-4"><?php echo $translations['search']['title']; ?></h2>
        <form action="/search.php" method="GET" class="search-form">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><?php echo $translations['property']['type']['label']; ?></label>
                    <select name="type" class="form-select">
                        <option value=""><?php echo $translations['search']['all_types']; ?></option>
                        <?php foreach ($translations['property']['type'] as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo $type === $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label"><?php echo $translations['property']['status']['label']; ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?php echo $translations['search']['all_statuses']; ?></option>
                        <?php foreach ($translations['property']['status'] as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo $status === $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label"><?php echo $translations['search']['min_price']; ?></label>
                    <input type="number" name="min_price" class="form-control" value="<?php echo $min_price; ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label"><?php echo $translations['search']['max_price']; ?></label>
                    <input type="number" name="max_price" class="form-control" value="<?php echo $max_price; ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label"><?php echo $translations['search']['min_area']; ?></label>
                    <input type="number" name="min_area" class="form-control" value="<?php echo $min_area; ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label"><?php echo $translations['search']['max_area']; ?></label>
                    <input type="number" name="max_area" class="form-control" value="<?php echo $max_area; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label"><?php echo $translations['search']['location']; ?></label>
                    <input type="text" name="location" class="form-control" value="<?php echo $location; ?>">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $translations['search']['submit']; ?>
                    </button>
                    <a href="/search.php" class="btn btn-outline-secondary">
                        <?php echo $translations['search']['clear']; ?>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Резултати -->
    <div class="search-results">
        <?php if (empty($properties)): ?>
            <div class="alert alert-info">
                <?php echo $translations['search']['no_results']; ?>
            </div>
        <?php else: ?>
            <h3 class="mb-4">
                <?php echo $translations['search']['results_count']; ?>: <?php echo count($properties); ?>
            </h3>
            
            <?php 
            $current_type = null;
            foreach ($properties as $property): 
                if ($current_type !== $property['type']): 
                    if ($current_type !== null) {
                        echo '</div>'; // Затваряме предишния row
                    }
            ?>
                    <h4 class="mt-4 mb-3">
                        <?php echo $translations['property']['type'][$property['type']]; ?> 
                        <small class="text-muted">(<?php echo $property['type_count']; ?>)</small>
                    </h4>
                    <div class="row">
            <?php 
                endif;
                $current_type = $property['type'];
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card property-card h-100">
                        <!-- Код за показване на имот (същият като в properties.php) -->
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!empty($properties)): ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?> 