<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/seeders/UserSeeder.php';
require_once __DIR__ . '/seeders/PropertySeeder.php';
require_once __DIR__ . '/seeders/SettingSeeder.php';

try {
    echo "Започване на сийдване...\n";
    
    $seeders = [
        new UserSeeder(),
        new PropertySeeder(),
        new SettingSeeder()
    ];
    
    foreach ($seeders as $seeder) {
        echo "Изпълнение на " . get_class($seeder) . "...\n";
        $seeder->run();
    }
    
    echo "Сийдването завърши успешно!\n";
} catch (Exception $e) {
    echo "Грешка: " . $e->getMessage() . "\n";
} 