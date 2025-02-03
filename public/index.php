<?php
<<<<<<< HEAD

// Start session
session_start();

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration
require_once __DIR__ . '/../config/database.php';

use App\Core\Container;
use App\Services\Router;
use App\Services\TranslationService;
use App\Services\Validator;
use App\Services\Auth;
use App\Repositories\PropertyRepository;
use App\Controllers\PropertyController;
use App\Middleware\LanguageMiddleware;
use App\Middleware\AuthMiddleware;
use App\Providers\DatabaseServiceProvider;

// Initialize language
$availableLanguages = ['bg', 'en', 'de', 'ru'];
$defaultLanguage = 'bg';

if (isset($_GET['lang']) && in_array($_GET['lang'], $availableLanguages)) {
    $_SESSION['language'] = $_GET['lang'];
} elseif (!isset($_SESSION['language'])) {
    $_SESSION['language'] = $defaultLanguage;
}

$currentLanguage = $_SESSION['language'];

// Initialize container
$container = Container::getInstance();

// Register database service
try {
    $databaseProvider = new DatabaseServiceProvider();
    $databaseProvider->register();

    // Get PDO instance
    $db = Container::resolve(PDO::class);
} catch (\Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Could not connect to the database");
}

// Initialize TranslationService first
Container::singleton(TranslationService::class, function() {
    return new TranslationService();
});

// Register services
Container::singleton(Auth::class, function() use ($db) {
    return new Auth($db);
});

Container::singleton(Validator::class, function() {
    $translationService = Container::resolve(TranslationService::class);
    return new Validator($translationService);
});

Container::singleton(PropertyRepository::class, function() use ($db) {
    return new PropertyRepository($db);
});

Container::singleton(PropertyController::class, function() {
    return new PropertyController(
        Container::resolve(TranslationService::class),
        Container::resolve(PropertyRepository::class),
        Container::resolve(Validator::class)
    );
});

// Register ErrorController
Container::singleton('error_controller', function() {
    return new \App\Controllers\ErrorController(
        Container::resolve(TranslationService::class)
    );
});

// Initialize router
$router = new Router($container);

// Add global middleware
$router->middleware(LanguageMiddleware::class);

// Регистриране на маршрутите
require_once __DIR__ . '/../routes/web.php';

// Създаване на функция за превод
if (!function_exists('__')) {
    function __(string $key, array $params = []): string {
        $translator = Container::resolve(TranslationService::class);
        return $translator->translate($key, $params);
    }
}

// Създаване на функция за вземане на всички преводи
if (!function_exists('translations')) {
    function translations(): array {
        $translator = Container::resolve(TranslationService::class);
        return $translator->getTranslations();
    }
}

// Създаване на функция за вземане на текущия език
if (!function_exists('currentLanguage')) {
    function currentLanguage(): string {
        $translator = Container::resolve(TranslationService::class);
        return $translator->getCurrentLanguage();
    }
}

