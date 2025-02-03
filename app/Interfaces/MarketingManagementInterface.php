<?php

namespace App\Interfaces;

interface MarketingManagementInterface
{
    /**
     * Get all marketing materials for a property
     */
    public function getPropertyMaterials(int $propertyId, array $filters = []);

    /**
     * Get a specific marketing material by ID
     */
    public function getMaterialById(int $id);

    /**
     * Upload a new marketing material
     */
    public function uploadMaterial(array $data, array $file);

    /**
     * Update marketing material details
     */
    public function updateMaterial(int $id, array $data);

    /**
     * Delete a marketing material
     */
    public function deleteMaterial(int $id);

    /**
     * Update material sort order
     */
    public function updateSortOrder(array $sortData);

    /**
     * Toggle material featured status
     */
    public function toggleFeatured(int $id);

    /**
     * Get all marketing campaigns
     */
    public function getAllCampaigns(array $filters = []);

    /**
     * Get a specific campaign by ID
     */
    public function getCampaignById(int $id);

    /**
     * Create a new marketing campaign
     */
    public function createCampaign(array $data);

    /**
     * Update an existing campaign
     */
    public function updateCampaign(int $id, array $data);

    /**
     * Delete a marketing campaign
     */
    public function deleteCampaign(int $id);

    /**
     * Add properties to a campaign
     */
    public function addCampaignProperties(int $campaignId, array $propertyIds);

    /**
     * Remove properties from a campaign
     */
    public function removeCampaignProperties(int $campaignId, array $propertyIds);

    /**
     * Add a channel to a campaign
     */
    public function addCampaignChannel(int $campaignId, array $channelData);

    /**
     * Update a campaign channel
     */
    public function updateCampaignChannel(int $channelId, array $channelData);

    /**
     * Delete a campaign channel
     */
    public function deleteCampaignChannel(int $channelId);

    /**
     * Record marketing analytics
     */
    public function recordAnalytics(array $data);

    /**
     * Get analytics for a material/campaign/channel
     */
    public function getAnalytics(string $type, int $id, array $filters = []);

    /**
     * Generate campaign report
     */
    public function generateCampaignReport(int $campaignId, array $filters = []);
} 