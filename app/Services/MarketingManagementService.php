<?php

namespace App\Services;

use App\Interfaces\MarketingManagementInterface;
use PDO;
use Exception;

class MarketingManagementService implements MarketingManagementInterface
{
    private $db;
    private $uploadDir = 'uploads/marketing';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getPropertyMaterials(int $propertyId, array $filters = [])
    {
        $sql = "SELECT * FROM marketing_materials WHERE property_id = :property_id";
        $params = [':property_id' => $propertyId];

        if (!empty($filters['type'])) {
            $sql .= " AND type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY sort_order ASC, created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMaterialById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM marketing_materials WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function uploadMaterial(array $data, array $file)
    {
        // Validate file
        $allowedTypes = [
            'photo' => ['image/jpeg', 'image/png'],
            'video' => ['video/mp4', 'video/quicktime'],
            'brochure' => ['application/pdf'],
            'floor_plan' => ['image/jpeg', 'image/png', 'application/pdf'],
            'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        ];

        if (!isset($allowedTypes[$data['type']]) || !in_array($file['type'], $allowedTypes[$data['type']])) {
            throw new Exception("Invalid file type for {$data['type']}");
        }

        // Create upload directory if it doesn't exist
        $uploadPath = $this->uploadDir . '/' . $data['type'] . '/' . $data['property_id'];
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filePath = $uploadPath . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception("Failed to upload file");
        }

        // Insert into database
        $sql = "INSERT INTO marketing_materials 
                (property_id, type, title, description, file_path, file_size, file_type, status)
                VALUES 
                (:property_id, :type, :title, :description, :file_path, :file_size, :file_type, :status)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':property_id' => $data['property_id'],
            ':type' => $data['type'],
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':file_path' => $filePath,
            ':file_size' => $file['size'],
            ':file_type' => $file['type'],
            ':status' => $data['status'] ?? 'active'
        ]);

        return $this->db->lastInsertId();
    }

