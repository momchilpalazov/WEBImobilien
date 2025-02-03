<?php

namespace App\Controllers;

use App\Services\TranslationService;

abstract class BaseAdminController extends BaseController
{
    protected TranslationService $translationService;
    protected array $errors = [];

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        
        $this->translationService = $translationService;
        
        // Add admin layout
        $this->view->addFolder('admin', dirname(__DIR__, 1) . '/views/admin');
        
        // Check authentication
        if (!$this->isAuthenticated()) {
            $this->redirect('/admin/login');
        }
        
        // Add shared admin data
        $this->view->addData([
            'user' => $_SESSION['user'] ?? null,
            'menu' => $this->getAdminMenu()
        ]);
    }

    protected function render(string $view, array $data = []): void
    {
        $viewPath = __DIR__ . "/../../views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        // Extract data to make it available in the view
        extract($data);

        // Start output buffering
        ob_start();

        // Include the view file
        require $viewPath;

        // Get the contents and clean the buffer
        $content = ob_get_clean();

        // Include the layout with the content
        require __DIR__ . '/../../views/layouts/admin.php';
    }

    protected function redirect(string $path): void
    {
        header("Location: {$path}");
        exit;
    }

    protected function setError(string $message): void
    {
        $_SESSION['error'] = $message;
    }

    protected function setSuccess(string $message): void
    {
        $_SESSION['success'] = $message;
    }

    protected function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    protected function getErrors(): array
    {
        return $this->errors;
    }

    protected function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    protected function clearErrors(): void
    {
        $this->errors = [];
    }

    protected function getOldInput(string $key, $default = null)
    {
        return $_SESSION['old'][$key] ?? $default;
    }

    protected function setOldInput(array $data): void
    {
        $_SESSION['old'] = $data;
    }

    protected function clearOldInput(): void
    {
        unset($_SESSION['old']);
    }

    protected function translate(string $key, array $params = []): string
    {
        return $this->translationService->translate($key, $params);
    }

    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }
    
    protected function getAdminMenu(): array
    {
        return [
            [
                'title' => 'Табло',
                'icon' => 'fas fa-tachometer-alt',
                'url' => '/admin'
            ],
            [
                'title' => 'Имоти',
                'icon' => 'fas fa-building',
                'url' => '/admin/properties'
            ],
            [
                'title' => 'Клиенти',
                'icon' => 'fas fa-users',
                'url' => '/admin/clients'
            ],
            [
                'title' => 'Договори',
                'icon' => 'fas fa-file-contract',
                'url' => '/admin/contracts',
                'submenu' => [
                    [
                        'title' => 'Списък',
                        'url' => '/admin/contracts'
                    ],
                    [
                        'title' => 'Шаблони',
                        'url' => '/admin/contracts/templates'
                    ]
                ]
            ],
            [
                'title' => 'Справки',
                'icon' => 'fas fa-chart-bar',
                'url' => '/admin/reports'
            ],
            [
                'title' => 'Настройки',
                'icon' => 'fas fa-cog',
                'url' => '/admin/settings'
            ]
        ];
    }
} 