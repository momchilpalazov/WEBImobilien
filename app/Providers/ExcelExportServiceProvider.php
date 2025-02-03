<?php

namespace App\Providers;

use App\Core\Container;
use App\Services\ExcelExportService;

class ExcelExportServiceProvider
{
    public function register(): void
    {
        Container::singleton(ExcelExportService::class, function() {
            return new ExcelExportService();
        });
    }
} 