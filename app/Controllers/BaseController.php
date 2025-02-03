<?php

namespace App\Controllers;

use App\Services\TranslationService;

abstract class BaseController
{
    protected array $translations;
    protected TranslationService $translationService;
    protected string $currentLanguage;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
        $this->currentLanguage = $translationService->getCurrentLanguage();
        $this->translations = $translationService->getTranslations($this->currentLanguage);
    }

    protected function view(string $name, array $data = []): void
    {
        error_log("Debug in BaseController::view - Start");
        
        // Подготвяме всички променливи, които ще са достъпни в изгледите
        $viewData = array_merge([
            'translations' => $this->translations,
            'currentLanguage' => $this->currentLanguage
        ], $data);
        
        error_log("ViewData prepared: " . print_r($viewData, true));
        
        // Стартираме буфера
        ob_start();
        
        // Правим променливите достъпни в обхвата
        extract($viewData);
        
        // Зареждаме view файла
        require __DIR__ . "/../../views/{$name}.php";
        
        // Запазваме съдържанието
        $content = ob_get_clean();
        
        // Добавяме content към данните
        $viewData['content'] = $content;
        
        error_log("Before layout - viewData: " . print_r($viewData, true));
        
        // Стартираме нов буфер за layout
        ob_start();
        
        // Отново правим всички променливи достъпни
        extract($viewData);
        
        // Зареждаме layout файла
        require __DIR__ . "/../../views/layouts/main.php";
        
        // Извеждаме финалното съдържание
        echo ob_get_clean();
        
        error_log("Debug in BaseController::view - End");
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
} 