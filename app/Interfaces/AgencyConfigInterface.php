<?php

namespace App\Interfaces;

interface AgencyConfigInterface
{
    /**
     * Get all agency configuration settings
     */
    public function getAll(): array;
    
    /**
     * Get a specific configuration value
     */
    public function get(string $key, $default = null);
    
    /**
     * Set a configuration value
     */
    public function set(string $key, $value): bool;
    
    /**
     * Save all configuration changes
     */
    public function save(): bool;
} 