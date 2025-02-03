<?php

namespace App\Interfaces;

interface DocumentManagementInterface
{
    /**
     * Check if user has access to document
     */
    public function hasAccess(int $documentId, int $userId): bool;

    /**
     * Get documents by entity type and ID
     */
    public function getDocumentsByEntity(string $entityType, int $entityId): array;

    /**
     * Search documents with criteria, sorting and pagination
     */
    public function searchDocuments(array $criteria, array $sorting, int $page, int $perPage): array;

    /**
     * Get all document categories
     */
    public function getCategories(): array;

    /**
     * Get document statistics
     */
    public function getStatistics(?string $entityType = null, ?int $entityId = null): array;

    /**
     * Get all documents with optional filters
     */
    public function getAllDocuments(array $filters = []): array;

    /**
     * Get a specific document by ID
     */
    public function getDocumentById(int $id): ?object;

    /**
     * Get documents related to a specific entity (property, deal, etc.)
     */
    public function getRelatedDocuments(string $relationType, int $relationId): array;

    /**
     * Upload a new document
     */
    public function uploadDocument(array $data, array $file): int;

    /**
     * Update document details
     */
    public function updateDocument(int $id, array $data): bool;

    /**
     * Delete a document
     */
    public function deleteDocument(int $id): bool;

    /**
     * Create a new document version
     */
    public function createVersion(int $documentId, array $data, array $file): int;

    /**
     * Get all versions of a document
     */
    public function getDocumentVersions(int $documentId): array;

    /**
     * Add document relations
     */
    public function addDocumentRelations(int $documentId, array $relations): bool;

    /**
     * Remove document relations
     */
    public function removeDocumentRelations(int $documentId, array $relations);

    /**
     * Add signature request
     */
    public function addSignatureRequest(int $documentId, array $signerData);

    /**
     * Update signature status
     */
    public function updateSignatureStatus(int $signatureId, string $status, array $data = []);

    /**
     * Get document signatures
     */
    public function getDocumentSignatures(int $documentId);

    /**
     * Log document access
     */
    public function logAccess(int $documentId, int $userId, string $actionType, array $details = []);

    /**
     * Get document access logs
     */
    public function getAccessLogs(int $documentId);

    /**
     * Get all document templates
     */
    public function getAllTemplates(array $filters = []);

    /**
     * Get a specific template by ID
     */
    public function getTemplateById(int $id);

    /**
     * Create a new document template
     */
    public function createTemplate(array $data);

    /**
     * Update an existing template
     */
    public function updateTemplate(int $id, array $data);

    /**
     * Delete a template
     */
    public function deleteTemplate(int $id);

    /**
     * Generate document from template
     */
    public function generateFromTemplate(int $templateId, array $variables);

    /**
     * Share document with external users
     */
    public function shareDocument(int $documentId, array $shareData);

    /**
     * Update document share settings
     */
    public function updateShare(int $shareId, array $data);

    /**
     * Revoke document share
     */
    public function revokeShare(int $shareId);

    /**
     * Get document shares
     */
    public function getDocumentShares(int $documentId);

    /**
     * Validate document share access
     */
    public function validateShareAccess(string $accessToken);
} 