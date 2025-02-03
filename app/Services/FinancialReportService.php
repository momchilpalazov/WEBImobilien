<?php

namespace App\Services;

use App\Interfaces\FinancialReportInterface;
use PDO;
use DateTime;
use Exception;
use App\Interfaces\CacheInterface;
use InvalidArgumentException;
use TCPDF;

class FinancialReportService implements FinancialReportInterface
{
    private PDO $db;
    private CacheInterface $cache;
    private const CACHE_TTL = 3600; // 1 hour cache
    private const PDF_PAGE_ORIENTATION = 'P';
    private const PDF_UNIT = 'mm';
    private const PDF_PAGE_FORMAT = 'A4';
    private const PDF_CREATOR = 'Imobilien Platform';
    
    public function __construct(PDO $db, CacheInterface $cache)
    {
        $this->db = $db;
        $this->cache = $cache;
    }
    
    public function getRevenueReport(array $filters): array
    {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $where[] = "t.transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "t.transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        if (!empty($filters['agent_id'])) {
            $where[] = "t.agent_id = :agent_id";
            $params['agent_id'] = $filters['agent_id'];
        }
        if (!empty($filters['property_type'])) {
            $where[] = "p.type = :property_type";
            $params['property_type'] = $filters['property_type'];
        }
        
        // Общи приходи
        $sql = "
            SELECT 
                SUM(CASE WHEN t.type = 'sale' THEN t.amount ELSE 0 END) as sales_revenue,
                SUM(CASE WHEN t.type = 'rent' THEN t.amount ELSE 0 END) as rental_revenue,
                SUM(t.commission_amount) as total_commission,
                COUNT(DISTINCT CASE WHEN t.type = 'sale' THEN t.id END) as sales_count,
                COUNT(DISTINCT CASE WHEN t.type = 'rent' THEN t.id END) as rentals_count
            FROM transactions t
            LEFT JOIN properties p ON p.id = t.property_id
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $totals = $stmt->fetch();
        
        // Приходи по месеци
        $sql = "
            SELECT 
                DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
                SUM(t.amount) as revenue,
                SUM(t.commission_amount) as commission,
                COUNT(DISTINCT t.id) as transactions
            FROM transactions t
            LEFT JOIN properties p ON p.id = t.property_id
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
            GROUP BY month
            ORDER BY month DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $byMonth = $stmt->fetchAll();
        
        // Приходи по тип имот
        $sql = "
            SELECT 
                p.type as property_type,
                SUM(t.amount) as revenue,
                SUM(t.commission_amount) as commission,
                COUNT(DISTINCT t.id) as transactions
            FROM transactions t
            LEFT JOIN properties p ON p.id = t.property_id
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
            GROUP BY p.type
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $byPropertyType = $stmt->fetchAll();
        
        // Приходи по агент
        $sql = "
            SELECT 
                u.id as agent_id,
                u.name as agent_name,
                SUM(t.amount) as revenue,
                SUM(t.commission_amount) as commission,
                COUNT(DISTINCT t.id) as transactions
            FROM transactions t
            LEFT JOIN properties p ON p.id = t.property_id
            LEFT JOIN users u ON u.id = t.agent_id
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
            GROUP BY u.id, u.name
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $byAgent = $stmt->fetchAll();
        
        return [
            'totals' => $totals,
            'by_month' => $byMonth,
            'by_property_type' => $byPropertyType,
            'by_agent' => $byAgent
        ];
    }
    
