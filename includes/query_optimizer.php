<?php

require_once __DIR__ . '/../config/database.php';
use App\Database;

class QueryOptimizer {
    private $db;
    private $cache;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->cache = new Cache();
    }

    public function getLatestProperties($limit = 6, $use_cache = true) {
        $cache_key = "latest_properties_{$limit}";
        
        if ($use_cache) {
            $cached = $this->cache->get($cache_key);
            if ($cached !== false) {
                return $cached;
            }
        }

        $sql = "
            SELECT p.*, pi.image_path 
            FROM properties p 
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1
            WHERE p.active = 1 
            ORDER BY p.created_at DESC 
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        $result = $stmt->fetchAll();
        
        if ($use_cache) {
            $this->cache->set($cache_key, $result);
        }

        return $result;
    }

    public function getPropertyWithDetails($id, $use_cache = true) {
        $cache_key = "property_details_{$id}";
        
        if ($use_cache) {
            $cached = $this->cache->get($cache_key);
            if ($cached !== false) {
                return $cached;
            }
        }

        $sql = "
            SELECT p.*, 
                GROUP_CONCAT(DISTINCT pi.image_path) as images,
                COUNT(DISTINCT i.id) as inquiry_count
            FROM properties p 
            LEFT JOIN property_images pi ON p.id = pi.property_id
            LEFT JOIN inquiries i ON p.id = i.property_id
            WHERE p.id = ?
            GROUP BY p.id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($use_cache) {
            $this->cache->set($cache_key, $result);
        }

        return $result;
    }
} 