// Dispatch request
try {
    $router->dispatch();
} catch (PDOException $e) {
    // Log database errors
    error_log("Database Error: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo "An error occurred. Please try again later.";
} catch (Exception $e) {
    // Log other errors
    error_log("Error: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo "An error occurred. Please try again later.";
} 
=======
require_once '../vendor/autoload.php';

use App\Database\Database;
use App\Cache\Cache;
use App\Logger\Logger;
use App\Config\Config;

// Зареждане на конфигурацията
Config::load(__DIR__ . '/../config/app.php');
Config::load(__DIR__ . '/../config/database.php');
Config::load(__DIR__ . '/../config/maps.php');

// Инициализация на логъра
$logger = new Logger();

try {
    // Вземане на последните имоти с кеширане
    $cache = new Cache();
    $cacheKey = 'latest_properties';
    
    $latest_properties = $cache->get($cacheKey);
    
    if ($latest_properties === false) {
        $db = Database::getInstance();
        $latest_properties = $db->query("
            SELECT p.*, 
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image
            FROM properties p 
            WHERE active = 1 
            ORDER BY created_at DESC 
            LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $cache->set($cacheKey, $latest_properties, 3600); // кеширане за 1 час
    }
    
} catch (\Exception $e) {
    $logger->error('Error loading homepage', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Показване на грешка на потребителя
    die('Възникна грешка при зареждане на страницата');
}

// Зареждане на езиковите файлове
$default_lang = 'bg';
$allowed_languages = ['bg', 'de', 'ru'];
$current_lang = isset($_GET['lang']) && in_array($_GET['lang'], $allowed_languages) ? $_GET['lang'] : $default_lang;

require_once "../languages/{$current_lang}.php";

$db = Database::getInstance()->getConnection();

// Вземане на статистики
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM properties WHERE active = 1) as total_properties,
    (SELECT COUNT(*) FROM clients) as total_clients,
    (SELECT COUNT(*) FROM deals WHERE status = 'completed') as total_deals";
$stats = $db->query($stats_sql)->fetch();
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['site_title']; ?></title>
    <meta name="description" content="<?php echo $lang['site_description']; ?>">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
                <div class="hero-content">
                    <h1><?php echo $lang['hero_title']; ?></h1>
                    <p class="hero-subtitle"><?php echo $lang['hero_subtitle']; ?></p>
                    <p class="hero-description"><?php echo $lang['hero_description']; ?></p>
                    <div class="hero-buttons">
                        <a href="search.php" class="btn btn-primary"><?php echo $lang['hero_cta']; ?></a>
                        <a href="contact.php" class="btn btn-outline"><?php echo $lang['contact_us']; ?></a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Key Features -->
        <section class="features-section">
            <div class="container">
                <h2 class="section-title"><?php echo $lang['key_features']; ?></h2>
                <div class="features-grid">
                    <div class="feature-item animate-on-scroll">
                        <div class="feature-icon">
                            <img src="assets/images/icons/location.svg" alt="Location">
                        </div>
                        <h3><?php echo $lang['feature_locations']; ?></h3>
                        <p><?php echo $lang['feature_locations_text']; ?></p>
                    </div>
                    <div class="feature-item animate-on-scroll">
                        <div class="feature-icon">
                            <img src="assets/images/icons/quality.svg" alt="Quality">
                        </div>
                        <h3><?php echo $lang['feature_quality']; ?></h3>
                        <p><?php echo $lang['feature_quality_text']; ?></p>
                    </div>
                    <div class="feature-item animate-on-scroll">
                        <div class="feature-icon">
                            <img src="assets/images/icons/support.svg" alt="Support">
                        </div>
                        <h3><?php echo $lang['feature_support']; ?></h3>
                        <p><?php echo $lang['feature_support_text']; ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Property Categories -->
        <section class="categories-section">
            <div class="container">
                <h2 class="section-title"><?php echo $lang['property_categories']; ?></h2>
                <div class="categories-grid">
                    <a href="search.php?type=industrial" class="category-card">
                        <div class="category-image">
                            <img src="assets/images/categories/industrial.jpg" alt="Industrial">
                        </div>
                        <h3><?php echo $lang['industrial_category']; ?></h3>
                    </a>
                    <a href="search.php?type=warehouse" class="category-card">
                        <div class="category-image">
                            <img src="assets/images/categories/warehouse.jpg" alt="Warehouse">
                        </div>
                        <h3><?php echo $lang['warehouse_category']; ?></h3>
                    </a>
                    <a href="search.php?type=logistics" class="category-card">
                        <div class="category-image">
                            <img src="assets/images/categories/logistics.jpg" alt="Logistics">
                        </div>
                        <h3><?php echo $lang['logistics_category']; ?></h3>
                    </a>
                    <a href="search.php?type=office" class="category-card">
                        <div class="category-image">
                            <img src="assets/images/categories/office.jpg" alt="Office">
                        </div>
                        <h3><?php echo $lang['office_category']; ?></h3>
                    </a>
            </div>
        </div>
    </section>

        <!-- Latest Properties -->
        <section class="latest-properties-section">
        <div class="container">
                <div class="section-header">
                    <h2 class="section-title"><?php echo $lang['latest_properties']; ?></h2>
                    <a href="search.php" class="btn btn-outline">
                        <?php echo $lang['view_all_properties']; ?>
                    </a>
                </div>
            <div class="properties-grid">
                    <?php foreach ($latest_properties as $property): ?>
                    <div class="property-card animate-on-scroll">
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
            </div>
        </section>

        <!-- Investment Section -->
        <section class="investment-section">
            <div class="container">
                <div class="investment-content">
                    <h2><?php echo $lang['invest_title']; ?></h2>
                    <p class="investment-subtitle"><?php echo $lang['invest_subtitle']; ?></p>
                    <ul class="investment-points">
                        <li><?php echo $lang['invest_point_1']; ?></li>
                        <li><?php echo $lang['invest_point_2']; ?></li>
                        <li><?php echo $lang['invest_point_3']; ?></li>
                        <li><?php echo $lang['invest_point_4']; ?></li>
                    </ul>
                    <a href="about.php" class="btn btn-primary"><?php echo $lang['invest_cta']; ?></a>
                </div>
                <div class="investment-image">
                    <img src="assets/images/investment-bg.jpg" alt="Invest in Bulgaria">
            </div>
        </div>
    </section>

        <!-- Statistics Section -->
        <section class="stats-section">
        <div class="container">
                <h2 class="section-title"><?php echo $lang['stats_title']; ?></h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $stats['total_properties']; ?></span>
                        <span class="stat-label"><?php echo $lang['stats_properties']; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $stats['total_clients']; ?></span>
                        <span class="stat-label"><?php echo $lang['stats_clients']; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">12</span>
                        <span class="stat-label"><?php echo $lang['stats_experience']; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $stats['total_deals']; ?></span>
                        <span class="stat-label"><?php echo $lang['stats_deals']; ?></span>
                    </div>
                </div>
        </div>
    </section>
</main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html> 
>>>>>>> 8b1f889630bb64639cf007f246e163bd8da80b38
