<?php

namespace App\Services;

use App\Interfaces\ViewingManagementInterface;
use PDO;
use Exception;

class ViewingManagementService implements ViewingManagementInterface
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllViewings(array $filters = [])
    {
        $sql = "SELECT v.*, 
                p.title as property_title, 
                CONCAT(c.first_name, ' ', c.last_name) as client_name,
                CONCAT(u.first_name, ' ', u.last_name) as agent_name
                FROM viewings v
                JOIN properties p ON v.property_id = p.id
                JOIN clients c ON v.client_id = c.id
                JOIN users u ON v.agent_id = u.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND v.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND v.scheduled_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND v.scheduled_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.title LIKE :search OR c.first_name LIKE :search OR c.last_name LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY v.scheduled_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getViewingById(int $id)
    {
        $sql = "SELECT v.*, 
                p.title as property_title, p.address as property_address,
                CONCAT(c.first_name, ' ', c.last_name) as client_name,
                c.email as client_email, c.phone as client_phone,
                CONCAT(u.first_name, ' ', u.last_name) as agent_name,
                u.email as agent_email, u.phone as agent_phone
                FROM viewings v
                JOIN properties p ON v.property_id = p.id
                JOIN clients c ON v.client_id = c.id
                JOIN users u ON v.agent_id = u.id
                WHERE v.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createViewing(array $data)
    {
        // Check agent availability
        if (!$this->checkAgentAvailability($data['agent_id'], $data['scheduled_at'])) {
            throw new Exception("Agent is not available at the specified time");
        }

        $sql = "INSERT INTO viewings (property_id, client_id, agent_id, scheduled_at, status) 
                VALUES (:property_id, :client_id, :agent_id, :scheduled_at, :status)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':property_id' => $data['property_id'],
            ':client_id' => $data['client_id'],
            ':agent_id' => $data['agent_id'],
            ':scheduled_at' => $data['scheduled_at'],
            ':status' => $data['status'] ?? 'scheduled'
        ]);

        $viewingId = $this->db->lastInsertId();

        // Send notifications
        $this->sendNotification($viewingId, 'scheduled', 'client', $data['client_id']);
        $this->sendNotification($viewingId, 'scheduled', 'agent', $data['agent_id']);

        return $viewingId;
    }

    public function updateViewing(int $id, array $data)
    {
        $updates = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['property_id', 'client_id', 'agent_id', 'scheduled_at', 'status'])) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE viewings SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteViewing(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM viewings WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getClientViewings(int $clientId, array $filters = [])
    {
        $sql = "SELECT v.*, 
                p.title as property_title,
                CONCAT(u.first_name, ' ', u.last_name) as agent_name
                FROM viewings v
                JOIN properties p ON v.property_id = p.id
                JOIN users u ON v.agent_id = u.id
                WHERE v.client_id = :client_id";

        $params = [':client_id' => $clientId];

        if (!empty($filters['status'])) {
            $sql .= " AND v.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY v.scheduled_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPropertyViewings(int $propertyId, array $filters = [])
    {
        $sql = "SELECT v.*, 
                CONCAT(c.first_name, ' ', c.last_name) as client_name,
                CONCAT(u.first_name, ' ', u.last_name) as agent_name
                FROM viewings v
                JOIN clients c ON v.client_id = c.id
                JOIN users u ON v.agent_id = u.id
                WHERE v.property_id = :property_id";

        $params = [':property_id' => $propertyId];

        if (!empty($filters['status'])) {
            $sql .= " AND v.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY v.scheduled_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAgentViewings(int $agentId, array $filters = [])
    {
        $sql = "SELECT v.*, 
                p.title as property_title,
                CONCAT(c.first_name, ' ', c.last_name) as client_name
                FROM viewings v
                JOIN properties p ON v.property_id = p.id
                JOIN clients c ON v.client_id = c.id
                WHERE v.agent_id = :agent_id";

        $params = [':agent_id' => $agentId];

        if (!empty($filters['status'])) {
            $sql .= " AND v.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND v.scheduled_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND v.scheduled_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY v.scheduled_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addFeedback(int $viewingId, array $feedbackData)
    {
        $sql = "INSERT INTO viewing_feedback 
                (viewing_id, property_condition, price_opinion, location_rating, 
                overall_impression, interested, comments)
                VALUES 
                (:viewing_id, :property_condition, :price_opinion, :location_rating,
                :overall_impression, :interested, :comments)";

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':viewing_id' => $viewingId,
            ':property_condition' => $feedbackData['property_condition'],
            ':price_opinion' => $feedbackData['price_opinion'],
            ':location_rating' => $feedbackData['location_rating'],
            ':overall_impression' => $feedbackData['overall_impression'],
            ':interested' => $feedbackData['interested'],
            ':comments' => $feedbackData['comments'] ?? null
        ]);

        if ($success) {
            // Update viewing feedback summary
            $avgRating = ($feedbackData['property_condition'] + 
                         $feedbackData['price_opinion'] + 
                         $feedbackData['location_rating'] + 
                         $feedbackData['overall_impression']) / 4;

            $this->db->prepare("UPDATE viewings SET feedback_rating = :rating WHERE id = :id")
                     ->execute([
                         ':rating' => $avgRating,
                         ':id' => $viewingId
                     ]);
        }

        return $success;
    }

    public function getViewingFeedback(int $viewingId)
    {
        $stmt = $this->db->prepare("SELECT * FROM viewing_feedback WHERE viewing_id = :viewing_id");
        $stmt->execute([':viewing_id' => $viewingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function sendNotification(int $viewingId, string $type, string $recipientType, int $recipientId)
    {
        $sql = "INSERT INTO viewing_notifications 
                (viewing_id, type, recipient_type, recipient_id, status)
                VALUES 
                (:viewing_id, :type, :recipient_type, :recipient_id, 'pending')";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':viewing_id' => $viewingId,
            ':type' => $type,
            ':recipient_type' => $recipientType,
            ':recipient_id' => $recipientId
        ]);
    }

    public function getViewingNotifications(int $viewingId)
    {
        $stmt = $this->db->prepare("SELECT * FROM viewing_notifications WHERE viewing_id = :viewing_id");
        $stmt->execute([':viewing_id' => $viewingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $viewingId, string $status)
    {
        $stmt = $this->db->prepare("UPDATE viewings SET status = :status WHERE id = :id");
        $success = $stmt->execute([
            ':id' => $viewingId,
            ':status' => $status
        ]);

        if ($success && $status === 'completed') {
            // Send feedback request notification
            $viewing = $this->getViewingById($viewingId);
            $this->sendNotification($viewingId, 'feedback_request', 'client', $viewing['client_id']);
        }

        return $success;
    }

    public function getCalendarEvents(array $filters = [])
    {
        $sql = "SELECT 
                v.id,
                v.scheduled_at as start,
                DATE_ADD(v.scheduled_at, INTERVAL 1 HOUR) as end,
                CONCAT(p.title, ' - ', c.first_name, ' ', c.last_name) as title,
                CASE 
                    WHEN v.status = 'scheduled' THEN 'primary'
                    WHEN v.status = 'completed' THEN 'success'
                    WHEN v.status = 'cancelled' THEN 'danger'
                    WHEN v.status = 'rescheduled' THEN 'warning'
                END as className
                FROM viewings v
                JOIN properties p ON v.property_id = p.id
                JOIN clients c ON v.client_id = c.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['agent_id'])) {
            $sql .= " AND v.agent_id = :agent_id";
            $params[':agent_id'] = $filters['agent_id'];
        }

        if (!empty($filters['start'])) {
            $sql .= " AND v.scheduled_at >= :start";
            $params[':start'] = $filters['start'];
        }

        if (!empty($filters['end'])) {
            $sql .= " AND v.scheduled_at <= :end";
            $params[':end'] = $filters['end'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkAgentAvailability(int $agentId, string $datetime)
    {
        // Check for existing viewings in ±1 hour window
        $sql = "SELECT COUNT(*) as count 
                FROM viewings 
                WHERE agent_id = :agent_id 
                AND scheduled_at BETWEEN 
                    DATE_SUB(:datetime, INTERVAL 1 HOUR) 
                    AND DATE_ADD(:datetime, INTERVAL 1 HOUR)
                AND status NOT IN ('cancelled')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':agent_id' => $agentId,
            ':datetime' => $datetime
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] === 0;
    }

    public function rescheduleViewing(int $viewingId, string $newDateTime)
    {
        $viewing = $this->getViewingById($viewingId);
        if (!$viewing) {
            return false;
        }

        // Check agent availability for new time
        if (!$this->checkAgentAvailability($viewing['agent_id'], $newDateTime)) {
            throw new Exception("Agent is not available at the new time");
        }

        $stmt = $this->db->prepare("UPDATE viewings SET scheduled_at = :datetime, status = 'rescheduled' WHERE id = :id");
        $success = $stmt->execute([
            ':id' => $viewingId,
            ':datetime' => $newDateTime
        ]);

        if ($success) {
            // Send rescheduled notifications
            $this->sendNotification($viewingId, 'rescheduled', 'client', $viewing['client_id']);
            $this->sendNotification($viewingId, 'rescheduled', 'agent', $viewing['agent_id']);
        }

        return $success;
    }
} 