    public function getCommissionReport(array $filters): array
    {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $where[] = "t.transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "t.transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        if (!empty($filters['agent_id'])) {
            $where[] = "t.agent_id = :agent_id";
            $params['agent_id'] = $filters['agent_id'];
        }
        
        // Общи комисионни
        $sql = "
            SELECT 
                SUM(t.commission_amount) as total_commission,
                AVG(t.commission_rate) as avg_commission_rate,
                COUNT(DISTINCT t.id) as transactions_count
            FROM transactions t
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $totals = $stmt->fetch();
        
        // Комисионни по месеци
        $sql = "
            SELECT 
                DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
                SUM(t.commission_amount) as commission,
                AVG(t.commission_rate) as avg_rate,
                COUNT(DISTINCT t.id) as transactions
            FROM transactions t
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
            GROUP BY month
            ORDER BY month DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $byMonth = $stmt->fetchAll();
        
        // Комисионни по агент
        $sql = "
            SELECT 
                u.id as agent_id,
                u.name as agent_name,
                SUM(t.commission_amount) as commission,
                AVG(t.commission_rate) as avg_rate,
                COUNT(DISTINCT t.id) as transactions
            FROM transactions t
            LEFT JOIN users u ON u.id = t.agent_id
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
            GROUP BY u.id, u.name
            ORDER BY commission DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $byAgent = $stmt->fetchAll();
        
        return [
            'totals' => $totals,
            'by_month' => $byMonth,
            'by_agent' => $byAgent
        ];
    }
    
    public function getAgentPerformance(int $agentId, array $filters): array
    {
        $where = ['t.agent_id = :agent_id'];
        $params = ['agent_id' => $agentId];
        
        if (!empty($filters['start_date'])) {
            $where[] = "t.transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "t.transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        // Общи показатели
        $sql = "
            SELECT 
                COUNT(DISTINCT t.id) as total_transactions,
                SUM(t.amount) as total_revenue,
                SUM(t.commission_amount) as total_commission,
                AVG(t.commission_rate) as avg_commission_rate,
                COUNT(DISTINCT CASE WHEN t.type = 'sale' THEN t.id END) as sales_count,
                COUNT(DISTINCT CASE WHEN t.type = 'rent' THEN t.id END) as rentals_count
            FROM transactions t
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $performance = $stmt->fetch();
        
        // Цели
        $sql = "
            SELECT 
                type,
                target_amount,
                achieved_amount,
                (achieved_amount / target_amount * 100) as achievement_rate,
                status
            FROM financial_goals
            WHERE agent_id = :agent_id
            AND period_start <= CURRENT_DATE
            AND period_end >= CURRENT_DATE
            AND status = 'active'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['agent_id' => $agentId]);
        $goals = $stmt->fetchAll();
        
        // История по месеци
        $sql = "
            SELECT 
                DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
                COUNT(DISTINCT t.id) as transactions,
                SUM(t.amount) as revenue,
                SUM(t.commission_amount) as commission
            FROM transactions t
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
            GROUP BY month
            ORDER BY month DESC
            LIMIT 12
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $history = $stmt->fetchAll();
        
        return [
            'performance' => $performance,
            'goals' => $goals,
            'history' => $history
        ];
    }
    
    public function getPropertyTypePerformance(array $filters): array
    {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $where[] = "t.transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "t.transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        if (!empty($filters['location'])) {
            $where[] = "p.location LIKE :location";
            $params['location'] = "%{$filters['location']}%";
        }
        
        // Показатели по тип имот
        $sql = "
            SELECT 
                p.type as property_type,
                COUNT(DISTINCT t.id) as transactions,
                SUM(t.amount) as revenue,
                AVG(t.amount) as avg_price,
                SUM(t.commission_amount) as commission,
                AVG(t.commission_rate) as avg_commission_rate
            FROM transactions t
            LEFT JOIN properties p ON p.id = t.property_id
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
            GROUP BY p.type
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $byType = $stmt->fetchAll();
        
        // Тенденции по месеци
        $sql = "
            SELECT 
                DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
                p.type as property_type,
                COUNT(DISTINCT t.id) as transactions,
                SUM(t.amount) as revenue,
                AVG(t.amount) as avg_price
            FROM transactions t
            LEFT JOIN properties p ON p.id = t.property_id
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
            GROUP BY month, p.type
            ORDER BY month DESC, p.type
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $trends = $stmt->fetchAll();
        
        return [
            'by_type' => $byType,
            'trends' => $trends
        ];
    }
    
