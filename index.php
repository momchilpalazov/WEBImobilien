<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

require_once 'config/database.php';
require_once 'src/Database/Database.php';
use App\Database\Database;

// Проверка за режим на поддръжка - преместваме я преди всичко останало
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
    $stmt->execute();
    $maintenance_mode = $stmt->fetchColumn();

    // Ако сайтът е в режим на поддръжка и потребителят не е админ, пренасочваме към maintenance.php
    if ($maintenance_mode === 'true' && (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true)) {
        header('Location: maintenance.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Maintenance mode check error: " . $e->getMessage());
}

$page_title = 'Industrial Properties - Home';
require_once 'includes/header.php';

// Вземаме избраните имоти
try {
    $db = Database::getInstance()->getConnection();
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

try {
    $featured_sql = "SELECT p.*, 
            COALESCE(
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1),
                (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY id ASC LIMIT 1)
            ) as image_path,
            p.pdf_flyer 
    FROM properties p
    WHERE p.featured = 1
    ORDER BY p.created_at DESC
    LIMIT 6";
    $featured_properties = $db->query($featured_sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Вземаме последно добавените имоти
try {
    $latest_properties = $db->query("
        SELECT p.*, pi.image_path 
        FROM properties p 
        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1
        ORDER BY p.created_at DESC 
        LIMIT 3
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Проверка на базовата функционалност
$config = [
    'database' => [
        'host' => 'localhost',
        'name' => 'imobilien',
        'user' => 'root',
        'pass' => '1'
    ]
];

try {
    // Тест на PHP версия
    echo "<h2>PHP Version: " . PHP_VERSION . "</h2>";
    
    // Тест на MySQL връзка
    $pdo = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset=utf8mb4",
        $config['database']['user'],
        $config['database']['pass']
    );
    echo "<h3>Database connection: Success</h3>";
    
    // Тест на composer autoload
    if (class_exists('\App\Interfaces\PropertyRepositoryInterface')) {
        echo "<h3>Composer autoload: Success</h3>";
    }
    
    // Тест на права за писане
    if (is_writable(__DIR__ . '/storage/logs')) {
        echo "<h3>Storage permissions: Success</h3>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
}
?>

<!-- Hero Section -->
<section class="hero-section py-5 bg-light">
    <div class="container-fluid px-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="title display-4 mb-0"><?php echo $translations['home']['hero_title'] ?? 'Industrial Properties'; ?></h1>
                <div class="heading-divider"></div>
                <p class="lead mb-4">
                    <?php echo $translations['home']['hero_text'] ?? 'Find your perfect industrial property'; ?>
                </p>
                <a href="/properties.php" class="btn btn-primary btn-lg">
                    <?php echo $translations['home']['view_all']; ?>
                </a>
            </div>

            <div class="col-md-6">
                <!-- Search Form -->
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-0"><?php echo $translations['search']['title']; ?></h5>
                        <div class="heading-divider"></div>
                        <form action="/properties.php" method="get">
                            <div class="mb-3">
                                <select name="type" class="form-select form-select-lg">
                                    <option value=""><?php echo $translations['search']['all_types']; ?></option>
                                    <?php foreach ($translations['property']['type'] as $key => $value): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <input type="number" name="min_area" class="form-control form-control-lg" placeholder="<?php echo $translations['search']['min_area']; ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_area" class="form-control form-control-lg" placeholder="<?php echo $translations['search']['max_area']; ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control form-control-lg" placeholder="<?php echo $translations['search']['min_price']; ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control form-control-lg" placeholder="<?php echo $translations['search']['max_price']; ?>">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <?php echo $translations['search']['submit']; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="featured-properties py-5">
    <div class="container-fluid px-4">
        <h2 class="title display-5 mb-0"><?php echo $translations['home']['featured_properties']; ?></h2>
        <div class="heading-divider"></div>
        <div class="row g-4">
            <?php foreach ($featured_properties as $property): ?>
                <div class="col-xl-4 col-lg-6">
                    <div class="card property-card h-100">
                        <div class="position-relative">
                            <img src="<?php echo $property['image_path'] ? 'uploads/properties/' . $property['image_path'] : 'images/no-image.jpg'; ?>" 
                                 class="card-img-top" alt="<?php echo $property["title_{$current_language}"]; ?>"
                                 style="height: 300px; object-fit: cover;">
                            <?php if (isset($property['pdf_flyer']) && !empty(trim($property['pdf_flyer']))): ?>
                                <a href="uploads/flyers/<?php echo htmlspecialchars($property['pdf_flyer']); ?>" target="_blank" class="pdf-flyer-link">
                                    <i class="fas fa-file-pdf"></i> 
                                    <?php 
                                    $pdf_text = [
                                        'bg' => 'Виж експозе',
                                        'en' => 'View brochure',
                                        'de' => 'Exposé ansehen',
                                        'ru' => 'Смотреть брошюру'
                                    ];
                                    echo $pdf_text[$current_language] ?? $pdf_text['en'];
                                    ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title h4"><?php echo $property["title_{$current_language}"]; ?></h5>
                            <p class="card-text fs-5">
                                <strong><?php echo $translations['property']['price']; ?>:</strong> 
                                €<?php echo number_format($property['price']); ?>
                            </p>
                            <p class="card-text fs-5">
                                <strong><?php echo $translations['property']['area']; ?>:</strong> 
                                <?php echo number_format($property['area']); ?> m²
                            </p>
                            <a href="/property.php?id=<?php echo $property['id']; ?>" class="btn btn-outline-primary btn-lg">
                                <?php echo $translations['property']['details']; ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Latest Properties -->
<section class="latest-properties py-5 bg-light">
    <div class="container-fluid px-4">
        <h2 class="title display-5 mb-0"><?php echo $translations['home']['latest_properties']; ?></h2>
        <div class="heading-divider"></div>
        <div class="row g-4">
            <?php foreach ($latest_properties as $property): ?>
                <div class="col-xl-4 col-lg-6">
                    <div class="card property-card h-100">
                        <div class="position-relative overflow-hidden">
                            <img src="<?php echo $property['image_path'] ? 'uploads/properties/' . $property['image_path'] : 'images/no-image.jpg'; ?>" 
                                 class="card-img-top" alt="<?php echo $property["title_{$current_language}"]; ?>"
                                 style="height: 300px; object-fit: cover;">
                            <?php if (isset($property['pdf_flyer']) && !empty(trim($property['pdf_flyer']))): ?>
                                <a href="uploads/flyers/<?php echo htmlspecialchars($property['pdf_flyer']); ?>" target="_blank" class="pdf-flyer-link">
                                    <i class="fas fa-file-pdf"></i> 
                                    <?php 
                                    $pdf_text = [
                                        'bg' => 'Виж експозе',
                                        'en' => 'View brochure',
                                        'de' => 'Exposé ansehen',
                                        'ru' => 'Смотреть брошюру'
                                    ];
                                    echo $pdf_text[$current_language] ?? $pdf_text['en'];
                                    ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title h4 mb-3"><?php echo $property["title_{$current_language}"]; ?></h5>
                            <div class="property-details mb-4">
                                <p class="card-text fs-5 mb-2">
                                <strong><?php echo $translations['property']['price']; ?>:</strong> 
                                    <span class="text-primary">€<?php echo number_format($property['price']); ?></span>
                            </p>
                                <p class="card-text fs-5 mb-0">
                                <strong><?php echo $translations['property']['area']; ?>:</strong> 
                                    <span><?php echo number_format($property['area']); ?> m²</span>
                            </p>
                            </div>
                            <a href="/property.php?id=<?php echo $property['id']; ?>" class="btn btn-outline-primary btn-lg w-100">
                                <?php echo $translations['property']['details']; ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Blog Posts Carousel -->
<section class="blog-posts py-5">
    <div class="container-fluid px-4">
        <h2 class="title display-5 mb-0">
            <?php
            $latest_posts_translations = [
                'bg' => 'Последни публикации',
                'en' => 'Latest Posts',
                'de' => 'Neueste Beiträge',
                'ru' => 'Последние публикации'
            ];
            echo $latest_posts_translations[$current_language] ?? $latest_posts_translations['en'];
            ?>
        </h2>
        <div class="heading-divider"></div>
        
        <?php
        try {
            // Опростена заявка без снимки
            $blog_posts_sql = "SELECT 
                id,
                title_" . $current_language . " as post_title,
                content_" . $current_language . " as post_content,
                created_at,
                slug
            FROM blog_posts 
            WHERE status = 'published' 
            AND title_" . $current_language . " IS NOT NULL 
            ORDER BY created_at DESC 
            LIMIT 6";
            
            $stmt = $db->prepare($blog_posts_sql);
            $stmt->execute();
            $blog_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($blog_posts)):
            ?>
            <div class="blog-posts-wrapper">
                <div class="row flex-nowrap overflow-auto pb-4 g-4" style="scroll-snap-type: x mandatory;">
                    <?php foreach($blog_posts as $post): ?>
                    <div class="col-md-4" style="scroll-snap-align: start;">
                        <div class="card h-100 blog-card">
                            <div class="card-body p-4">
                                <div class="post-date mb-3 text-muted">
                                    <i class="bi bi-calendar3 me-2"></i>
                                    <?php echo date('d.m.Y', strtotime($post['created_at'])); ?>
                                </div>
                                <h5 class="card-title h4 mb-3">
                                    <a href="/blog-post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                       class="text-decoration-none text-dark stretched-link">
                                        <?php echo htmlspecialchars($post['post_title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted mb-4">
                                    <?php 
                                    if (!empty($post['post_content'])) {
                                        $excerpt = strip_tags($post['post_content']);
                                        echo mb_substr($excerpt, 0, 150, 'UTF-8') . '...';
                                    }
                                    ?>
                                </p>
                                <div class="mt-auto">
                                    <a href="/blog-post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                                       class="btn btn-outline-primary">
                                        <?php 
                                        $read_more = [
                                            'bg' => 'Прочети повече',
                                            'en' => 'Read more',
                                            'de' => 'Mehr lesen',
                                            'ru' => 'Читать далее'
                                        ];
                                        echo $read_more[$current_language] ?? $read_more['en'];
                                        ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center">
                <?php
                $no_posts = [
                    'bg' => 'Все още няма публикации в блога.',
                    'en' => 'No blog posts yet.',
                    'de' => 'Noch keine Blogbeiträge.',
                    'ru' => 'Пока нет публикаций в блоге.'
                ];
                echo $no_posts[$current_language] ?? $no_posts['bg'];
                ?>
            </div>
            <?php 
            endif;
        } catch (PDOException $e) {
            error_log("Blog posts query error: " . $e->getMessage());
            ?>
            <div class="alert alert-info text-center">
                <?php
                $error_message = [
                    'bg' => 'Възникна грешка при зареждане на публикациите.',
                    'en' => 'An error occurred while loading posts.',
                    'de' => 'Beim Laden der Beiträge ist ein Fehler aufgetreten.',
                    'ru' => 'Произошла ошибка при загрузке публикаций.'
                ];
                echo $error_message[$current_language] ?? $error_message['bg'];
                ?>
            </div>
            <?php
        }
        ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<style>
:root {
    --heading-line-color: #007bff;
}

.section-title {
    font-weight: 200;
    position: relative;
}

.hero-section {
    background: linear-gradient(to right, #f8f9fa, #e9ecef);
    padding: 80px 0;
}

.property-card {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.property-card:hover {
    transform: translateY(-5px);
}

.card {
    border-radius: 12px;
    overflow: hidden;
}

.card-img-top {
    transition: transform 0.3s ease;
}

.property-card:hover .card-img-top {
    transform: scale(1.05);
}

.pdf-flyer-link {
    position: absolute;
    bottom: 15px;
    right: 15px;
    background-color: rgba(255, 255, 255, 0.95);
    padding: 8px 15px;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 2;
}

.pdf-flyer-link:hover {
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    transform: translateY(-2px);
    color: #333;
    text-decoration: none;
}

.pdf-flyer-link i {
    color: #dc3545;
    font-size: 1.1rem;
}

.form-control, .form-select {
    border: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}

.btn-primary {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
}

.btn-outline-primary:hover {
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .hero-section {
        padding: 40px 0;
    }
    
    .col-md-6:first-child {
        margin-bottom: 2rem;
    }
}

/* Property Cards */
.property-card {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
}

.property-card:hover {
    transform: translateY(-5px);
}

.property-card .card-img-top {
    transition: transform 0.3s ease;
}

.property-card:hover .card-img-top {
    transform: scale(1.05);
}

.property-details {
    padding: 1rem 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

/* Blog Cards */
.blog-posts-wrapper {
    margin: 0 -0.5rem;
}

.blog-posts-wrapper .row {
    padding-bottom: 1rem;
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
}

.blog-posts-wrapper .row::-webkit-scrollbar {
    height: 6px;
}

.blog-posts-wrapper .row::-webkit-scrollbar-track {
    background: transparent;
}

.blog-posts-wrapper .row::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

.blog-card {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    background: linear-gradient(to bottom right, #ffffff, #f8f9fa);
}

.blog-card:hover {
    transform: translateY(-5px);
}

.blog-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.blog-card .card-title {
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 1rem;
}

.blog-card .card-title a:hover {
    color: #007bff !important;
}

.blog-card .card-text {
    color: #6c757d;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.blog-card .post-date {
    font-size: 0.875rem;
    color: #6c757d;
}

.btn-outline-primary {
    border-width: 2px;
    font-weight: 500;
    padding: 0.5rem 1.5rem;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
}

@media (max-width: 768px) {
    .blog-card {
        margin-bottom: 1rem;
    }
    
    .blog-posts-wrapper .row {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 1rem;
        margin-bottom: -1rem;
    }
    
    .blog-posts-wrapper .col-md-4 {
        min-width: 300px;
    }
}
</style>