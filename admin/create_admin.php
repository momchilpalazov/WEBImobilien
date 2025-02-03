<?php
require_once '../config/config.php';

$username = 'admin';
$password = 'your_secure_password';
$email = 'admin@example.com';

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("sss", $username, $hashed_password, $email);
    
    if ($stmt->execute()) {
        echo "Администраторският акаунт е създаден успешно!";
    } else {
        echo "Грешка при създаване на акаунта: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close(); 