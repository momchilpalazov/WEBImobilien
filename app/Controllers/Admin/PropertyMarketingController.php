<?php

namespace App\Controllers\Admin;

use App\Services\PropertyMarketingService;

class PropertyMarketingController extends BaseAdminController
{
    private PropertyMarketingService $marketingService;

    public function __construct(PropertyMarketingService $marketingService)
    {
        parent::__construct();
        $this->marketingService = $marketingService;
    }

    public function generate(int $id): void
    {
        if (!$this->isPost()) {
            $this->redirect('/admin/properties/' . $id);
            return;
        }

        try {
            $options = [
                'types' => $_POST['types'] ?? []
            ];
            
            $materials = $this->marketingService->generateMarketingMaterials($id, $options);

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'materials' => $materials
                ]);
                return;
            }

            $this->setSuccess('Маркетинговите материали са генерирани успешно.');
            $this->redirect('/admin/properties/' . $id);

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
        $path = 'storage/marketing/' . $filename;
        
        if (!file_exists($path)) {
            $this->setError('Файлът не е намерен.');
            $this->redirect('/admin/properties');
            return;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $contentType = $this->getContentType($extension);

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($path);
        exit;
    }

    private function getContentType(string $extension): string
    {
        $types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf'
        ];

        return $types[$extension] ?? 'application/octet-stream';
    }
} 