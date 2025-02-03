<?php

namespace App\Services;

class FileUploadService
{
    private string $uploadDir;
    private array $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];
    private int $maxFileSize = 5242880; // 5MB

    public function __construct(string $baseUploadDir = null)
    {
        $this->uploadDir = $baseUploadDir ?? __DIR__ . '/../../public/uploads/';
    }

    /**
     * Upload a file
     *
     * @param array $file The uploaded file array from $_FILES
     * @param string $subdirectory Optional subdirectory within the upload directory
     * @return string The path to the uploaded file relative to the public directory
     * @throws \Exception If the file upload fails
     */
    public function upload(array $file, string $subdirectory = ''): string
    {
        $this->validateFile($file);

        $targetDir = $this->uploadDir . trim($subdirectory, '/') . '/';
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new \Exception('Failed to create upload directory');
            }
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $this->generateUniqueFilename($extension);
        $targetPath = $targetDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception('Failed to move uploaded file');
        }

        // Return the path relative to the public directory
        return 'uploads/' . ($subdirectory ? trim($subdirectory, '/') . '/' : '') . $filename;
    }

    /**
     * Delete a file
     *
     * @param string $path The path to the file relative to the public directory
     * @return bool Whether the deletion was successful
     */
    public function delete(string $path): bool
    {
        $fullPath = $this->uploadDir . str_replace('uploads/', '', $path);
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    /**
     * Validate the uploaded file
     *
     * @param array $file The uploaded file array from $_FILES
     * @throws \Exception If the file is invalid
     */
    private function validateFile(array $file): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('File upload failed with error code: ' . $file['error']);
        }

        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new \Exception('Invalid file type. Allowed types: ' . implode(', ', $this->allowedTypes));
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception('File size exceeds the maximum allowed size of ' . ($this->maxFileSize / 1024 / 1024) . 'MB');
        }
    }

    /**
     * Generate a unique filename
     *
     * @param string $extension The file extension
     * @return string The generated filename
     */
    private function generateUniqueFilename(string $extension): string
    {
        return uniqid() . '_' . time() . '.' . $extension;
    }

    /**
     * Set the maximum allowed file size in bytes
     *
     * @param int $size The maximum file size in bytes
     */
    public function setMaxFileSize(int $size): void
    {
        $this->maxFileSize = $size;
    }

    /**
     * Add allowed file types
     *
     * @param array $types Array of MIME types
     */
    public function addAllowedTypes(array $types): void
    {
        $this->allowedTypes = array_merge($this->allowedTypes, $types);
    }

    /**
     * Set the base upload directory
     *
     * @param string $dir The base upload directory path
     * @throws \Exception If the directory is not writable
     */
    public function setUploadDir(string $dir): void
    {
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new \Exception('Upload directory is not writable');
        }
        $this->uploadDir = rtrim($dir, '/') . '/';
    }
} 