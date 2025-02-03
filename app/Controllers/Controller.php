<?php

namespace App\Controllers;

use App\Services\TranslationService;

abstract class Controller
{
    protected TranslationService $translationService;
    
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }
    
    protected function view(string $view, array $data = []): string
    {
        // Добавяне на преводите към данните
        $data['translations'] = $this->translationService->getTranslations();
        $data['currentLanguage'] = $this->translationService->getCurrentLanguage();
        
        // Извличане на променливите в локален scope
        extract($data);
        
        // Стартиране на output buffering
        ob_start();
        
        // Включване на изгледа
        $viewPath = __DIR__ . '/../../views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        
        require $viewPath;
        
        // Връщане на съдържанието и изчистване на буфера
        return ob_get_clean();
    }
    
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
    
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
    
    protected function json(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
} 