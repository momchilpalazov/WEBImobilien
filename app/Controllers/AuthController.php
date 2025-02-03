<?php

namespace App\Controllers;

use App\Services\TranslationService;
use App\Interfaces\AuthInterface;
use App\Services\Validator;

class AuthController extends BaseController
{
    private AuthInterface $auth;
    private Validator $validator;

    public function __construct(
        TranslationService $translationService,
        AuthInterface $auth,
        Validator $validator
    ) {
        parent::__construct($translationService);
        $this->auth = $auth;
        $this->validator = $validator;
    }

    public function loginForm(): void
    {
        if ($this->auth->check()) {
            header('Location: /admin');
            exit;
        }

        $this->view('auth/login');
    }

    public function login(): void
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        if (!$this->validator->validate($_POST, $rules)) {
            $_SESSION['errors'] = $this->validator->getErrors();
            $_SESSION['old'] = $_POST;
            header('Location: /admin/login');
            exit;
        }

        $data = $this->validator->getValidData();

        if ($this->auth->attempt($data['email'], $data['password'])) {
            header('Location: /admin');
            exit;
        }

        $_SESSION['errors'] = ['auth' => $this->translations['auth']['failed']];
        $_SESSION['old'] = $_POST;
        header('Location: /admin/login');
        exit;
    }

    public function logout(): void
    {
        $this->auth->logout();
        header('Location: /admin/login');
        exit;
    }
} 