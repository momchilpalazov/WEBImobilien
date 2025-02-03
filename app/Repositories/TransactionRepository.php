<?php

namespace App\Repositories;

use PDO;
use App\Models\Transaction;
use App\Interfaces\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?Transaction
    {
        $stmt = $this->db->prepare('
            SELECT t.*, 
                   p.title as property_title,
                   CONCAT(c.first_name, " ", c.last_name) as client_name,
                   CONCAT(a.first_name, " ", a.last_name) as agent_name,
                   CONCAT(u.first_name, " ", u.last_name) as creator_name
            FROM transactions t
            LEFT JOIN properties p ON t.property_id = p.id
            LEFT JOIN clients c ON t.client_id = c.id
            LEFT JOIN users a ON t.agent_id = a.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.id = ?
        ');
        $stmt->execute([$id]);
        
        $transaction = $stmt->fetchObject(Transaction::class);
        return $transaction ?: null;
    }

    public function findAll(array $filters = [], array $sorting = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = 't.type = ?';
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where[] = 't.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['agent_id'])) {
            $where[] = 't.agent_id = ?';
            $params[] = $filters['agent_id'];
        }

        if (!empty($filters['start_date'])) {
            $where[] = 't.transaction_date >= ?';
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = 't.transaction_date <= ?';
            $params[] = $filters['end_date'];
        }

        $orderBy = !empty($sorting['field']) ? $sorting['field'] : 'transaction_date';
        $order = !empty($sorting['direction']) ? $sorting['direction'] : 'DESC';
        
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare('
            SELECT t.*, 
                   p.title as property_title,
                   CONCAT(c.first_name, " ", c.last_name) as client_name,
                   CONCAT(a.first_name, " ", a.last_name) as agent_name,
                   CONCAT(u.first_name, " ", u.last_name) as creator_name
            FROM transactions t
            LEFT JOIN properties p ON t.property_id = p.id
            LEFT JOIN clients c ON t.client_id = c.id
            LEFT JOIN users a ON t.agent_id = a.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY ' . $orderBy . ' ' . $order . '
            LIMIT ? OFFSET ?
        ');

        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Transaction::class);
    }

    public function create(Transaction $transaction): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO transactions (
                type, property_id, client_id, agent_id, amount, currency,
                commission_rate, commission_amount, status, description,
                transaction_date, due_date, payment_method, reference_number,
                created_by
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ');

        $stmt->execute([
            $transaction->type,
            $transaction->property_id,
            $transaction->client_id,
            $transaction->agent_id,
            $transaction->amount,
            $transaction->currency,
            $transaction->commission_rate,
            $transaction->commission_amount,
            $transaction->status,
            $transaction->description,
            $transaction->transaction_date,
            $transaction->due_date,
            $transaction->payment_method,
            $transaction->reference_number,
            $transaction->created_by
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(Transaction $transaction): bool
    {
        $stmt = $this->db->prepare('
            UPDATE transactions SET
                type = ?,
                property_id = ?,
                client_id = ?,
                agent_id = ?,
                amount = ?,
                currency = ?,
                commission_rate = ?,
                commission_amount = ?,
                status = ?,
                description = ?,
                transaction_date = ?,
                due_date = ?,
                payment_method = ?,
                reference_number = ?
            WHERE id = ?
        ');

        return $stmt->execute([
            $transaction->type,
            $transaction->property_id,
            $transaction->client_id,
            $transaction->agent_id,
            $transaction->amount,
            $transaction->currency,
            $transaction->commission_rate,
            $transaction->commission_amount,
            $transaction->status,
            $transaction->description,
            $transaction->transaction_date,
            $transaction->due_date,
            $transaction->payment_method,
            $transaction->reference_number,
            $transaction->id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM transactions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getTotalsByPeriod(string $startDate, string $endDate, string $type = null): array
    {
        $where = ['transaction_date BETWEEN ? AND ?'];
        $params = [$startDate, $endDate];

        if ($type) {
            $where[] = 'type = ?';
            $params[] = $type;
        }

        $stmt = $this->db->prepare('
            SELECT 
                COUNT(*) as total_count,
                SUM(amount) as total_amount,
                SUM(commission_amount) as total_commission,
                AVG(commission_rate) as avg_commission_rate
            FROM transactions
            WHERE ' . implode(' AND ', $where) . '
            AND status = "completed"
        ');

        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAgentPerformance(int $agentId, string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                type,
                COUNT(*) as transaction_count,
                SUM(amount) as total_amount,
                SUM(commission_amount) as total_commission,
                AVG(commission_rate) as avg_commission_rate
            FROM transactions
            WHERE agent_id = ?
            AND transaction_date BETWEEN ? AND ?
            AND status = "completed"
            GROUP BY type
        ');

        $stmt->execute([$agentId, $startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 