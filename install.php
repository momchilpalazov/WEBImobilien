<?php
session_start();

// Проверка дали инсталацията вече е направена
if (file_exists('config/installed.php')) {
    die('Инсталацията вече е завършена. Изтрийте файла config/installed.php за да инсталирате отново.');
}

// Функция за проверка на изискванията
function checkRequirements() {
    $requirements = [
        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'GD Extension' => extension_loaded('gd'),
        'FileInfo Extension' => extension_loaded('fileinfo'),
        'uploads/ Directory Writable' => is_writable('uploads') || @mkdir('uploads', 0777, true),
        'config/ Directory Writable' => is_writable('config') || @mkdir('config', 0777, true)
    ];
    
    return $requirements;
}

// Функция за създаване на базата данни и таблиците
function createDatabase($host, $username, $password, $database) {
    try {
        // Създаване на връзка без избрана база данни
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Създаване на базата данни
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Избиране на базата данни
        $pdo->exec("USE `$database`");
        
        // Изпълнение на SQL файловете за създаване на таблиците
        $sqlFiles = [
            'database/create_properties_table.sql',
            'database/create_blog_table.sql',
            'database/create_inquiries_table.sql',
            'database/create_seo_table.sql'
        ];
        
        foreach ($sqlFiles as $file) {
            if (file_exists($file)) {
                $sql = file_get_contents($file);
                $pdo->exec($sql);
            }
        }
        
        return true;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

// Функция за създаване на конфигурационния файл
function createConfig($host, $username, $password, $database) {
    $config = <<<EOT
<?php
define('DB_HOST', '$host');
define('DB_NAME', '$database');
define('DB_USER', '$username');
define('DB_PASS', '$password');

return [
    'host' => '$host',
    'dbname' => '$database',
    'username' => '$username',
    'password' => '$password',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
EOT;
    
    return file_put_contents('config/database.php', $config);
}

// Обработка на формата
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['db_host'] ?? '';
    $username = $_POST['db_user'] ?? '';
    $password = $_POST['db_pass'] ?? '';
    $database = $_POST['db_name'] ?? '';
    
    // Създаване на базата данни
    $result = createDatabase($host, $username, $password, $database);
    
    if ($result === true) {
        // Създаване на конфигурационния файл
        if (createConfig($host, $username, $password, $database)) {
            // Създаване на маркер за завършена инсталация
            file_put_contents('config/installed.php', '<?php return true;');
            
            // Пренасочване към seed.php
            header('Location: seed.php');
            exit;
        } else {
            $error = 'Грешка при създаване на конфигурационния файл';
        }
    } else {
        $error = $result;
    }
}

$requirements = checkRequirements();
$canInstall = !in_array(false, $requirements);
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инсталация - Industrial Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Инсталация на системата</h2>
                        
                        <!-- Проверка на изискванията -->
                        <h4 class="mb-3">Системни изисквания</h4>
                        <ul class="list-group mb-4">
                            <?php foreach ($requirements as $requirement => $satisfied): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $requirement; ?>
                                <?php if ($satisfied): ?>
                                    <span class="badge bg-success">✓</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">✗</span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($canInstall): ?>
                        <!-- Форма за инсталация -->
                        <form method="post" action="">
                            <div class="mb-3">
                                <label class="form-label">MySQL Хост</label>
                                <input type="text" class="form-control" name="db_host" value="localhost" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">MySQL Потребител</label>
                                <input type="text" class="form-control" name="db_user" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">MySQL Парола</label>
                                <input type="password" class="form-control" name="db_pass">
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Име на база данни</label>
                                <input type="text" class="form-control" name="db_name" value="industrial_properties" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                Инсталирай
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            Моля, изпълнете всички системни изисквания преди да продължите с инсталацията.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 