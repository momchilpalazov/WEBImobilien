<?php

namespace App\Interfaces;

interface ContractTemplateInterface
{
    /**
     * Get the template content for a specific contract type
     */
    public function getTemplate(string $type): string;
    
    /**
     * Get all available template types
     */
    public function getAvailableTypes(): array;
    
    /**
     * Get required fields for a specific template type
     */
    public function getRequiredFields(string $type): array;
    
    /**
     * Get template variables for a specific type
     */
    public function getTemplateVariables(string $type): array;
    
    /**
     * Save a new template
     */
    public function saveTemplate(string $type, string $content): bool;
    
    /**
     * Delete a template
     */
    public function deleteTemplate(string $type): bool;
} 