<?php

namespace App\Services;

use App\Interfaces\ContractServiceInterface;
use App\Interfaces\ContractTemplateInterface;
use App\Interfaces\PropertyRepositoryInterface;
use App\Interfaces\ClientRepositoryInterface;
use App\Interfaces\AgencyConfigInterface;
use App\Exceptions\ContractException;

class ContractService implements ContractServiceInterface
{
    private ContractTemplateInterface $templateService;
    private ContractExportService $exportService;
    private PropertyRepositoryInterface $propertyRepository;
    private ClientRepositoryInterface $clientRepository;
    private AgencyConfigInterface $agencyConfig;
    private string $contractsPath;
    
    public function __construct(
        ContractTemplateInterface $templateService,
        ContractExportService $exportService,
        PropertyRepositoryInterface $propertyRepository,
        ClientRepositoryInterface $clientRepository,
        AgencyConfigInterface $agencyConfig,
        string $contractsPath = 'storage/contracts'
    ) {
        $this->templateService = $templateService;
        $this->exportService = $exportService;
        $this->propertyRepository = $propertyRepository;
        $this->clientRepository = $clientRepository;
        $this->agencyConfig = $agencyConfig;
        $this->contractsPath = $contractsPath;
        
        if (!is_dir($this->contractsPath)) {
            mkdir($this->contractsPath, 0755, true);
        }
    }
    
    public function generateContract(int $propertyId, string $type, array $data): string
    {
        try {
            // Validate contract type and data
            $this->validateContractData($type, $data);
            
            // Get property details
            $property = $this->propertyRepository->find($propertyId);
            if (!$property) {
                throw new ContractException('Имотът не е намерен.');
            }
            
            // Get template
            $template = $this->templateService->getTemplate($type);
            
            // Prepare data for template
            $templateData = $this->prepareContractData($property, $data);
            
            // Replace variables in template
            $content = $this->replaceVariables($template, $templateData);
            
            // Generate PDF filename
            $filename = $this->generateFilename($type, $propertyId);
            
            // Save contract data to database
            $contractId = $this->saveContract([
                'property_id' => $propertyId,
                'type' => $type,
                'filename' => $filename,
                'data' => json_encode($data),
                'status' => 'draft',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Export to PDF
            $pdfPath = $this->contractsPath . '/' . $filename;
            if (!$this->exportService->exportToPdf($content, $pdfPath)) {
                throw new ContractException('Грешка при генериране на PDF файл.');
            }
            
            return $filename;
        } catch (\Exception $e) {
            throw new ContractException($e->getMessage());
        }
    }
    
    public function getContract(int $id): ?array
    {
        // TODO: Implement database query
        return null;
    }
    
    public function getPropertyContracts(int $propertyId): array
    {
        // TODO: Implement database query
        return [];
    }
    
    public function getClientContracts(int $clientId): array
    {
        // TODO: Implement database query
        return [];
    }
    
    public function saveContract(array $data): int
    {
        // TODO: Implement database insert/update
        return 0;
    }
    
    public function updateStatus(int $id, string $status): bool
    {
        // TODO: Implement database update
        return false;
    }
    
    public function deleteContract(int $id): bool
    {
        // TODO: Implement database delete and file removal
        return false;
    }
    
    public function getStatistics(array $filters = []): array
    {
        // TODO: Implement statistics calculation
        return [];
    }
    
    private function validateContractData(string $type, array $data): void
    {
        $requiredFields = $this->templateService->getRequiredFields($type);
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new ContractException("Полето '{$field}' е задължително.");
            }
        }
    }
    
    private function prepareContractData(array $property, array $data): array
    {
        $agencyData = $this->agencyConfig->getAll();
        
        return array_merge($data, [
            'property_id' => $property['id'],
            'property_address' => $property['address'],
            'property_description' => $property['description'],
            'property_area' => $property['area'],
            'contract_date' => date('d.m.Y'),
            'contract_number' => date('Ymd') . '-' . str_pad($property['id'], 3, '0', STR_PAD_LEFT),
            'agency_name' => $agencyData['name'],
            'agency_address' => $agencyData['address'],
            'agency_bulstat' => $agencyData['bulstat'],
            'agency_city' => $agencyData['city'],
            'agency_phone' => $agencyData['phone'],
            'agency_email' => $agencyData['email'],
            'agency_representative' => $agencyData['representative'],
            'agency_bank_name' => $agencyData['bank_name'],
            'agency_bank_account' => $agencyData['bank_account'],
            'agency_bank_bic' => $agencyData['bank_bic']
        ]);
    }
    
    private function replaceVariables(string $template, array $data): string
    {
        return preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($data) {
            $variable = $matches[1];
            return $data[$variable] ?? $matches[0];
        }, $template);
    }
    
    private function generateFilename(string $type, int $propertyId): string
    {
        return sprintf(
            '%s_%s_%s.pdf',
            $type,
            $propertyId,
            date('Y-m-d_H-i-s')
        );
    }
} 