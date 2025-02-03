<?php

namespace App\Interfaces;

interface ClientManagementInterface
{
    /**
     * Get all clients with optional filtering
     */
    public function getAllClients(array $filters = []);

    /**
     * Get a specific client by ID
     */
    public function getClientById(int $id);

    /**
     * Create a new client
     */
    public function createClient(array $data);

    /**
     * Update an existing client
     */
    public function updateClient(int $id, array $data);

    /**
     * Delete a client
     */
    public function deleteClient(int $id);

    /**
     * Get client preferences
     */
    public function getClientPreferences(int $clientId);

    /**
     * Update client preferences
     */
    public function updateClientPreferences(int $clientId, array $preferences);

    /**
     * Add client interaction
     */
    public function addInteraction(int $clientId, array $interactionData);

    /**
     * Get client interactions history
     */
    public function getInteractions(int $clientId, array $filters = []);

    /**
     * Get property matches for a client
     */
    public function getPropertyMatches(int $clientId, array $filters = []);

    /**
     * Update property match status
     */
    public function updateMatchStatus(int $matchId, string $status);

    /**
     * Calculate property matches for a client
     */
    public function calculateMatches(int $clientId);

    /**
     * Link document to client
     */
    public function linkDocument(int $clientId, int $documentId);

    /**
     * Get client documents
     */
    public function getClientDocuments(int $clientId);
} 