<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/migrations/Migration.php';

// Изпълнение на миграциите
echo "Running migrations...\n";
$migration = new Migration();

if (isset($argv[1]) && $argv[1] === 'fresh') {
    $migration->rollback();
}

$migration->migrate();

// Изпълнение на сийдърите
if (!isset($argv[1]) || $argv[1] !== 'fresh' || (isset($argv[2]) && $argv[2] === '--seed')) {
    echo "\nRunning seeders...\n";
    require_once __DIR__ . '/seed.php';
} 