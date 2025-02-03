<?php

namespace App\Providers;

use App\Core\Container;
use App\Interfaces\PropertyRepositoryInterface;
use App\Repositories\PropertyRepository;
use PDO;

class PropertyServiceProvider
{
    public function register(): void
    {
        Container::singleton(PropertyRepositoryInterface::class, function() {
            $pdo = Container::resolve(PDO::class);
            return new PropertyRepository($pdo);
        });
    }
} 