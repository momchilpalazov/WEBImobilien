<?php

namespace App\Controllers\Admin;

use App\Interfaces\PropertyMatchingInterface;
use App\Interfaces\PropertyRepositoryInterface;
use App\Interfaces\ClientRepositoryInterface;

class PropertyMatchingController extends BaseAdminController
{
    private PropertyMatchingInterface $matchingService;
    private PropertyRepositoryInterface $propertyRepository;
    private ClientRepositoryInterface $clientRepository;
    
    public function __construct(
        PropertyMatchingInterface $matchingService,
        PropertyRepositoryInterface $propertyRepository,
        ClientRepositoryInterface $clientRepository
    ) {
        parent::__construct();
        $this->matchingService = $matchingService;
        $this->propertyRepository = $propertyRepository;
        $this->clientRepository = $clientRepository;
    }
    
    public function findMatchingProperties(int $clientId): void
    {
        try {
            $preferences = [];
            if ($this->isPost()) {
                $preferences = $this->sanitizeInput($_POST);
            }
            
            $matches = $this->matchingService->findMatchingProperties($clientId, $preferences);
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'matches' => $matches
                ]);
                return;
            }
            
            $client = $this->clientRepository->find($clientId);
            $this->render('admin/matching/properties', [
                'client' => $client,
                'matches' => $matches
            ]);
            
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
            $this->redirect('/admin/clients/' . $clientId);
        }
    }
    
    public function findMatchingClients(int $propertyId): void
    {
        try {
            $matches = $this->matchingService->findMatchingClients($propertyId);
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'matches' => $matches
                ]);
                return;
            }
            
            $property = $this->propertyRepository->findById($propertyId);
            $this->render('admin/matching/clients', [
                'property' => $property,
                'matches' => $matches
            ]);
            
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
            $this->redirect('/admin/properties/' . $propertyId);
        }
    }
    
    public function updatePreferences(int $clientId): void
    {
        try {
            if (!$this->isPost()) {
                $this->redirect('/admin/clients/' . $clientId);
                return;
            }
            
            $preferences = $this->sanitizeInput($_POST);
            
            if ($this->matchingService->updateClientPreferences($clientId, $preferences)) {
                if ($this->isAjax()) {
                    $this->json([
                        'success' => true,
                        'message' => 'Предпочитанията са обновени успешно.'
                    ]);
                    return;
                }
                
                $this->setSuccess('Предпочитанията са обновени успешно.');
            } else {
                throw new \Exception('Грешка при обновяване на предпочитанията.');
            }
            
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
        }
        
        $this->redirect('/admin/clients/' . $clientId);
    }
    
    public function matchHistory(int $clientId): void
    {
        try {
            $history = $this->matchingService->getClientMatchHistory($clientId);
            $client = $this->clientRepository->find($clientId);
            
            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'history' => $history
                ]);
                return;
            }
            
            $this->render('admin/matching/history', [
                'client' => $client,
                'history' => $history
            ]);
            
        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
            
            $this->setError($e->getMessage());
            $this->redirect('/admin/clients/' . $clientId);
        }
    }
    
    private function sanitizeInput(array $input): array
    {
        $sanitized = [];
        
        // Основни критерии
        $sanitized['property_type'] = filter_var($input['property_type'] ?? '', FILTER_SANITIZE_STRING);
        $sanitized['price_min'] = filter_var($input['price_min'] ?? 0, FILTER_VALIDATE_FLOAT);
        $sanitized['price_max'] = filter_var($input['price_max'] ?? 0, FILTER_VALIDATE_FLOAT);
        $sanitized['area_min'] = filter_var($input['area_min'] ?? 0, FILTER_VALIDATE_FLOAT);
        $sanitized['area_max'] = filter_var($input['area_max'] ?? 0, FILTER_VALIDATE_FLOAT);
        
        // Локации
        $sanitized['locations'] = [];
        if (isset($input['locations']) && is_array($input['locations'])) {
            foreach ($input['locations'] as $location) {
                $sanitized['locations'][] = filter_var($location, FILTER_SANITIZE_STRING);
            }
        }
        
        // Характеристики
        $sanitized['required_features'] = [];
        if (isset($input['required_features']) && is_array($input['required_features'])) {
            foreach ($input['required_features'] as $feature) {
                $sanitized['required_features'][] = filter_var($feature, FILTER_SANITIZE_STRING);
            }
        }
        
        return $sanitized;
    }
} 