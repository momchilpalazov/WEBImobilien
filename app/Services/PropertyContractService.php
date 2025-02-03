<?php

namespace App\Services;

class PropertyContractService
{
    private $propertyRepository;
    private $templateService;
    private $exportService;
    private $contractPath;

    public function __construct(
        PropertyRepositoryInterface $propertyRepository,
        TemplateService $templateService,
        ExportService $exportService,
        string $contractPath = 'storage/contracts'
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->templateService = $templateService;
        $this->exportService = $exportService;
        $this->contractPath = $contractPath;
    }

    public function generateContract(int $propertyId, string $type, array $data): string
    {
        try {
            $this->validateContractData($type, $data);
            
            $property = $this->propertyRepository->find($propertyId);
            if (!$property) {
                throw new \Exception('Имотът не е намерен.');
            }

            $templateData = $this->prepareContractData($property, $data);
            $template = $this->getContractTemplate($type);
            
            $contract = $this->templateService->render($template, $templateData);
            
            return $this->saveContract($contract, $type, $propertyId);
        } catch (\Exception $e) {
            error_log("Error generating contract: " . $e->getMessage());
            throw $e;
        }
    }

    private function validateContractData(string $type, array $data): void
    {
        $requiredFields = $this->getRequiredFields($type);
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Полето '{$field}' е задължително.");
            }
        }

        switch ($type) {
            case 'rental':
                $this->validateRentalContract($data);
                break;
            case 'sale':
                $this->validateSaleContract($data);
                break;
            case 'preliminary':
                $this->validatePreliminaryContract($data);
                break;
            default:
                throw new \Exception('Невалиден тип договор.');
        }
    }

    private function validateRentalContract(array $data): void
    {
        if (!isset($data['rental_period']) || $data['rental_period'] < 1) {
            throw new \Exception('Невалиден период на наем.');
        }
        if (!isset($data['monthly_rent']) || $data['monthly_rent'] <= 0) {
            throw new \Exception('Невалидна месечна наемна цена.');
        }
        if (!isset($data['deposit']) || $data['deposit'] < 0) {
            throw new \Exception('Невалиден депозит.');
        }
    }

    private function validateSaleContract(array $data): void
    {
        if (!isset($data['price']) || $data['price'] <= 0) {
            throw new \Exception('Невалидна продажна цена.');
        }
        if (!isset($data['payment_method'])) {
            throw new \Exception('Не е избран метод на плащане.');
        }
    }

    private function validatePreliminaryContract(array $data): void
    {
        if (!isset($data['deposit_amount']) || $data['deposit_amount'] <= 0) {
            throw new \Exception('Невалиден капаро.');
        }
        if (!isset($data['final_contract_date'])) {
            throw new \Exception('Не е зададена дата за окончателен договор.');
        }
    }

    private function getRequiredFields(string $type): array
    {
        $common = ['client_name', 'client_egn', 'client_address'];
        
        switch ($type) {
            case 'rental':
                return array_merge($common, ['rental_period', 'monthly_rent', 'deposit']);
            case 'sale':
                return array_merge($common, ['price', 'payment_method']);
            case 'preliminary':
                return array_merge($common, ['deposit_amount', 'final_contract_date']);
            default:
                return $common;
        }
    }

    private function prepareContractData(array $property, array $data): array
    {
        return array_merge($data, [
            'property_id' => $property['id'],
            'property_address' => $property['address'],
            'property_description' => $property['description'],
            'property_area' => $property['area'],
            'contract_date' => date('Y-m-d'),
            'agency_name' => 'Имоти ООД', // TODO: Get from config
            'agency_address' => 'ул. Примерна 1', // TODO: Get from config
            'agency_bulstat' => '123456789', // TODO: Get from config
            'legal_clauses' => $this->getLegalClauses($data['type'])
        ]);
    }

    private function getContractTemplate(string $type): string
    {
        $templates = [
            'rental' => 'contracts/rental',
            'sale' => 'contracts/sale',
            'preliminary' => 'contracts/preliminary'
        ];

        if (!isset($templates[$type])) {
            throw new \Exception('Невалиден тип договор.');
        }

        return $templates[$type];
    }

    private function getLegalClauses(string $type): array
    {
        // TODO: Load from database or config
        return [
            'general' => 'Общи разпоредби...',
            'specific' => 'Специфични условия за ' . $type . '...',
            'termination' => 'Условия за прекратяване...',
            'disputes' => 'Разрешаване на спорове...'
        ];
    }

    private function saveContract(string $content, string $type, int $propertyId): string
    {
        $filename = sprintf(
            '%s_%s_%s.pdf',
            $type,
            $propertyId,
            date('Y-m-d_H-i-s')
        );

        $path = $this->contractPath . '/' . $filename;
        
        if (!is_dir($this->contractPath)) {
            mkdir($this->contractPath, 0755, true);
        }

        $this->exportService->exportToPdf($content, $path);

        return $filename;
    }
} 