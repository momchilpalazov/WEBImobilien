<?php

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