<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/config.php';

checkAdminAuth();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: properties.php');
    exit();
}

$image_id = (int)$_GET['id'];

// Първо вземаме информация за снимката
$query = "SELECT property_id, image_path FROM property_images WHERE id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    $stmt->close();
    
    if ($image) {
        // Изтриваме физическия файл
        $file_path = '../' . $image['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Изтриваме записа от базата данни
        $delete_query = "DELETE FROM property_images WHERE id = ?";
        if ($delete_stmt = $conn->prepare($delete_query)) {
            $delete_stmt->bind_param("i", $image_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
        
        header('Location: property_edit.php?id=' . $image['property_id'] . '&success=1');
        exit();
    }
}

header('Location: properties.php');
exit(); 