<?php

namespace App\Services;

use App\Interfaces\DocumentManagementInterface;
use PDO;
use Exception;

class DocumentManagementService implements DocumentManagementInterface
{
    private $db;
    private $uploadDir = 'uploads/documents';
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllDocuments(array $filters = [])
    {
        $sql = "SELECT d.*, u.name as created_by_name 
                FROM documents d
                JOIN users u ON d.created_by = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND d.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND d.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (d.title LIKE :search OR d.description LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND d.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND d.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY d.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDocumentById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT d.*, u.name as created_by_name 
            FROM documents d
            JOIN users u ON d.created_by = u.id
            WHERE d.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRelatedDocuments(string $relationType, int $relationId)
    {
        $sql = "SELECT d.*, u.name as created_by_name 
                FROM documents d
                JOIN document_relations dr ON d.id = dr.document_id
                JOIN users u ON d.created_by = u.id
                WHERE dr.relation_type = :relation_type 
                AND dr.relation_id = :relation_id
                ORDER BY d.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':relation_type' => $relationType,
            ':relation_id' => $relationId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function uploadDocument(array $data, array $file)
    {
        // Validate file type
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png'
        ];

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type');
        }

        // Create upload directory if it doesn't exist
        $uploadPath = $this->uploadDir . '/' . date('Y/m');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filePath = $uploadPath . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to upload file');
        }

        $this->db->beginTransaction();

        try {
            // Insert document
            $sql = "INSERT INTO documents 
                    (title, description, file_path, file_size, file_type, category, status, created_by)
                    VALUES 
                    (:title, :description, :file_path, :file_size, :file_type, :category, :status, :created_by)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'] ?? null,
                ':file_path' => $filePath,
                ':file_size' => $file['size'],
                ':file_type' => $file['type'],
                ':category' => $data['category'],
                ':status' => $data['status'] ?? 'active',
                ':created_by' => $data['created_by']
            ]);
            
            $documentId = $this->db->lastInsertId();

            // Add relations if provided
            if (!empty($data['relations'])) {
                $this->addDocumentRelations($documentId, $data['relations']);
            }

            // Add signatures if provided
            if (!empty($data['signatures'])) {
                foreach ($data['signatures'] as $signer) {
                    $this->addSignatureRequest($documentId, $signer);
                }
            }

