<?php
session_start();

function checkAuth() {
    if (!isset($_SESSION['admin_user'])) {
        header('Location: ../index.php');
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['admin_user']) && $_SESSION['admin_user']['role'] === 'admin';
}

function getCurrentUser() {
    return $_SESSION['admin_user'] ?? null;
}

function checkPermission($permission) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    switch ($permission) {
        case 'manage_users':
            return $user['role'] === 'admin';
        case 'manage_properties':
            return in_array($user['role'], ['admin', 'agent']);
        default:
            return false;
    }
} 