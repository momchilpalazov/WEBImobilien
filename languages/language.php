<?php
// Проверка дали сесията е стартирана
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Дефиниране на позволените езици
$allowed_languages = ['bg', 'en', 'de', 'ru'];
$default_language = 'bg';

// Функция за определяне на езика от IP адреса
function detect_language_from_ip() {
    return 'bg'; // По подразбиране връщаме български
}

// Определяне на езика
if (!isset($_SESSION['lang'])) {
    if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed_languages)) {
        $_SESSION['lang'] = $_GET['lang'];
    } else {
        $_SESSION['lang'] = $default_language;
    }
}

$current_lang = $_SESSION['lang'];

// Зареждане на езиковия файл
$language_file = __DIR__ . "/{$current_lang}.php";
if (file_exists($language_file)) {
    require_once $language_file;
} else {
    require_once __DIR__ . "/{$default_language}.php";
}

// Дефиниране на глобална променлива за езика
$GLOBALS['current_lang'] = $current_lang; 