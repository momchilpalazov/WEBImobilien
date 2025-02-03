<?php
use App\Database;
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панел - Industrial Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-xxl">
            <a class="navbar-brand" href="index.php">
                <img src="../images/logo.svg" alt="Industrial Properties" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-speedometer2 me-2"></i>Табло
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="properties.php">
                            <i class="bi bi-building me-2"></i>Имоти
                        </a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link" href="inquiries.php">
                            <i class="bi bi-envelope me-2"></i>Запитвания
                            <?php
                            // Брой нови запитвания
                            $db = Database::getInstance()->getConnection();
                            $stmt = $db->query("SELECT COUNT(*) FROM inquiries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
                            $new_inquiries = $stmt->fetchColumn();
                            if ($new_inquiries > 0):
                            ?>
                            <span class="badge bg-danger"><?php echo $new_inquiries; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                  
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-2"></i>Към сайта
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Изход
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid flex-grow-1 py-4">
        <div class="container-xxl"><?php // Тук започва основното съдържание ?> 