<?php

/**
 * Дефиниране на маршрутите в приложението
 */

use App\Controllers\HomeController;
use App\Controllers\PropertyController;
use App\Controllers\ContactController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\TranslationController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\AuthController;

// Публични маршрути
$router->get('/', [PropertyController::class, 'index'], 'home');
$router->get('/properties', [PropertyController::class, 'index'], 'properties.index');
$router->get('/property/{id}', [PropertyController::class, 'show'], 'properties.show');
$router->get('/contact', [ContactController::class, 'index'], 'contact');
$router->post('/contact', [ContactController::class, 'send'], 'contact.send');

// Маршрути за админ панела
$router->group('/admin', function($router) {
    // Защита с AuthMiddleware
    $router->middleware(\App\Middleware\AuthMiddleware::class);
    
    // Табло за управление
    $router->get('/', [DashboardController::class, 'index'], 'admin.dashboard');
    
    // Управление на имоти
    $router->get('/properties', [AdminPropertyController::class, 'index'], 'admin.properties');
    $router->get('/properties/create', [AdminPropertyController::class, 'create'], 'admin.properties.create');
    $router->post('/properties', [AdminPropertyController::class, 'store'], 'admin.properties.store');
    $router->get('/properties/{id}/edit', [AdminPropertyController::class, 'edit'], 'admin.properties.edit');
    $router->post('/properties/{id}', [AdminPropertyController::class, 'update'], 'admin.properties.update');
    $router->post('/properties/{id}/delete', [AdminPropertyController::class, 'delete'], 'admin.properties.delete');
    
    // Управление на потребители
    $router->get('/users', [UserController::class, 'index'], 'admin.users');
    $router->get('/users/create', [UserController::class, 'create'], 'admin.users.create');
    $router->post('/users', [UserController::class, 'store'], 'admin.users.store');
    $router->get('/users/{id}/edit', [UserController::class, 'edit'], 'admin.users.edit');
    $router->post('/users/{id}', [UserController::class, 'update'], 'admin.users.update');
    $router->post('/users/{id}/delete', [UserController::class, 'delete'], 'admin.users.delete');
    
    // Управление на преводи
    $router->get('/translations', [TranslationController::class, 'index'], 'admin.translations');
    $router->post('/translations', [TranslationController::class, 'update'], 'admin.translations.update');
    
    // Настройки
    $router->get('/settings', [SettingsController::class, 'index'], 'admin.settings');
    $router->post('/settings', [SettingsController::class, 'update'], 'admin.settings.update');
});

// Маршрути за автентикация
$router->get('/admin/login', [AuthController::class, 'loginForm'], 'auth.login');
$router->post('/admin/login', [AuthController::class, 'login'], 'auth.login.post');
$router->post('/admin/logout', [AuthController::class, 'logout'], 'auth.logout'); 