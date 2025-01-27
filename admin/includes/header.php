<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once "../config/database.php";
require_once "../includes/functions.php";
use App\Database;

$db = Database::getInstance()->getConnection();
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панел - <?php echo $page_title ?? 'Начало'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>

<header class="main-header">
    <div class="header-left">
        <button class="menu-toggle">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title"><?php echo $page_title ?? 'Админ панел'; ?></h1>
    </div>
    <div class="header-right">
        <a href="../" class="btn btn-outline-light btn-sm" target="_blank">
            <i class="bi bi-box-arrow-up-right me-2"></i>
            Към сайта
        </a>
        <div class="dropdown">
            <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-2"></i>
                <?php echo $_SESSION['admin_name']; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="profile.php">
                        <i class="bi bi-person me-2"></i>
                        Профил
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Изход
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header> 