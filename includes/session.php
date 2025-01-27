<?php
// Конфигурация на сесиите
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

session_start();

// Регенериране на session ID на всеки 30 минути
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

// Проверка за валидност на IP адреса
if (!isset($_SESSION['IP'])) {
    $_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];
} else if ($_SESSION['IP'] !== $_SERVER['REMOTE_ADDR']) {
    session_destroy();
    header('Location: login.php');
    exit;
} 