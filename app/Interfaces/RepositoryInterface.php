<?php

namespace App\Interfaces;

interface RepositoryInterface
{
    /**
     * Get all records
     *
     * @param array $filters
     * @return array
     */
    public function all(array $filters = []): array;

    /**
     * Find record by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array;

    /**
     * Create new record
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int;

    /**
     * Update existing record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
} 