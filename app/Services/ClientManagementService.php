<?php

namespace App\Services;

use App\Interfaces\ClientManagementInterface;
use PDO;
use Exception;

class ClientManagementService implements ClientManagementInterface
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllClients(array $filters = [])
    {
        $sql = "SELECT * FROM clients WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClientById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createClient(array $data)
    {
        $sql = "INSERT INTO clients (first_name, last_name, email, phone, status, source) 
                VALUES (:first_name, :last_name, :email, :phone, :status, :source)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':status' => $data['status'] ?? 'potential',
            ':source' => $data['source'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    public function updateClient(int $id, array $data)
    {
        $updates = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['first_name', 'last_name', 'email', 'phone', 'status', 'source'])) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE clients SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteClient(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM clients WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getClientPreferences(int $clientId)
    {
        $stmt = $this->db->prepare("SELECT * FROM client_preferences WHERE client_id = :client_id");
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateClientPreferences(int $clientId, array $preferences)
    {
        // First, check if preferences exist
        $stmt = $this->db->prepare("SELECT id FROM client_preferences WHERE client_id = :client_id");
        $stmt->execute([':client_id' => $clientId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing preferences
            $updates = [];
            $params = [':client_id' => $clientId];

            foreach ($preferences as $key => $value) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $value;
            }

            $sql = "UPDATE client_preferences SET " . implode(', ', $updates) . " WHERE client_id = :client_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } else {
            // Insert new preferences
            $preferences['client_id'] = $clientId;
            $columns = implode(', ', array_keys($preferences));
            $values = ':' . implode(', :', array_keys($preferences));
            
            $sql = "INSERT INTO client_preferences ($columns) VALUES ($values)";
            $stmt = $this->db->prepare($sql);
            
            foreach ($preferences as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            return $stmt->execute();
        }
    }

    public function addInteraction(int $clientId, array $interactionData)
    {
        $sql = "INSERT INTO client_interactions (client_id, interaction_type, description, agent_id, 
                property_id, scheduled_at, status, notes) 
                VALUES (:client_id, :interaction_type, :description, :agent_id, 
                :property_id, :scheduled_at, :status, :notes)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':client_id' => $clientId,
            ':interaction_type' => $interactionData['interaction_type'],
            ':description' => $interactionData['description'] ?? null,
            ':agent_id' => $interactionData['agent_id'] ?? null,
            ':property_id' => $interactionData['property_id'] ?? null,
            ':scheduled_at' => $interactionData['scheduled_at'] ?? null,
            ':status' => $interactionData['status'] ?? 'planned',
            ':notes' => $interactionData['notes'] ?? null
        ]);
    }

    public function getInteractions(int $clientId, array $filters = [])
    {
        $sql = "SELECT i.*, u.name as agent_name, p.title as property_title 
                FROM client_interactions i 
                LEFT JOIN users u ON i.agent_id = u.id 
                LEFT JOIN properties p ON i.property_id = p.id 
                WHERE i.client_id = :client_id";
        
        $params = [':client_id' => $clientId];

        if (!empty($filters['type'])) {
            $sql .= " AND i.interaction_type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND i.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY i.scheduled_at DESC, i.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPropertyMatches(int $clientId, array $filters = [])
    {
        $sql = "SELECT m.*, p.title, p.price, p.location 
                FROM client_property_matches m 
                JOIN properties p ON m.property_id = p.id 
                WHERE m.client_id = :client_id";
        
        $params = [':client_id' => $clientId];

        if (!empty($filters['status'])) {
            $sql .= " AND m.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY m.match_score DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateMatchStatus(int $matchId, string $status)
    {
        $stmt = $this->db->prepare("UPDATE client_property_matches SET status = :status WHERE id = :id");
        return $stmt->execute([
            ':id' => $matchId,
            ':status' => $status
        ]);
    }

    public function calculateMatches(int $clientId)
    {
        // Get client preferences
        $preferences = $this->getClientPreferences($clientId);
        if (!$preferences) {
            return false;
        }

        // Build query based on preferences
        $sql = "SELECT p.*, 
                (CASE 
                    WHEN p.price BETWEEN :min_price AND :max_price THEN 20 ELSE 0 END +
                    CASE WHEN p.area BETWEEN :min_area AND :max_area THEN 20 ELSE 0 END +
                    CASE WHEN p.property_type = :property_type THEN 30 ELSE 0 END +
                    CASE WHEN p.location LIKE :location THEN 30 ELSE 0 END
                ) as match_score
                FROM properties p
                WHERE p.status = 'active'
                HAVING match_score > 30
                ORDER BY match_score DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':min_price' => $preferences['min_price'],
            ':max_price' => $preferences['max_price'],
            ':min_area' => $preferences['min_area'],
            ':max_area' => $preferences['max_area'],
            ':property_type' => $preferences['property_type'],
            ':location' => "%{$preferences['location']}%"
        ]);

        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Store matches
        foreach ($matches as $match) {
            $this->db->prepare("INSERT INTO client_property_matches 
                               (client_id, property_id, match_score) 
                               VALUES (:client_id, :property_id, :match_score)
                               ON DUPLICATE KEY UPDATE match_score = :match_score")
                     ->execute([
                         ':client_id' => $clientId,
                         ':property_id' => $match['id'],
                         ':match_score' => $match['match_score']
                     ]);
        }

        return true;
    }

    public function linkDocument(int $clientId, int $documentId)
    {
        $stmt = $this->db->prepare("INSERT INTO client_documents (client_id, document_id) VALUES (:client_id, :document_id)");
        return $stmt->execute([
            ':client_id' => $clientId,
            ':document_id' => $documentId
        ]);
    }

    public function getClientDocuments(int $clientId)
    {
        $sql = "SELECT d.* 
                FROM documents d 
                JOIN client_documents cd ON d.id = cd.document_id 
                WHERE cd.client_id = :client_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':client_id' => $clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 