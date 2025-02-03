<?php
session_start();

// Проверка дали инсталацията е завършена
if (!file_exists('config/installed.php')) {
    header('Location: install.php');
    exit;
}

require_once 'config/database.php';
require_once 'src/Database.php';

use App\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Масив със SQL файлове за сийдване
    $seedFiles = [
        'database/insert_default_blog_posts.sql',
        'database/update_descriptions.sql',
        'database/update_property_types.sql'
    ];
    
    // Изпълнение на SQL файловете
    foreach ($seedFiles as $file) {
        if (file_exists($file)) {
            echo "<p>Изпълнение на $file...</p>";
            $sql = file_get_contents($file);
            $db->exec($sql);
            echo "<p class='text-success'>✓ Успешно!</p>";
        }
    }
    
    // Създаване на необходимите директории
    $directories = [
        'uploads/properties',
        'uploads/properties/thumbnails',
        'uploads/blog',
        'uploads/flyers'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            echo "<p>Създадена директория: $dir</p>";
        }
    }
    
    // Изтегляне на примерни изображения
    if (file_exists('scripts/download_images.php')) {
        include 'scripts/download_images.php';
    }
    
    if (file_exists('scripts/download_service_images.php')) {
        include 'scripts/download_service_images.php';
    }
    
    // Създаване на админ акаунт
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        INSERT INTO users (username, password, role, email) 
        VALUES (?, ?, 'admin', 'admin@example.com')
        ON DUPLICATE KEY UPDATE password = ?
    ");
    $stmt->execute(['admin', $adminPassword, $adminPassword]);
    
    echo "<p>✓ Админ акаунт създаден успешно (потребител: admin, парола: admin123)</p>";
    
    // Създаване на .htaccess файл
    $htaccess = <<<EOT
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Защита на конфигурационните файлове
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch ".(php|sql)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

<FilesMatch "^(index|blog|property|about|contact)\.php$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# PHP настройки
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value max_input_time 300
EOT;
    
    file_put_contents('.htaccess', $htaccess);
    echo "<p>✓ .htaccess файл създаден успешно</p>";
    
    // Създаване на robots.txt
    $robots = <<<EOT
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /config/
Disallow: /includes/
Disallow: /src/
Disallow: /vendor/
Sitemap: https://yourdomain.com/sitemap.xml
EOT;
    
    file_put_contents('robots.txt', $robots);
    echo "<p>✓ robots.txt файл създаден успешно</p>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Грешка: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инсталиране на данни - Industrial Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Инсталиране на данни</h2>
                        
                        <div class="alert alert-success">
                            <h4 class="alert-heading">Инсталацията е завършена успешно!</h4>
                            <p>Всички необходими файлове и данни са инсталирани.</p>
                            <hr>
                            <p class="mb-0">
                                <a href="/" class="btn btn-success">Към началната страница</a>
                                <a href="/admin" class="btn btn-primary">Към админ панела</a>
                            </p>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h5>Важно!</h5>
                            <p>За по-голяма сигурност, моля:</p>
                            <ol class="mb-0">
                                <li>Изтрийте файловете install.php и seed.php</li>
                                <li>Променете паролата на админ акаунта</li>
                                <li>Настройте правилните пътища в robots.txt</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 