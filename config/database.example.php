<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// OAuth2 настройки
define('OAUTH2_CLIENT_ID', 'your-client-id');
define('OAUTH2_CLIENT_SECRET', 'your-client-secret');
define('OAUTH2_REDIRECT_URI', 'http://your-domain.com/oauth_callback.php');
define('OAUTH2_TENANT_ID', 'your-tenant-id');
define('OAUTH2_SCOPES', 'https://outlook.office.com/SMTP.Send');

return [
    'host' => DB_HOST,
    'dbname' => DB_NAME,
    'username' => DB_USER,
    'password' => DB_PASS,
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
]; 