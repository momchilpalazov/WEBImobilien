<?php

namespace App\Providers;

use App\Core\Container;
use App\Interfaces\PropertyRepositoryInterface;
use App\Services\PropertyStatisticsService;

class PropertyStatisticsServiceProvider
{
    public function register(): void
    {
        Container::singleton(PropertyStatisticsService::class, function() {
            $propertyRepository = Container::resolve(PropertyRepositoryInterface::class);
            return new PropertyStatisticsService($propertyRepository);
        });
    }
} 