<?php

namespace App\Services;

use App\Interfaces\ContractTemplateInterface;
use App\Exceptions\TemplateNotFoundException;
use App\Exceptions\InvalidTemplateException;

class ContractTemplateService implements ContractTemplateInterface
{
    private string $templatesPath;
    private array $availableTypes = [
        'rental' => 'Договор за наем',
        'sale' => 'Договор за продажба',
        'preliminary' => 'Предварителен договор',
        'brokerage' => 'Брокерски договор'
    ];
    
    private array $requiredFieldsByType = [
        'rental' => [
            'client_name', 'client_egn', 'client_address',
            'rental_period', 'monthly_rent', 'deposit'
        ],
        'sale' => [
            'client_name', 'client_egn', 'client_address',
            'price', 'payment_method'
        ],
        'preliminary' => [
            'client_name', 'client_egn', 'client_address',
            'deposit_amount', 'final_contract_date'
        ],
        'brokerage' => [
            'client_name', 'client_egn', 'client_address',
            'commission_rate', 'service_period'
        ]
    ];
    
    public function __construct(string $templatesPath = 'storage/templates/contracts')
    {
        $this->templatesPath = $templatesPath;
        if (!is_dir($this->templatesPath)) {
            mkdir($this->templatesPath, 0755, true);
        }
    }
    
    public function getTemplate(string $type): string
    {
        if (!isset($this->availableTypes[$type])) {
            throw new InvalidTemplateException("Невалиден тип на договор: {$type}");
        }
        
        $templatePath = $this->getTemplatePath($type);
        if (!file_exists($templatePath)) {
            throw new TemplateNotFoundException("Шаблонът не е намерен: {$type}");
        }
        
        return file_get_contents($templatePath);
    }
    
    public function getAvailableTypes(): array
    {
        return $this->availableTypes;
    }
    
    public function getRequiredFields(string $type): array
    {
        if (!isset($this->requiredFieldsByType[$type])) {
            throw new InvalidTemplateException("Невалиден тип на договор: {$type}");
        }
        
        return $this->requiredFieldsByType[$type];
    }
    
    public function getTemplateVariables(string $type): array
    {
        $template = $this->getTemplate($type);
        preg_match_all('/\{\{([^}]+)\}\}/', $template, $matches);
        
        return array_unique($matches[1] ?? []);
    }
    
    public function saveTemplate(string $type, string $content): bool
    {
        if (!isset($this->availableTypes[$type])) {
            throw new InvalidTemplateException("Невалиден тип на договор: {$type}");
        }
        
        $templatePath = $this->getTemplatePath($type);
        return file_put_contents($templatePath, $content) !== false;
    }
    
    public function deleteTemplate(string $type): bool
    {
        if (!isset($this->availableTypes[$type])) {
            throw new InvalidTemplateException("Невалиден тип на договор: {$type}");
        }
        
        $templatePath = $this->getTemplatePath($type);
        if (!file_exists($templatePath)) {
            return true;
        }
        
        return unlink($templatePath);
    }
    
    private function getTemplatePath(string $type): string
    {
        return $this->templatesPath . '/' . $type . '.html';
    }
} 