    public function getLocationPerformance(array $filters): array
    {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $where[] = "t.transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "t.transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        if (!empty($filters['property_type'])) {
            $where[] = "p.type = :property_type";
            $params['property_type'] = $filters['property_type'];
        }
        
        // Показатели по локация
        $sql = "
            SELECT 
                p.location,
                COUNT(DISTINCT t.id) as transactions,
                SUM(t.amount) as revenue,
                AVG(t.amount) as avg_price,
                SUM(t.commission_amount) as commission,
                AVG(t.commission_rate) as avg_commission_rate
            FROM transactions t
            LEFT JOIN properties p ON p.id = t.property_id
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
            GROUP BY p.location
            ORDER BY revenue DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $byLocation = $stmt->fetchAll();
        
        // Тенденции по месеци
        $sql = "
            SELECT 
                DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
                p.location,
                COUNT(DISTINCT t.id) as transactions,
                SUM(t.amount) as revenue,
                AVG(t.amount) as avg_price
            FROM transactions t
            LEFT JOIN properties p ON p.id = t.property_id
            WHERE " . implode(" AND ", $where) . "
            AND t.status = 'completed'
            GROUP BY month, p.location
            ORDER BY month DESC, revenue DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $trends = $stmt->fetchAll();
        
        return [
            'by_location' => $byLocation,
            'trends' => $trends
        ];
    }
    
    public function getTransactionHistory(array $filters, array $sorting = [], int $page = 1, int $perPage = 20): array
    {
        $cacheKey = 'transaction_history_' . md5(serialize([$filters, $sorting, $page, $perPage]));
        
        // Try to get from cache first
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // Build query
        $query = "SELECT t.*, p.title, a.name as agent_name 
                 FROM transactions t
                 LEFT JOIN properties p ON t.property_id = p.id
                 LEFT JOIN agents a ON t.agent_id = a.id
                 WHERE 1=1";
                 
        $params = [];
        
        // Add filters
        if (!empty($filters['start_date'])) {
            $query .= " AND t.transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " AND t.transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['type'])) {
            $query .= " AND t.type = :type";
            $params['type'] = $filters['type'];
        }
        
        if (!empty($filters['agent_id'])) {
            $query .= " AND t.agent_id = :agent_id";
            $params['agent_id'] = $filters['agent_id'];
        }
        
        // Add sorting
        if (!empty($sorting)) {
            $query .= " ORDER BY " . $sorting['field'] . " " . $sorting['direction'];
        } else {
            $query .= " ORDER BY t.transaction_date DESC";
        }
        
        // Add pagination
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT :offset, :limit";
        $params['offset'] = $offset;
        $params['limit'] = $perPage;
        
        // Execute query
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $transactions = $stmt->fetchAll();
        
