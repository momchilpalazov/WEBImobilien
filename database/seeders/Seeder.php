<?php

require_once __DIR__ . '/../../config/database.php';
use App\Database;
use Faker\Factory;

abstract class Seeder {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    abstract public function run();
    
    protected function faker() {
        return Factory::create();
    }
} 