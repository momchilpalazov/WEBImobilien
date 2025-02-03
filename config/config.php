<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '1234');
define('DB_NAME', 'industrial_properties');

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Language settings
$available_languages = ['bg', 'de', 'ru'];
$default_language = 'bg';

// Get current language
$current_language = isset($_GET['lang']) && 
                   in_array($_GET['lang'], $available_languages) ? 
                   $_GET['lang'] : $default_language;

// Load language file
require_once "languages/{$current_language}.php"; 