<?php

namespace App\Interfaces;

interface ViewingManagementInterface
{
    /**
     * Get all viewings with optional filtering
     */
    public function getAllViewings(array $filters = []);

    /**
     * Get a specific viewing by ID
     */
    public function getViewingById(int $id);

    /**
     * Create a new viewing
     */
    public function createViewing(array $data);

    /**
     * Update an existing viewing
     */
    public function updateViewing(int $id, array $data);

    /**
     * Delete a viewing
     */
    public function deleteViewing(int $id);

    /**
     * Get viewings for a specific client
     */
    public function getClientViewings(int $clientId, array $filters = []);

    /**
     * Get viewings for a specific property
     */
    public function getPropertyViewings(int $propertyId, array $filters = []);

    /**
     * Get viewings for a specific agent
     */
    public function getAgentViewings(int $agentId, array $filters = []);

    /**
     * Add feedback for a viewing
     */
    public function addFeedback(int $viewingId, array $feedbackData);

    /**
     * Get feedback for a viewing
     */
    public function getViewingFeedback(int $viewingId);

    /**
     * Send notification for a viewing
     */
    public function sendNotification(int $viewingId, string $type, string $recipientType, int $recipientId);

    /**
     * Get notifications for a viewing
     */
    public function getViewingNotifications(int $viewingId);

    /**
     * Update viewing status
     */
    public function updateStatus(int $viewingId, string $status);

    /**
     * Get calendar events for viewings
     */
    public function getCalendarEvents(array $filters = []);

    /**
     * Check agent availability for viewing
     */
    public function checkAgentAvailability(int $agentId, string $datetime);

    /**
     * Reschedule a viewing
     */
    public function rescheduleViewing(int $viewingId, string $newDateTime);
} 