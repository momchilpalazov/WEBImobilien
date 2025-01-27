<?php

require_once __DIR__ . '/../config/database.php';
use App\Database;

function getLatestProperties($limit = 6) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT p.*, pi.image_path as main_image 
        FROM properties p 
        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1
        WHERE p.featured = 1 
        ORDER BY p.created_at DESC 
        LIMIT :limit
    ");
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

function getPropertyById($id) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT p.*, GROUP_CONCAT(pi.image_path) as images 
        FROM properties p 
        LEFT JOIN property_images pi ON p.id = pi.property_id 
        WHERE p.id = :id 
        GROUP BY p.id
    ");
    
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch();
}

function saveInquiry($data) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        INSERT INTO inquiries (property_id, service_id, name, email, phone, message) 
        VALUES (:property_id, :service_id, :name, :email, :phone, :message)
    ");
    
    return $stmt->execute([
        ':property_id' => $data['property_id'] ?? null,
        ':service_id' => $data['service_id'] ?? null,
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':phone' => $data['phone'] ?? null,
        ':message' => $data['message']
    ]);
}

function checkPermission($permission) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM users u
        JOIN role_permissions rp ON u.role = rp.role
        JOIN permissions p ON rp.permission_id = p.id
        WHERE u.id = ? AND p.name = ?
    ");
    
    $stmt->execute([$_SESSION['user_id'], $permission]);
    return $stmt->fetchColumn() > 0;
}