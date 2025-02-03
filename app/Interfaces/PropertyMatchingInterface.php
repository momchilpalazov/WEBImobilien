<?php

namespace App\Interfaces;

interface PropertyMatchingInterface
{
    /**
     * Find matching properties for a client based on their preferences
     *
     * @param int $clientId
     * @param array $preferences Optional additional preferences to override stored ones
     * @return array Array of matching properties with match scores
     */
    public function findMatchingProperties(int $clientId, array $preferences = []): array;
    
    /**
     * Find matching clients for a property
     *
     * @param int $propertyId
     * @return array Array of matching clients with match scores
     */
    public function findMatchingClients(int $propertyId): array;
    
    /**
     * Update client preferences
     *
     * @param int $clientId
     * @param array $preferences
     * @return bool
     */
    public function updateClientPreferences(int $clientId, array $preferences): bool;
    
    /**
     * Get match score between a property and client preferences
     *
     * @param int $propertyId
     * @param int $clientId
     * @return array Match details with overall score and individual criteria scores
     */
    public function getMatchScore(int $propertyId, int $clientId): array;
    
    /**
     * Save match history
     *
     * @param int $propertyId
     * @param int $clientId
     * @param array $matchDetails
     * @return bool
     */
    public function saveMatchHistory(int $propertyId, int $clientId, array $matchDetails): bool;
    
    /**
     * Get match history for a client
     *
     * @param int $clientId
     * @return array
     */
    public function getClientMatchHistory(int $clientId): array;
} 