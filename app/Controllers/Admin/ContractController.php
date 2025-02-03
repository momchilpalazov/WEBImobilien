<?php

namespace App\Controllers\Admin;

use App\Interfaces\ContractServiceInterface;
use App\Interfaces\PropertyRepositoryInterface;
use App\Interfaces\ClientRepositoryInterface;
use App\Exceptions\ContractException;

class ContractController extends BaseAdminController
{
    private ContractServiceInterface $contractService;
    private PropertyRepositoryInterface $propertyRepository;
    private ClientRepositoryInterface $clientRepository;
    
    public function __construct(
        ContractServiceInterface $contractService,
        PropertyRepositoryInterface $propertyRepository,
        ClientRepositoryInterface $clientRepository
    ) {
        parent::__construct();
        $this->contractService = $contractService;
        $this->propertyRepository = $propertyRepository;
        $this->clientRepository = $clientRepository;
    }
    
    public function index(): void
    {
        $filters = $_GET['filter'] ?? [];
        $contracts = $this->contractService->getStatistics($filters);
        
        $this->render('admin/contracts/index', [
            'contracts' => $contracts,
            'filters' => $filters
        ]);
    }
    
    public function generate(int $propertyId): void
    {
        try {
            if (!$this->isPost()) {
                $property = $this->propertyRepository->find($propertyId);
                if (!$property) {
                    throw new ContractException('Имотът не е намерен.');
                }
                
                $this->render('admin/contracts/generate', [
                    'property' => $property
                ]);
                return;
            }
            
            $type = $_POST['type'] ?? '';
            $data = $this->sanitizeInput($_POST);
            
            $filename = $this->contractService->generateContract($propertyId, $type, $data);
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'url' => "/admin/contracts/download/{$filename}"
                ]);
                return;
            }
            
            $this->setSuccess('Договорът е генериран успешно.');
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
            $this->redirect("/admin/properties/{$propertyId}");
        }
    }
    
    public function download(string $filename): void
    {
        $path = 'storage/contracts/' . $filename;
        
        if (!file_exists($path)) {
            $this->setError('Файлът не е намерен.');
            $this->redirect('/admin/contracts');
            return;
        }
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($path);
        exit;
    }
    
    public function delete(int $id): void
    {
        try {
            if (!$this->isPost()) {
                $this->redirect('/admin/contracts');
                return;
            }
            
            if (!$this->contractService->deleteContract($id)) {
                throw new ContractException('Грешка при изтриване на договора.');
            }
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Договорът е изтрит успешно.'
                ]);
                return;
            }
            
            $this->setSuccess('Договорът е изтрит успешно.');
            $this->redirect('/admin/contracts');
            
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
            $this->redirect('/admin/contracts');
        }
    }
    
    public function updateStatus(int $id): void
    {
        try {
            if (!$this->isPost()) {
                $this->redirect('/admin/contracts');
                return;
            }
            
            $status = $_POST['status'] ?? '';
            if (empty($status)) {
                throw new ContractException('Статусът е задължителен.');
            }
            
            if (!$this->contractService->updateStatus($id, $status)) {
                throw new ContractException('Грешка при обновяване на статуса.');
            }
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Статусът е обновен успешно.'
                ]);
                return;
            }
            
            $this->setSuccess('Статусът е обновен успешно.');
            $this->redirect('/admin/contracts');
            
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
            $this->redirect('/admin/contracts');
        }
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