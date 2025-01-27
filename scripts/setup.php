<?php
$directories = [
    '../uploads',
    '../uploads/properties',
    '../uploads/properties/thumbnails',
    '../cache',
    '../logs',
    '../logs/errors',
    '../logs/access'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "Created directory: $dir\n";
    }
}

// Създаване на .htaccess файлове за защита на директориите
$htaccess_content = "Options -Indexes\nDeny from all";
$protect_dirs = ['../logs', '../cache'];

foreach ($protect_dirs as $dir) {
    file_put_contents("$dir/.htaccess", $htaccess_content);
    echo "Created .htaccess in: $dir\n";
}

// Проверка на правата
foreach ($directories as $dir) {
    if (is_writable($dir)) {
        echo "Directory $dir is writable: OK\n";
    } else {
        echo "Warning: Directory $dir is not writable!\n";
    }
} 