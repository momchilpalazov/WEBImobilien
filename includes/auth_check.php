<?php
function checkAdminAuth() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit();
    }
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
} 