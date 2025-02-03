<?php

namespace App\Controllers\Admin;

use App\Services\PropertyContractService;

class PropertyContractController extends BaseAdminController
{
    private PropertyContractService $contractService;

    public function __construct(PropertyContractService $contractService)
    {
        parent::__construct();
        $this->contractService = $contractService;
    }

    public function generate(int $id): void
    {
        if (!$this->isPost()) {
            $this->redirect('/admin/properties/' . $id);
            return;
        }

        try {
            $type = $_POST['type'] ?? '';
            $data = $this->sanitizeInput($_POST);
            
            $filename = $this->contractService->generateContract($id, $type, $data);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'url' => "/admin/contracts/download/{$filename}"
                ]);
                return;
            }

            $this->redirect("/admin/contracts/download/{$filename}");

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $this->setError($e->getMessage());
            $this->redirect('/admin/properties/' . $id);
        }
    }

    public function download(string $filename): void
    {
        $path = 'storage/contracts/' . $filename;
        
        if (!file_exists($path)) {
            $this->setError('Файлът не е намерен.');
            $this->redirect('/admin/properties');
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($path);
        exit;
    }

    private function sanitizeInput(array $input): array
    {
        $sanitized = [];
        
        foreach ($input as $key => $value) {
            if ($key === 'type') continue; // Типът се валидира отделно
            
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } elseif (is_numeric($value)) {
                $sanitized[$key] = floatval($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
} 