<?php

namespace App\Interfaces;

use App\Models\Property;

interface PropertyRepositoryInterface
{
    /**
     * Get all properties
     *
     * @return array
     */
    public function getAllProperties(): array;

    /**
     * Get property by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getPropertyById(int $id): ?array;

    /**
     * Get properties by status
     *
     * @param string $status
     * @return array
     */
    public function getPropertiesByStatus(string $status): array;

    /**
     * Get properties by type
     *
     * @param string $type
     * @return array
     */
    public function getPropertiesByType(string $type): array;

    /**
     * Create a new property
     *
     * @param array $data
     * @return int ID of the created property
     * @throws \Exception
     */
    public function create(Property $property): int;

    /**
     * Update an existing property
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a property
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete(int $id): bool;

    /**
     * Find properties by filters with pagination
     *
     * @param array $filters
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function findByFilters(array $filters = [], int $offset = 0, int $limit = 10): array;

    /**
     * Count total properties matching filters
     *
     * @param array $filters
     * @return int
     */
    public function countByFilters(array $filters = []): int;

    /**
     * Get available property types
     *
     * @return array
     */
    public function getAvailableTypes(): array;

    /**
     * Get available property statuses
     *
     * @return array
     */
    public function getAvailableStatuses(): array;

    /**
     * Find property by ID
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?Property;

    /**
     * Get property images
     *
     * @param int $propertyId
     * @return array
     */
    public function getPropertyImages(int $propertyId): array;

    /**
     * Find similar properties based on type, price and area
     *
     * @param string $type Property type
     * @param float $price Target price
     * @param float $area Target area
     * @param int $excludeId Property ID to exclude
     * @param int $limit Maximum number of properties to return
     * @return array
     */
    public function findSimilar(string $type, float $price, float $area, int $excludeId, int $limit = 4): array;

    public function findAll(array $filters = [], array $sorting = [], int $page = 1, int $perPage = 20): array;
} 
