<?php

namespace App\Controllers\Admin;

use App\Interfaces\AgencyConfigInterface;

class AgencySettingsController extends BaseAdminController
{
    private AgencyConfigInterface $agencyConfig;
    
    public function __construct(AgencyConfigInterface $agencyConfig)
    {
        parent::__construct();
        $this->agencyConfig = $agencyConfig;
    }
    
    public function index(): void
    {
        $config = $this->agencyConfig->getAll();
        
        $this->render('admin/settings/agency', [
            'config' => $config
        ]);
    }
    
    public function update(): void
    {
        try {
            if (!$this->isPost()) {
                $this->redirect('/admin/settings/agency');
                return;
            }
            
            $data = $this->sanitizeInput($_POST);
            
            // Validate required fields
            $requiredFields = [
                'name', 'bulstat', 'address', 'city', 
                'phone', 'email', 'representative'
            ];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new \InvalidArgumentException("Полето '{$field}' е задължително.");
                }
            }
            
            // Update all config values
            foreach ($data as $key => $value) {
                $this->agencyConfig->set($key, $value);
            }
            
            // Save changes
            if (!$this->agencyConfig->save()) {
                throw new \RuntimeException('Грешка при запазване на настройките.');
            }
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Настройките са запазени успешно.'
                ]);
                return;
            }
            
            $this->setSuccess('Настройките са запазени успешно.');
            $this->redirect('/admin/settings/agency');
            
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
            $this->redirect('/admin/settings/agency');
        }
    }
    
    private function sanitizeInput(array $input): array
    {
        $sanitized = [];
        
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
} 