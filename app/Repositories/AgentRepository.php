<?php

namespace App\Repositories;

use App\Models\Agent;
use PDO;

class AgentRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM agents WHERE active = 1 ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_CLASS, Agent::class);
    }

    public function findById(int $id): ?Agent
    {
        $stmt = $this->db->prepare('SELECT * FROM agents WHERE id = ? AND active = 1');
        $stmt->execute([$id]);
        $result = $stmt->fetchObject(Agent::class);
        return $result ?: null;
    }
} 