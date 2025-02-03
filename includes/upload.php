<?php
class Upload {
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    private $maxSize = 5242880; // 5MB
    
    public function validateFile($file) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid file parameters');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }

        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception('Invalid file type');
        }

        if ($file['size'] > $this->maxSize) {
            throw new Exception('File too large');
        }

        return true;
    }

    public function saveFile($file, $destination) {
        $this->validateFile($file);
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $destination . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file');
        }

        return $filename;
    }
} 