<?php

require_once __DIR__ . '/../../config/database.php';
use App\Database;

class Migration {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function migrate() {
        try {
            $sql = file_get_contents(__DIR__ . '/../structure.sql');
            $this->db->exec($sql);
            echo "Migration completed successfully!\n";
        } catch (PDOException $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
        }
    }
    
    public function rollback() {
        try {
            $tables = [
                'posts',
                'clients',
                'deals',
                'inquiries',
                'users',
                'property_documents',
                'property_images',
                'properties'
            ];
            
            foreach ($tables as $table) {
                $this->db->exec("DROP TABLE IF EXISTS $table");
            }
            
            echo "Rollback completed successfully!\n";
        } catch (PDOException $e) {
            echo "Rollback failed: " . $e->getMessage() . "\n";
        }
    }
} 