    public function updateMaterial(int $id, array $data)
    {
        $updates = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'description', 'status', 'sort_order', 'is_featured'])) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE marketing_materials SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteMaterial(int $id)
    {
        // Get file path before deleting
        $material = $this->getMaterialById($id);
        if ($material && file_exists($material['file_path'])) {
            unlink($material['file_path']);
        }

        $stmt = $this->db->prepare("DELETE FROM marketing_materials WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function updateSortOrder(array $sortData)
    {
        $sql = "UPDATE marketing_materials SET sort_order = :sort_order WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        foreach ($sortData as $item) {
            $stmt->execute([
                ':id' => $item['id'],
                ':sort_order' => $item['sort_order']
            ]);
        }

        return true;
    }

    public function toggleFeatured(int $id)
    {
        $sql = "UPDATE marketing_materials 
                SET is_featured = NOT is_featured 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getAllCampaigns(array $filters = [])
    {
        $sql = "SELECT c.*, 
                COUNT(DISTINCT cp.property_id) as property_count,
                COUNT(DISTINCT ch.id) as channel_count
                FROM marketing_campaigns c
                LEFT JOIN campaign_properties cp ON c.id = cp.campaign_id
                LEFT JOIN campaign_channels ch ON c.id = ch.campaign_id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND c.start_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND (c.end_date <= :date_to OR c.end_date IS NULL)";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= " GROUP BY c.id ORDER BY c.start_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCampaignById(int $id)
    {
        // Get campaign details
        $stmt = $this->db->prepare("SELECT * FROM marketing_campaigns WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$campaign) {
            return null;
        }

        // Get campaign properties
        $stmt = $this->db->prepare("
            SELECT p.* 
            FROM properties p
            JOIN campaign_properties cp ON p.id = cp.property_id
            WHERE cp.campaign_id = :campaign_id
        ");
        $stmt->execute([':campaign_id' => $id]);
        $campaign['properties'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get campaign channels
        $stmt = $this->db->prepare("SELECT * FROM campaign_channels WHERE campaign_id = :campaign_id");
        $stmt->execute([':campaign_id' => $id]);
        $campaign['channels'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $campaign;
    }

    public function createCampaign(array $data)
    {
        $this->db->beginTransaction();

        try {
            // Insert campaign
            $sql = "INSERT INTO marketing_campaigns 
                    (title, description, start_date, end_date, budget, status)
                    VALUES 
                    (:title, :description, :start_date, :end_date, :budget, :status)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'] ?? null,
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'] ?? null,
                ':budget' => $data['budget'] ?? null,
                ':status' => $data['status'] ?? 'draft'
            ]);

            $campaignId = $this->db->lastInsertId();

            // Add properties if provided
            if (!empty($data['property_ids'])) {
                $this->addCampaignProperties($campaignId, $data['property_ids']);
            }

            // Add channels if provided
            if (!empty($data['channels'])) {
                foreach ($data['channels'] as $channel) {
                    $this->addCampaignChannel($campaignId, $channel);
                }
            }

            $this->db->commit();
            return $campaignId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateCampaign(int $id, array $data)
    {
        $updates = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'description', 'start_date', 'end_date', 'budget', 'status'])) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE marketing_campaigns SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteCampaign(int $id)
    {
        $stmt = $this->db->prepare("DELETE FROM marketing_campaigns WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function addCampaignProperties(int $campaignId, array $propertyIds)
    {
        $sql = "INSERT INTO campaign_properties (campaign_id, property_id) VALUES (:campaign_id, :property_id)";
        $stmt = $this->db->prepare($sql);

        foreach ($propertyIds as $propertyId) {
            $stmt->execute([
                ':campaign_id' => $campaignId,
                ':property_id' => $propertyId
            ]);
        }

        return true;
    }

    public function removeCampaignProperties(int $campaignId, array $propertyIds)
    {
        $sql = "DELETE FROM campaign_properties 
                WHERE campaign_id = :campaign_id AND property_id = :property_id";
        $stmt = $this->db->prepare($sql);

        foreach ($propertyIds as $propertyId) {
            $stmt->execute([
                ':campaign_id' => $campaignId,
                ':property_id' => $propertyId
            ]);
        }

        return true;
    }

    public function addCampaignChannel(int $campaignId, array $channelData)
    {
        $sql = "INSERT INTO campaign_channels 
                (campaign_id, channel_type, channel_name, target_audience, 
                budget_allocation, start_date, end_date, metrics, status)
                VALUES 
                (:campaign_id, :channel_type, :channel_name, :target_audience,
                :budget_allocation, :start_date, :end_date, :metrics, :status)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':campaign_id' => $campaignId,
            ':channel_type' => $channelData['channel_type'],
            ':channel_name' => $channelData['channel_name'],
            ':target_audience' => $channelData['target_audience'] ?? null,
            ':budget_allocation' => $channelData['budget_allocation'] ?? null,
            ':start_date' => $channelData['start_date'] ?? null,
            ':end_date' => $channelData['end_date'] ?? null,
            ':metrics' => $channelData['metrics'] ?? null,
            ':status' => $channelData['status'] ?? 'planned'
        ]);

        return $this->db->lastInsertId();
    }

    public function updateCampaignChannel(int $channelId, array $channelData)
    {
        $updates = [];
        $params = [':id' => $channelId];

        foreach ($channelData as $key => $value) {
            if (in_array($key, ['channel_type', 'channel_name', 'target_audience', 
                              'budget_allocation', 'start_date', 'end_date', 
                              'metrics', 'status'])) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE campaign_channels SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteCampaignChannel(int $channelId)
    {
        $stmt = $this->db->prepare("DELETE FROM campaign_channels WHERE id = :id");
        return $stmt->execute([':id' => $channelId]);
    }

    public function recordAnalytics(array $data)
    {
        $sql = "INSERT INTO marketing_analytics 
                (material_id, campaign_id, channel_id, metric_type, metric_value, date_recorded)
                VALUES 
                (:material_id, :campaign_id, :channel_id, :metric_type, :metric_value, :date_recorded)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':material_id' => $data['material_id'] ?? null,
            ':campaign_id' => $data['campaign_id'] ?? null,
            ':channel_id' => $data['channel_id'] ?? null,
            ':metric_type' => $data['metric_type'],
            ':metric_value' => $data['metric_value'],
            ':date_recorded' => $data['date_recorded'] ?? date('Y-m-d')
        ]);
    }

    public function getAnalytics(string $type, int $id, array $filters = [])
    {
        $sql = "SELECT metric_type, 
                SUM(metric_value) as total_value,
                DATE(date_recorded) as date
                FROM marketing_analytics
                WHERE {$type}_id = :id";

        $params = [':id' => $id];

        if (!empty($filters['date_from'])) {
            $sql .= " AND date_recorded >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND date_recorded <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['metric_type'])) {
            $sql .= " AND metric_type = :metric_type";
            $params[':metric_type'] = $filters['metric_type'];
        }

        $sql .= " GROUP BY metric_type, DATE(date_recorded)
                  ORDER BY date_recorded ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generateCampaignReport(int $campaignId, array $filters = [])
    {
        // Get campaign details
        $campaign = $this->getCampaignById($campaignId);
        if (!$campaign) {
            return null;
        }

        // Get analytics for campaign and its channels
        $campaignAnalytics = $this->getAnalytics('campaign', $campaignId, $filters);
        
        $channelAnalytics = [];
        foreach ($campaign['channels'] as $channel) {
            $channelAnalytics[$channel['id']] = $this->getAnalytics('channel', $channel['id'], $filters);
        }

        // Calculate totals and conversion rates
        $totals = [
            'views' => 0,
            'clicks' => 0,
            'inquiries' => 0,
            'shares' => 0,
            'leads' => 0,
            'conversions' => 0
        ];

        foreach ($campaignAnalytics as $analytic) {
            $totals[$analytic['metric_type']] += $analytic['total_value'];
        }

        $conversionRates = [
            'click_through_rate' => $totals['views'] ? ($totals['clicks'] / $totals['views']) * 100 : 0,
            'inquiry_rate' => $totals['clicks'] ? ($totals['inquiries'] / $totals['clicks']) * 100 : 0,
            'lead_conversion_rate' => $totals['inquiries'] ? ($totals['leads'] / $totals['inquiries']) * 100 : 0,
            'final_conversion_rate' => $totals['leads'] ? ($totals['conversions'] / $totals['leads']) * 100 : 0
        ];

        return [
            'campaign' => $campaign,
            'analytics' => [
                'campaign' => $campaignAnalytics,
                'channels' => $channelAnalytics
            ],
            'totals' => $totals,
            'conversion_rates' => $conversionRates,
            'period' => [
                'from' => $filters['date_from'] ?? $campaign['start_date'],
                'to' => $filters['date_to'] ?? ($campaign['end_date'] ?? date('Y-m-d'))
            ]
        ];
    }
} 