<?php
// Проверяваме дали файлът е зареден директно
if (!defined('LANGUAGE_LOADED')) {
    define('LANGUAGE_LOADED', true);

    // Проверяваме дали сесията е стартирана
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/ip_location.php';

    function getCurrentLanguage() {
        $supported_languages = ['bg', 'de', 'ru', 'en'];
        $default_language = 'bg';
        
        // Дебъг информация
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("GET data: " . print_r($_GET, true));
        
        // 1. Проверяваме дали има избран език в URL
        if (isset($_GET['lang']) && in_array($_GET['lang'], $supported_languages)) {
            $_SESSION['language'] = $_GET['lang'];
            error_log("Language set from URL: " . $_GET['lang']);
            return $_GET['lang'];
        }
        
        // 2. Проверяваме дали има запазен език в сесията
        if (isset($_SESSION['language']) && in_array($_SESSION['language'], $supported_languages)) {
            error_log("Language from session: " . $_SESSION['language']);
            return $_SESSION['language'];
        }
        
        // 3. Определяме езика по IP адреса
        $country_code = getCountryCodeByIP();
        $language = getLanguageByCountryCode($country_code);
        
        if (!in_array($language, $supported_languages)) {
            $language = $default_language;
        }
        
        // Запазваме езика в сесията
        $_SESSION['language'] = $language;
        error_log("Language set from IP: " . $language);
        
        return $language;
    }

    // Дебъг информация при зареждане на файла
    error_log("language.php loaded. Session status: " . session_status());
}
?> 