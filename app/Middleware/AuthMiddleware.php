<?php

namespace App\Middleware;

use App\Services\Auth;
use App\Core\Container;

class AuthMiddleware
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = Container::resolve(Auth::class);
    }

    public function handle(): void
    {
        if (!$this->auth->check()) {
            header('Location: /admin/login');
            exit;
        }

        if (!$this->auth->hasRole('admin')) {
            header('Location: /');
            exit;
        }
    }
} 