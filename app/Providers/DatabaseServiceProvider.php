<?php

namespace App\Providers;

use App\Core\Container;
use PDO;
use App\Interfaces\TransactionRepositoryInterface;
use App\Repositories\TransactionRepository;

class DatabaseServiceProvider
{
    public function register(): void
    {
        Container::singleton(PDO::class, function() {
            $config = require __DIR__ . '/../../config/database.php';
            
            $dsn = sprintf(
                '%s:host=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['database'],
                $config['charset']
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            return new PDO($dsn, $config['username'], $config['password'], $options);
        });

        Container::singleton(TransactionRepositoryInterface::class, function () {
            return new TransactionRepository(Container::resolve(\PDO::class));
        });
    }
} 