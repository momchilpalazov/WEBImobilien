<?php

namespace App\Services;

use App\Interfaces\AgencyConfigInterface;

class AgencyConfigService implements AgencyConfigInterface
{
    private string $configPath;
    private array $config;
    
    public function __construct(string $configPath = 'storage/config/agency.json')
    {
        $this->configPath = $configPath;
        $this->loadConfig();
    }
    
    public function getAll(): array
    {
        return $this->config;
    }
    
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
    
    public function set(string $key, $value): bool
    {
        $this->config[$key] = $value;
        return true;
    }
    
    public function save(): bool
    {
        try {
            $dir = dirname($this->configPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            return file_put_contents(
                $this->configPath,
                json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ) !== false;
        } catch (\Exception $e) {
            error_log("Error saving agency config: " . $e->getMessage());
            return false;
        }
    }
    
    private function loadConfig(): void
    {
        $defaultConfig = [
            'name' => 'Имоти ООД',
            'bulstat' => '123456789',
            'address' => 'ул. Примерна 1',
            'city' => 'София',
            'phone' => '+359 2 123 4567',
            'email' => 'office@imoti.com',
            'representative' => 'Иван Иванов',
            'bank_name' => 'Примерна Банка АД',
            'bank_account' => 'BG00BANK12345678901234',
            'bank_bic' => 'BANKBGSF'
        ];
        
        if (file_exists($this->configPath)) {
            try {
                $savedConfig = json_decode(file_get_contents($this->configPath), true);
                if (is_array($savedConfig)) {
                    $this->config = array_merge($defaultConfig, $savedConfig);
                    return;
                }
            } catch (\Exception $e) {
                error_log("Error loading agency config: " . $e->getMessage());
            }
        }
        
        $this->config = $defaultConfig;
        $this->save(); // Save default config if no file exists
    }
} 