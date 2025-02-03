<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\FileUploadService;

class UploadController extends BaseController
{
    private FileUploadService $fileUploader;

    public function __construct(FileUploadService $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    public function uploadImage(): void
    {
        try {
            if (!isset($_FILES['file'])) {
                $this->jsonResponse([
                    'error' => 'No file uploaded'
                ], 400);
                return;
            }

            $file = $_FILES['file'];
            $path = $this->fileUploader->upload($file, 'editor');

            $this->jsonResponse([
                'location' => '/' . $path
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
    }
} 