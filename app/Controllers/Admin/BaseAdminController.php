<?php

namespace App\Controllers\Admin;

use App\Services\TranslationService;
use App\Interfaces\AuthInterface;

abstract class BaseAdminController
{
    protected array $translations;
    protected AuthInterface $auth;

    public function __construct(
        TranslationService $translationService,
        AuthInterface $auth
    ) {
        $this->auth = $auth;
        $this->translations = $translationService->getTranslations(CURRENT_LANGUAGE);

        // Check if user is authenticated
        if (!$this->auth->check()) {
            $_SESSION['error'] = $this->translations['auth']['unauthorized'];
            header('Location: /admin/login');
            exit;
        }
    }

    protected function render(string $view, array $data = []): void
    {
        // Add common data
        $data['translations'] = $this->translations;
        $data['currentUser'] = $this->auth->user();
        $data['currentLanguage'] = CURRENT_LANGUAGE;
        $data['menu'] = $this->getAdminMenu();

        // Extract data to make it available in the view
        extract($data);

        // Include the view file
        require_once __DIR__ . "/../../../views/{$view}.php";
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
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

    protected function setError(string $message): void
    {
        $_SESSION['error'] = $message;
    }

    protected function setSuccess(string $message): void
    {
        $_SESSION['success'] = $message;
    }

    protected function getCurrentUserId(): int
    {
        $user = $this->auth->user();
        if (!$user || !isset($user['id'])) {
            throw new \RuntimeException('User not authenticated');
        }
        return (int)$user['id'];
    }

    private function getAdminMenu(): array
    {
        return [
            'dashboard' => [
                'title' => $this->translations['admin']['menu']['dashboard'],
                'icon' => 'bi bi-speedometer2',
                'url' => '/admin'
            ],
            'properties' => [
                'title' => $this->translations['admin']['menu']['properties'],
                'icon' => 'bi bi-building',
                'url' => '/admin/properties'
            ],
            'users' => [
                'title' => $this->translations['admin']['menu']['users'],
                'icon' => 'bi bi-people',
                'url' => '/admin/users'
            ],
            'translations' => [
                'title' => $this->translations['admin']['menu']['translations'],
                'icon' => 'bi bi-translate',
                'url' => '/admin/translations'
            ],
            'settings' => [
                'title' => $this->translations['admin']['menu']['settings'],
                'icon' => 'bi bi-gear',
                'url' => '/admin/settings'
            ]
        ];
    }
} 