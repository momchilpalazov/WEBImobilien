<?php
require_once 'config/database.php';
require_once 'src/Database.php';
use App\Database;

// Проверка дали сайтът е в режим на поддръжка
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
    $stmt->execute();
    $maintenance_mode = $stmt->fetchColumn();

    // Ако сайтът не е в режим на поддръжка, пренасочваме към началната страница
    if ($maintenance_mode === 'false') {
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    // Ако има грешка с базата данни, показваме страницата за поддръжка
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The site is under renovation - Industrial Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .maintenance-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 15px;
        }
        .maintenance-icon {
            font-size: 5rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">🛠️</div>
        <h1 class="mb-4">The site is under renovation.</h1>
        <p class="lead mb-4">
        We are currently performing scheduled site maintenance.
        Please try again in a few minutes.
        </p>
        <p class="text-muted">
            Thank you for your patience!
        </p>
    </div>
</body>
</html> 