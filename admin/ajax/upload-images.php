<?php
session_start();

require_once '../includes/auth.php';
require_once "../../src/Database/Database.php";
require_once "../../config/database.php";

use App\Database\Database;

// Включване на error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Задаване на header-и
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

class ImageUploader {
    private $db;
    private $propertyId;
    private $uploadDir;
    private $thumbnailsDir;
    private $file;
    
    public function __construct($propertyId, $file) {
        $this->db = Database::getInstance()->getConnection();
        $this->propertyId = $propertyId;
        $this->file = $file;
        
        // Инициализиране на директориите
        $baseDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'properties';
        $this->uploadDir = $baseDir;
        $this->thumbnailsDir = $baseDir . DIRECTORY_SEPARATOR . 'thumbnails';
        
        $this->initDirectories();
    }
    
    private function initDirectories() {
        if (!file_exists($this->uploadDir)) {
            if (!@mkdir($this->uploadDir, 0777, true)) {
                error_log("Failed to create directory: " . $this->uploadDir);
                throw new Exception("Грешка при създаване на директория за качване");
            }
        }
        if (!file_exists($this->thumbnailsDir)) {
            if (!@mkdir($this->thumbnailsDir, 0777, true)) {
                error_log("Failed to create directory: " . $this->thumbnailsDir);
                throw new Exception("Грешка при създаване на директория за миниатюри");
            }
        }
        
        if (!is_writable($this->uploadDir)) {
            error_log("Directory not writable: " . $this->uploadDir);
            throw new Exception("Няма права за запис в директорията за качване");
        }
        if (!is_writable($this->thumbnailsDir)) {
            error_log("Directory not writable: " . $this->thumbnailsDir);
            throw new Exception("Няма права за запис в директорията за миниатюри");
        }
    }
    