            $this->db->commit();
            return $documentId;
        } catch (Exception $e) {
            $this->db->rollBack();
            unlink($filePath);
            throw $e;
        }
    }

    public function updateDocument(int $id, array $data)
    {
        $updates = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'description', 'category', 'status'])) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE documents SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteDocument(int $id)
    {
        // Get document details
        $document = $this->getDocumentById($id);
        if (!$document) {
            return false;
        }

        $this->db->beginTransaction();

        try {
            // Delete file
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }

            // Delete versions
            $versions = $this->getDocumentVersions($id);
            foreach ($versions as $version) {
                if (file_exists($version['file_path'])) {
                    unlink($version['file_path']);
                }
            }

            // Delete document and related records (relations, signatures, etc. will be deleted by foreign key constraints)
            $stmt = $this->db->prepare("DELETE FROM documents WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function createVersion(int $documentId, array $data, array $file)
    {
        // Get current version number
        $stmt = $this->db->prepare("
            SELECT MAX(version_number) as max_version 
            FROM document_versions 
            WHERE document_id = :document_id
        ");
        $stmt->execute([':document_id' => $documentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nextVersion = ($result['max_version'] ?? 0) + 1;

        // Upload new version file
        $uploadPath = $this->uploadDir . '/versions/' . date('Y/m');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filePath = $uploadPath . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to upload file');
        }

        // Insert version record
        $sql = "INSERT INTO document_versions 
                (document_id, version_number, file_path, file_size, changes_description, created_by)
                VALUES 
                (:document_id, :version_number, :file_path, :file_size, :changes_description, :created_by)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':document_id' => $documentId,
            ':version_number' => $nextVersion,
            ':file_path' => $filePath,
            ':file_size' => $file['size'],
            ':changes_description' => $data['changes_description'] ?? null,
            ':created_by' => $data['created_by']
        ]);
    }

    public function getDocumentVersions(int $documentId)
    {
        $sql = "SELECT v.*, u.name as created_by_name 
                FROM document_versions v
                JOIN users u ON v.created_by = u.id
                WHERE v.document_id = :document_id
                ORDER BY v.version_number DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addDocumentRelations(int $documentId, array $relations)
    {
        $sql = "INSERT INTO document_relations (document_id, relation_type, relation_id)
                VALUES (:document_id, :relation_type, :relation_id)";
        $stmt = $this->db->prepare($sql);

        foreach ($relations as $relation) {
            $stmt->execute([
                ':document_id' => $documentId,
                ':relation_type' => $relation['type'],
                ':relation_id' => $relation['id']
            ]);
        }

        return true;
    }

    public function removeDocumentRelations(int $documentId, array $relations)
    {
        $sql = "DELETE FROM document_relations 
                WHERE document_id = :document_id 
                AND relation_type = :relation_type 
                AND relation_id = :relation_id";
        $stmt = $this->db->prepare($sql);

        foreach ($relations as $relation) {
            $stmt->execute([
                ':document_id' => $documentId,
                ':relation_type' => $relation['type'],
                ':relation_id' => $relation['id']
            ]);
        }

        return true;
    }

    public function addSignatureRequest(int $documentId, array $signerData)
    {
        $sql = "INSERT INTO document_signatures 
                (document_id, signer_type, signer_id, signer_name, signer_email, expiration_date)
                VALUES 
                (:document_id, :signer_type, :signer_id, :signer_name, :signer_email, :expiration_date)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':document_id' => $documentId,
            ':signer_type' => $signerData['type'],
            ':signer_id' => $signerData['id'] ?? null,
            ':signer_name' => $signerData['name'],
            ':signer_email' => $signerData['email'] ?? null,
            ':expiration_date' => $signerData['expiration_date'] ?? null
        ]);
    }

    public function updateSignatureStatus(int $signatureId, string $status, array $data = [])
    {
        $updates = ['signature_status = :status'];
        $params = [
            ':id' => $signatureId,
            ':status' => $status
        ];

        if ($status === 'signed') {
            $updates[] = 'signature_date = CURRENT_TIMESTAMP';
            $updates[] = 'signature_ip = :ip';
            $updates[] = 'signature_data = :data';
            $params[':ip'] = $data['ip'] ?? null;
            $params[':data'] = $data['signature_data'] ?? null;
        }

        $sql = "UPDATE document_signatures 
                SET " . implode(', ', $updates) . "
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function getDocumentSignatures(int $documentId)
    {
        $sql = "SELECT * FROM document_signatures WHERE document_id = :document_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function logAccess(int $documentId, int $userId, string $actionType, array $details = [])
    {
        $sql = "INSERT INTO document_access_logs 
                (document_id, user_id, action_type, action_details, ip_address, user_agent)
                VALUES 
                (:document_id, :user_id, :action_type, :action_details, :ip_address, :user_agent)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':document_id' => $documentId,
            ':user_id' => $userId,
            ':action_type' => $actionType,
            ':action_details' => json_encode($details),
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    public function getAccessLogs(int $documentId)
    {
        $sql = "SELECT l.*, u.name as user_name 
                FROM document_access_logs l
                JOIN users u ON l.user_id = u.id
                WHERE l.document_id = :document_id
                ORDER BY l.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTemplates(array $filters = [])
    {
        $sql = "SELECT t.*, u.name as created_by_name 
                FROM document_templates t
                JOIN users u ON t.created_by = u.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['category'])) {
            $sql .= " AND t.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND t.is_active = :is_active";
            $params[':is_active'] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (t.title LIKE :search OR t.description LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY t.title";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        
    public function getTemplateById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as created_by_name 
            FROM document_templates t
            JOIN users u ON t.created_by = u.id
            WHERE t.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createTemplate(array $data)
    {
        $sql = "INSERT INTO document_templates 
                (title, description, category, content, variables, created_by)
                VALUES 
                (:title, :description, :category, :content, :variables, :created_by)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':category' => $data['category'],
            ':content' => $data['content'],
            ':variables' => json_encode($data['variables'] ?? null),
            ':created_by' => $data['created_by']
        ]);

        return $this->db->lastInsertId();
    }

    public function updateTemplate(int $id, array $data)
    {
        $updates = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'description', 'category', 'content', 'variables', 'is_active'])) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $key === 'variables' ? json_encode($value) : $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE document_templates 
                SET " . implode(', ', $updates) . "
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteTemplate(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM document_templates WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function generateFromTemplate(int $templateId, array $variables)
    {
        $template = $this->getTemplateById($templateId);
        if (!$template) {
            throw new Exception('Template not found');
        }

        $content = $template['content'];

        // Replace variables in content
        foreach ($variables as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }

        return $content;
    }

    public function shareDocument(int $documentId, array $shareData)
    {
        $accessToken = bin2hex(random_bytes(32));

        $sql = "INSERT INTO document_shares 
                (document_id, shared_by, shared_with_email, access_token, permissions, expiration_date)
                VALUES 
                (:document_id, :shared_by, :shared_with_email, :access_token, :permissions, :expiration_date)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':document_id' => $documentId,
            ':shared_by' => $shareData['shared_by'],
            ':shared_with_email' => $shareData['email'],
            ':access_token' => $accessToken,
            ':permissions' => json_encode($shareData['permissions'] ?? null),
            ':expiration_date' => $shareData['expiration_date'] ?? null
        ]);

        return $accessToken;
    }

    public function updateShare(int $shareId, array $data)
    {
        $updates = [];
        $params = [':id' => $shareId];

        foreach ($data as $key => $value) {
            if (in_array($key, ['permissions', 'expiration_date', 'is_active'])) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $key === 'permissions' ? json_encode($value) : $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE document_shares 
                SET " . implode(', ', $updates) . "
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function revokeShare(int $shareId)
    {
        $sql = "UPDATE document_shares SET is_active = FALSE WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $shareId]);
    }

    public function getDocumentShares(int $documentId)
    {
        $sql = "SELECT s.*, u.name as shared_by_name 
                FROM document_shares s
                JOIN users u ON s.shared_by = u.id
                WHERE s.document_id = :document_id
                ORDER BY s.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validateShareAccess(string $accessToken)
    {
        $sql = "SELECT s.*, d.* 
                FROM document_shares s
                JOIN documents d ON s.document_id = d.id
                WHERE s.access_token = :access_token
                AND s.is_active = TRUE
                AND (s.expiration_date IS NULL OR s.expiration_date > CURRENT_TIMESTAMP)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':access_token' => $accessToken]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
} 