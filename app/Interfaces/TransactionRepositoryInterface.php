<?php

namespace App\Interfaces;

use App\Models\Transaction;

interface TransactionRepositoryInterface
{
    public function findById(int $id): ?Transaction;
    
    public function findAll(array $filters = [], array $sorting = [], int $page = 1, int $perPage = 20): array;
    
    public function create(Transaction $transaction): int;
    
    public function update(Transaction $transaction): bool;
    
    public function delete(int $id): bool;
    
    public function getTotalsByPeriod(string $startDate, string $endDate, string $type = null): array;
    
    public function getAgentPerformance(int $agentId, string $startDate, string $endDate): array;
} 