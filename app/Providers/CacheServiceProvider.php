<?php

namespace App\Providers;

use App\Core\Container;
use App\Interfaces\CacheInterface;
use App\Services\FileCache;

class CacheServiceProvider
{
    public function register(): void
    {
        Container::singleton(CacheInterface::class, function() {
            return new FileCache();
        });
    }
} 
