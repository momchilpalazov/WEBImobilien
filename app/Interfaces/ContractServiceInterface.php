<?php

namespace App\Interfaces;

interface ContractServiceInterface
{
    /**
     * Generate a contract for a property
     */
    public function generateContract(int $propertyId, string $type, array $data): string;
    
    /**
     * Get contract details by ID
     */
    public function getContract(int $id): ?array;
    
    /**
     * Get all contracts for a property
     */
    public function getPropertyContracts(int $propertyId): array;
    
    /**
     * Get all contracts for a client
     */
    public function getClientContracts(int $clientId): array;
    
    /**
     * Save contract data
     */
    public function saveContract(array $data): int;
    
    /**
     * Update contract status
     */
    public function updateStatus(int $id, string $status): bool;
    
    /**
     * Delete a contract
     */
    public function deleteContract(int $id): bool;
    
    /**
     * Get contract statistics
     */
    public function getStatistics(array $filters = []): array;
} 