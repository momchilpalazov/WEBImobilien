<?php
require_once '../config/database.php';
use App\Database;
require_once '../includes/language.php';

header('Content-Type: application/json');

// Вземаме параметрите
$types = $_GET['types'] ?? [];
$status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$min_area = filter_input(INPUT_GET, 'min_area', FILTER_VALIDATE_FLOAT);
$max_area = filter_input(INPUT_GET, 'max_area', FILTER_VALIDATE_FLOAT);
$min_price = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT);
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);
$features = $_GET['features'] ?? [];
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'date_desc';
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

// SQL заявка
$sql = "
    SELECT p.*, pi.image_path, COUNT(*) OVER (PARTITION BY p.type) as type_count 
    FROM properties p
    LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1
    WHERE 1=1
";
$params = [];

// Добавяме филтрите
if (!empty($types)) {
    $placeholders = str_repeat('?,', count($types) - 1) . '?';
    $sql .= " AND p.type IN ($placeholders)";
    $params = array_merge($params, $types);
}

if ($status) {
    $sql .= " AND p.status = ?";
    $params[] = $status;
}

if ($min_area) {
    $sql .= " AND p.area >= ?";
    $params[] = $min_area;
}

if ($max_area) {
    $sql .= " AND p.area <= ?";
    $params[] = $max_area;
}

if ($min_price) {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
}

if ($max_price) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
}

if (!empty($features)) {
    foreach ($features as $feature) {
        $sql .= " AND p.{$feature} = 1";
    }
}

// Сортиране
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'area_asc':
        $sql .= " ORDER BY p.area ASC";
        break;
    case 'area_desc':
        $sql .= " ORDER BY p.area DESC";
        break;
    case 'date_asc':
        $sql .= " ORDER BY p.created_at ASC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Общ брой резултати
    $count_sql = str_replace('p.*, pi.image_path', 'COUNT(*) as total', $sql);
    $stmt = $db->prepare($count_sql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Добавяме пагинация
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    
    // Вземаме резултатите
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Подготвяме HTML за резултатите
    ob_start();
    $current_type = null;
    foreach ($properties as $property): 
        if ($current_type !== $property['type']): 
            if ($current_type !== null) {
                echo '</div>'; // Затваряме предишния row
            }
    ?>
            <h3 class="mt-4 mb-3">
                <?php echo $translations['property']['type'][$property['type']]; ?> 
                <small class="text-muted">(<?php echo $property['type_count']; ?>)</small>
            </h3>
            <div class="row">
    <?php 
        endif;
        $current_type = $property['type'];
    ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card property-card h-100">
                    <img src="<?php echo $property['image_path'] ? '../uploads/properties/' . $property['image_path'] : '../images/no-image.jpg'; ?>" 
                         class="card-img-top" alt="<?php echo $property["title_{$current_language}"]; ?>"
                         loading="lazy">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $property["title_{$current_language}"]; ?></h5>
                        <p class="card-text">
                            <strong>Price:</strong> €<?php echo number_format($property['price']); ?>
                        </p>
                        <p class="card-text">
                            <strong>Area:</strong> <?php echo number_format($property['area']); ?> m²
                        </p>
                        <a href="/property.php?id=<?php echo $property['id']; ?>" class="btn btn-outline-primary">
                            Details
                        </a>
                    </div>
                </div>
            </div>
    <?php endforeach; 
    if (!empty($properties)): ?>
        </div> <!-- Затваряме последния row -->
    <?php endif;
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'total' => $total,
        'pages' => ceil($total / $per_page),
        'current_page' => $page
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while loading properties'
    ]);
} 