    public function upload() {
        try {
            // Проверка дали имотът съществува и е валиден
            $stmt = $this->db->prepare("SELECT type, status FROM properties WHERE id = ?");
            $stmt->execute([$this->propertyId]);
            $property = $stmt->fetch();
            
            if (!$property) {
                throw new Exception("Имотът не съществува");
            }
            
            // Валидация на файла
            $this->validateFile();
            
            // Генериране на уникално име
            $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
            $filename = uniqid() . '_' . time() . '.' . $extension;
            
            // Качване на оригиналния файл
            $uploadPath = $this->uploadDir . DIRECTORY_SEPARATOR . $filename;
            if (!@move_uploaded_file($this->file['tmp_name'], $uploadPath)) {
                error_log("Failed to move uploaded file to: " . $uploadPath);
                throw new Exception("Грешка при качване на файла");
            }
            
            // Оптимизиране на оригиналното изображение
            $this->optimizeImage($uploadPath);
            
            // Създаване на миниатюра
            $this->createThumbnail($uploadPath, $this->thumbnailsDir . DIRECTORY_SEPARATOR . $filename);
            
            // Запис в базата данни
            $this->saveToDatabase($filename);
            
            return [
                'success' => true,
                'message' => 'Снимката е качена успешно',
                'filename' => $filename
            ];
        } catch (Exception $e) {
            error_log("Upload error in ImageUploader: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function validateFile() {
        if (!isset($this->file) || $this->file['error'] !== UPLOAD_ERR_OK) {
            $error = isset($this->file['error']) ? $this->file['error'] : 'unknown';
            error_log("File validation error: " . $error);
            throw new Exception("Невалиден файл");
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($this->file['type'], $allowedTypes)) {
            error_log("Invalid file type: " . $this->file['type']);
            throw new Exception("Невалиден тип файл. Разрешени са само: JPG, PNG и GIF");
        }
        
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($this->file['size'] > $maxSize) {
            error_log("File too large: " . $this->file['size']);
            throw new Exception("Файлът е твърде голям. Максимален размер: 5MB");
        }
    }
    
    private function createThumbnail($sourcePath, $targetPath) {
        list($width, $height) = getimagesize($sourcePath);
        $targetWidth = 300;  // Ширина на миниатюрата
        $targetHeight = 200; // Височина на миниатюрата
        
        $ratio = min($targetWidth / $width, $targetHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        $thumb = imagecreatetruecolor($targetWidth, $targetHeight);
        
        // Запазване на прозрачността за PNG
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        
        // Запълване с бял фон
        $white = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $white);
        
        // Определяне на центъра
        $dstX = ($targetWidth - $newWidth) / 2;
        $dstY = ($targetHeight - $newHeight) / 2;
        
        switch (strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $source = imagecreatefrompng($sourcePath);
                break;
            case 'gif':
                $source = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new Exception("Неподдържан формат на изображението");
        }
        
        // Преоразмеряване със запазване на пропорциите
        imagecopyresampled(
            $thumb, $source,
            $dstX, $dstY,
            0, 0,
            $newWidth, $newHeight,
            $width, $height
        );
        
        // Запазване на миниатюрата
        switch (strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($thumb, $targetPath, 85);
                break;
            case 'png':
                imagepng($thumb, $targetPath, 8);
                break;
            case 'gif':
                imagegif($thumb, $targetPath);
                break;
        }
        
        imagedestroy($thumb);
        imagedestroy($source);
    }
    
    private function optimizeImage($sourcePath) {
        list($width, $height) = getimagesize($sourcePath);
        $maxWidth = 1920;  // Максимална ширина за уеб
        $maxHeight = 1080; // Максимална височина за уеб
        
        // Ако изображението е по-малко от максималните размери, не го преоразмеряваме
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return;
        }
        
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        $optimized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Запазване на прозрачността за PNG
        imagealphablending($optimized, false);
        imagesavealpha($optimized, true);
        
        switch (strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $source = imagecreatefrompng($sourcePath);
                break;
            case 'gif':
                $source = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new Exception("Неподдържан формат на изображението");
        }
        
        // Преоразмеряване
        imagecopyresampled(
            $optimized, $source,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $width, $height
        );
        
        // Презаписване на оригиналния файл с оптимизираната версия
        switch (strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($optimized, $sourcePath, 85);
                break;
            case 'png':
                imagepng($optimized, $sourcePath, 8);
                break;
            case 'gif':
                imagegif($optimized, $sourcePath);
                break;
        }
        
        imagedestroy($optimized);
        imagedestroy($source);
    }
    
    private function saveToDatabase($filename) {
        try {
            $stmt = $this->db->prepare("INSERT INTO property_images (property_id, image_path, created_at) VALUES (?, ?, NOW())");
            if (!$stmt->execute([$this->propertyId, $filename])) {
                $error = $stmt->errorInfo();
                error_log("Database error: " . print_r($error, true));
                throw new Exception("Грешка при запис в базата данни");
            }
        } catch (PDOException $e) {
            error_log("PDO error: " . $e->getMessage());
            throw new Exception("Грешка при запис в базата данни: " . $e->getMessage());
        }
    }
}

try {
    ob_start(); // Започваме буфериране на изхода
    
    // Проверка за админ права
    if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
        throw new Exception('Неоторизиран достъп');
    }

    // Проверка за POST заявка
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Невалиден метод на заявка');
    }

    // Проверка за property_id
    if (!isset($_POST['property_id']) || !is_numeric($_POST['property_id'])) {
        throw new Exception('Невалиден ID на имот');
    }

    // Проверка за файл
    if (!isset($_FILES['image'])) {
        throw new Exception('Не е изпратен файл');
    }

    // Създаване на uploader и качване на файла
    $uploader = new ImageUploader($_POST['property_id'], $_FILES['image']);
    $result = $uploader->upload();

    // Изчистваме буфера преди да изпратим JSON
    ob_clean();
    
    echo json_encode($result);

} catch (Exception $e) {
    error_log('Upload error: ' . $e->getMessage());
    // Изчистваме буфера преди да изпратим JSON
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Край на буферирането
ob_end_flush(); 