        // Get total count for pagination
        $countQuery = str_replace("SELECT t.*, p.title, a.name as agent_name", "SELECT COUNT(*)", 
                                substr($query, 0, strpos($query, " LIMIT")));
        $stmt = $this->db->prepare($countQuery);
        foreach ($params as $key => $value) {
            if ($key !== 'offset' && $key !== 'limit') {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        $result = [
            'data' => $transactions,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
        
        // Cache the results
        $this->cache->set($cacheKey, $result, self::CACHE_TTL);
        
        return $result;
    }
    
    public function getFinancialMetrics(array $filters): array
    {
        $cacheKey = 'financial_metrics_' . md5(serialize($filters));
        
        // Try to get from cache first
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // If not in cache, calculate metrics
        $metrics = [
            'total_revenue' => $this->calculateTotalRevenue($filters),
            'total_commission' => $this->calculateTotalCommission($filters),
            'average_transaction_value' => $this->calculateAverageTransactionValue($filters),
            'total_expenses' => $this->calculateTotalExpenses($filters),
            'profit_margin' => $this->calculateProfitMargin($filters),
            'year_over_year_growth' => $this->calculateYearOverYearGrowth($filters)
        ];
        
        // Cache the results
        $this->cache->set($cacheKey, $metrics, self::CACHE_TTL);
        
        return $metrics;
    }
    
    public function getForecastReport(array $filters): array
    {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $where[] = "f.period_start >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "f.period_end <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        // Прогнози
        $sql = "
            SELECT 
                f.*,
                u.name as agent_name
            FROM financial_forecasts f
            LEFT JOIN users u ON u.id = f.agent_id
            WHERE " . implode(" AND ", $where) . "
            ORDER BY f.period_start ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $forecasts = $stmt->fetchAll();
        
        // Изчисляване на точност на прогнозите
        foreach ($forecasts as &$forecast) {
            if ($forecast['actual_amount'] !== null) {
                $forecast['accuracy'] = 100 - abs(
                    ($forecast['actual_amount'] - $forecast['forecast_amount']) / $forecast['forecast_amount'] * 100
                );
            }
        }
        
        return [
            'forecasts' => $forecasts
        ];
    }
    
    public function exportReport(string $reportType, array $filters, string $format): string
    {
        $data = [];
        
        // Get report data based on type
        switch ($reportType) {
            case 'transactions':
                $data = $this->getTransactionHistory($filters);
                break;
            case 'revenue':
                $data = $this->getRevenueReport($filters);
                break;
            case 'commission':
                $data = $this->getCommissionReport($filters);
                break;
            case 'agent_performance':
                $data = $this->getAgentPerformance($filters['agent_id'] ?? 0, $filters);
                break;
            default:
                throw new InvalidArgumentException('Invalid report type');
        }

        // Generate export based on format
        switch ($format) {
            case 'csv':
                return $this->generateCSV($data, $reportType);
            case 'pdf':
                return $this->generatePDF($data, $reportType);
            default:
                throw new InvalidArgumentException('Invalid export format');
        }
    }
    
    private function generateCSV(array $data, string $reportType): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add headers based on report type
        $headers = $this->getReportHeaders($reportType);
        fputcsv($output, $headers);

        // Add data rows
        foreach ($data as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            fputcsv($output, array_values($row));
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    private function generatePDF(array $data, string $reportType): string
    {
        // Initialize TCPDF
        $pdf = new TCPDF(self::PDF_PAGE_ORIENTATION, self::PDF_UNIT, self::PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(self::PDF_CREATOR);
        $pdf->SetAuthor('Imobilien Platform');
        $pdf->SetTitle(ucfirst($reportType) . ' Report');

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('dejavusans', '', 10);

        // Add report title
        $pdf->Cell(0, 10, ucfirst($reportType) . ' Report', 0, 1, 'C');
        
        // Add data table
        $headers = $this->getReportHeaders($reportType);
        $this->addPDFTable($pdf, $headers, $data);

        return $pdf->Output('', 'S');
    }

    private function getReportHeaders(string $reportType): array
    {
        switch ($reportType) {
            case 'transactions':
                return ['ID', 'Дата', 'Тип', 'Сума', 'Статус', 'Агент'];
            case 'revenue':
                return ['Период', 'Приходи', 'Разходи', 'Печалба', 'Марж'];
            case 'commission':
                return ['Агент', 'Брой сделки', 'Обща комисионна', 'Среден %'];
            case 'agent_performance':
                return ['Метрика', 'Стойност', 'Промяна спрямо предходен период'];
            default:
                throw new InvalidArgumentException('Invalid report type');
        }
    }

    private function addPDFTable(TCPDF $pdf, array $headers, array $data): void
    {
        // Calculate column widths
        $width = $pdf->getPageWidth() - 30; // margins
        $colWidth = $width / count($headers);

        // Add headers
        foreach ($headers as $header) {
            $pdf->Cell($colWidth, 7, $header, 1, 0, 'C');
        }
        $pdf->Ln();

        // Add data
        foreach ($data as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            foreach ($row as $cell) {
                $pdf->Cell($colWidth, 6, (string)$cell, 1);
            }
            $pdf->Ln();
        }
    }
    
    public function getComparisonReport(array $period1, array $period2, array $metrics): array
    {
        $results = [];
        
        foreach ($metrics as $metric) {
            // Данни за първия период
            $period1Data = $this->getMetricData($metric, $period1);
            
            // Данни за втория период
            $period2Data = $this->getMetricData($metric, $period2);
            
            // Изчисляване на разлика и процентна промяна
            $difference = $period2Data - $period1Data;
            $percentChange = $period1Data != 0 ? ($difference / $period1Data * 100) : 0;
            
            $results[$metric] = [
                'period1' => $period1Data,
                'period2' => $period2Data,
                'difference' => $difference,
                'percent_change' => $percentChange
            ];
        }
        
        return $results;
    }
    
    private function getMetricData(string $metric, array $period): float
    {
        $where = [
            "t.transaction_date >= :start_date",
            "t.transaction_date <= :end_date",
            "t.status = 'completed'"
        ];
        
        $params = [
            'start_date' => $period['start_date'],
            'end_date' => $period['end_date']
        ];
        
        $sql = match ($metric) {
            'revenue' => "SELECT COALESCE(SUM(amount), 0) FROM transactions t WHERE " . implode(" AND ", $where),
            'commission' => "SELECT COALESCE(SUM(commission_amount), 0) FROM transactions t WHERE " . implode(" AND ", $where),
            'sales_count' => "SELECT COUNT(*) FROM transactions t WHERE type = 'sale' AND " . implode(" AND ", $where),
            'rentals_count' => "SELECT COUNT(*) FROM transactions t WHERE type = 'rent' AND " . implode(" AND ", $where),
            'avg_transaction' => "SELECT COALESCE(AVG(amount), 0) FROM transactions t WHERE " . implode(" AND ", $where),
            default => throw new Exception("Unknown metric: {$metric}")
        };
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }

    private function clearCache(string $type = ''): void
    {
        if ($type) {
            $this->cache->delete($type . '_*');
        } else {
            $this->cache->clear();
        }
    }

    private function calculateTotalRevenue(array $filters): float
    {
        $sql = "SELECT SUM(amount) FROM transactions WHERE status = 'completed'";
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn() ?: 0.0;
    }

    private function calculateTotalCommission(array $filters): float
    {
        $sql = "SELECT SUM(commission_amount) FROM transactions WHERE status = 'completed'";
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn() ?: 0.0;
    }

    private function calculateAverageTransactionValue(array $filters): float
    {
        $sql = "SELECT AVG(amount) FROM transactions WHERE status = 'completed'";
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND transaction_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND transaction_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn() ?: 0.0;
    }

    private function calculateTotalExpenses(array $filters): float
    {
        $sql = "SELECT SUM(amount) FROM expenses WHERE 1=1";
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND expense_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND expense_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn() ?: 0.0;
    }

    private function calculateProfitMargin(array $filters): float
    {
        $revenue = $this->calculateTotalRevenue($filters);
        $expenses = $this->calculateTotalExpenses($filters);
        
        if ($revenue <= 0) {
            return 0.0;
        }
        
        return (($revenue - $expenses) / $revenue) * 100;
    }

    private function calculateYearOverYearGrowth(array $filters): float
    {
        // Get current period revenue
        $currentRevenue = $this->calculateTotalRevenue($filters);
        
        // Calculate previous period dates
        $prevStartDate = date('Y-m-d', strtotime($filters['start_date'] . ' -1 year'));
        $prevEndDate = date('Y-m-d', strtotime($filters['end_date'] . ' -1 year'));
        
        // Get previous period revenue
        $prevFilters = array_merge($filters, [
            'start_date' => $prevStartDate,
            'end_date' => $prevEndDate
        ]);
        $prevRevenue = $this->calculateTotalRevenue($prevFilters);
        
        if ($prevRevenue <= 0) {
            return 0.0;
        }
        
        return (($currentRevenue - $prevRevenue) / $prevRevenue) * 100;
    }
} 