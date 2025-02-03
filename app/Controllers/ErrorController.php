<?php

namespace App\Controllers;

use App\Services\TranslationService;

class ErrorController extends BaseController
{
    public function __construct(TranslationService $translationService)
    {
        parent::__construct($translationService);
    }

    public function notFound(): void
    {
        header("HTTP/1.0 404 Not Found");
        $this->view('errors/404');
    }
} 