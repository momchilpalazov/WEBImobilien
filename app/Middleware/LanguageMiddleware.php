<?php

namespace App\Middleware;

use App\Interfaces\MiddlewareInterface;
use App\Interfaces\ContainerInterface;
use App\Services\TranslationService;

class LanguageMiddleware implements MiddlewareInterface
{
    private array $availableLanguages = ['bg', 'en', 'de', 'ru'];
    private string $defaultLanguage = 'bg';

    public function __invoke(ContainerInterface $container)
    {
        if (isset($_GET['language']) && in_array($_GET['language'], $this->availableLanguages)) {
            $_SESSION['language'] = $_GET['language'];
        } elseif (!isset($_SESSION['language'])) {
            $_SESSION['language'] = $this->defaultLanguage;
        }

        // Обновяване на TranslationService с текущия език
        $translationService = $container->get(TranslationService::class);
        $translationService->setLanguage($_SESSION['language']);
    }
} 