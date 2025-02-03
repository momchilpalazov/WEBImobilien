<?php

namespace App\Interfaces;

interface ClientRepositoryInterface
{
    /**
     * Find a client by ID
     */
    public function find(int $id): ?array;
    
    /**
     * Get all clients
     */
    public function findAll(array $criteria = []): array;
    
    /**
     * Create a new client
     */
    public function create(array $data): int;
    
    /**
     * Update a client
     */
    public function update(int $id, array $data): bool;
    
    /**
     * Delete a client
     */
    public function delete(int $id): bool;
    
    /**
     * Get client statistics
     */
    public function getStatistics(array $filters = []): array;
} 