<?php

namespace App\Services;

class PropertyReportService
{
    private $propertyRepository;
    private $agentRepository;
    private $viewingRepository;
    private $taskRepository;
    private $exportService;

    public function __construct(
        PropertyRepositoryInterface $propertyRepository,
        AgentRepositoryInterface $agentRepository,
        ViewingRepositoryInterface $viewingRepository,
        TaskRepositoryInterface $taskRepository,
        ExportService $exportService
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->agentRepository = $agentRepository;
        $this->viewingRepository = $viewingRepository;
        $this->taskRepository = $taskRepository;
        $this->exportService = $exportService;
    }

    // Тук ще добавим методите за генериране на отчети
    public function generateReport(string $type, array $filters = [], string $format = 'pdf'): string
    {
        try {
            $data = $this->collectReportData($type, $filters);
            $template = $this->getReportTemplate($type);
            
            return $this->exportService->export($data, $template, $format);
        } catch (\Exception $e) {
            error_log("Error generating {$type} report: " . $e->getMessage());
            throw $e;
        }
    }

    private function collectReportData(string $type, array $filters): array
    {
        switch ($type) {
            case 'property_performance':
                return $this->getPropertyPerformanceData($filters);
            case 'agent_activity':
                return $this->getAgentActivityData($filters);
            case 'market_analysis':
                return $this->getMarketAnalysisData($filters);
            case 'financial_summary':
                return $this->getFinancialSummaryData($filters);
            default:
                throw new \Exception('Невалиден тип отчет.');
        }
    }

    private function getPropertyPerformanceData(array $filters): array
    {
        $startDate = $this->getStartDate($filters['period'], $filters['start_date'] ?? null);
        $properties = $this->propertyRepository->findByFilters($filters);
        
        $performanceData = [];
        foreach ($properties as $property) {
            $viewings = $this->viewingRepository->findByProperty($property['id'], $startDate);
            $tasks = $this->taskRepository->findByProperty($property['id'], $startDate);
            
            $performanceData[] = [
                'id' => $property['id'],
                'title' => $property['title'],
                'type' => $property['type'],
                'price' => $property['price'],
                'status' => $property['status'],
                'days_on_market' => $this->calculateDaysOnMarket($property),
                'viewings_count' => count($viewings),
                'tasks_completed' => count(array_filter($tasks, fn($task) => $task['status'] === 'completed')),
                'last_activity' => $this->getLastActivity($property['id']),
                'price_changes' => $this->getPriceChanges($property['id'], $startDate)
            ];
        }

        return $performanceData;
    }

    private function getAgentActivityData(array $filters): array
    {
        $startDate = $this->getStartDate($filters['period'], $filters['start_date'] ?? null);
        $agents = $filters['agent_id'] 
            ? [$this->agentRepository->find($filters['agent_id'])]
            : $this->agentRepository->findAll();

        $activityData = [];
        foreach ($agents as $agent) {
            $properties = $this->propertyRepository->findByAgent($agent['id'], $startDate);
            $viewings = $this->viewingRepository->findByAgent($agent['id'], $startDate);
            $tasks = $this->taskRepository->findByAgent($agent['id'], $startDate);

            $activityData[] = [
                'id' => $agent['id'],
                'name' => $agent['name'],
                'properties_listed' => count($properties),
                'properties_sold' => count(array_filter($properties, fn($p) => $p['status'] === 'sold')),
                'viewings_conducted' => count($viewings),
                'tasks_completed' => count(array_filter($tasks, fn($t) => $t['status'] === 'completed')),
                'response_time' => $this->calculateAverageResponseTime($agent['id'], $startDate),
                'client_satisfaction' => $this->calculateClientSatisfaction($agent['id'], $startDate)
            ];
        }

        return $activityData;
    }

    private function getMarketAnalysisData(array $filters): array
    {
        $startDate = $this->getStartDate($filters['period'], $filters['start_date'] ?? null);
        $properties = $this->propertyRepository->findByFilters($filters);

        $propertyTypes = [];
        $locations = [];
        $priceRanges = [];

        foreach ($properties as $property) {
            // Анализ по тип имот
            $type = $property['type'];
            if (!isset($propertyTypes[$type])) {
                $propertyTypes[$type] = ['count' => 0, 'avg_price' => 0, 'total_price' => 0];
            }
            $propertyTypes[$type]['count']++;
            $propertyTypes[$type]['total_price'] += $property['price'];

            // Анализ по локация
            $location = $property['location'];
            if (!isset($locations[$location])) {
                $locations[$location] = ['count' => 0, 'avg_price' => 0, 'total_price' => 0];
            }
            $locations[$location]['count']++;
            $locations[$location]['total_price'] += $property['price'];

            // Анализ по ценови диапазон
            $priceRange = $this->getPriceRange($property['price']);
            if (!isset($priceRanges[$priceRange])) {
                $priceRanges[$priceRange] = 0;
            }
            $priceRanges[$priceRange]++;
        }

        // Изчисляване на средни цени
        foreach ($propertyTypes as &$data) {
            $data['avg_price'] = $data['count'] > 0 ? $data['total_price'] / $data['count'] : 0;
        }
        foreach ($locations as &$data) {
            $data['avg_price'] = $data['count'] > 0 ? $data['total_price'] / $data['count'] : 0;
        }

        return [
            'property_types' => $propertyTypes,
            'locations' => $locations,
            'price_ranges' => $priceRanges,
            'total_properties' => count($properties),
            'market_trends' => $this->calculateMarketTrends($startDate)
        ];
    }

    private function getFinancialSummaryData(array $filters): array
    {
        $startDate = $this->getStartDate($filters['period'], $filters['start_date'] ?? null);
        $properties = $this->propertyRepository->findByFilters($filters);

        $totalRevenue = 0;
        $totalCommissions = 0;
        $soldProperties = [];
        $pendingProperties = [];

        foreach ($properties as $property) {
            if ($property['status'] === 'sold') {
                $soldProperties[] = $property;
                $totalRevenue += $property['price'];
                $totalCommissions += $this->calculateCommission($property);
            } elseif ($property['status'] === 'pending') {
                $pendingProperties[] = $property;
            }
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_commissions' => $totalCommissions,
            'properties_sold' => [
                'count' => count($soldProperties),
                'value' => array_sum(array_column($soldProperties, 'price'))
            ],
            'properties_pending' => [
                'count' => count($pendingProperties),
                'value' => array_sum(array_column($pendingProperties, 'price'))
            ],
            'average_sale_price' => count($soldProperties) > 0 
                ? array_sum(array_column($soldProperties, 'price')) / count($soldProperties)
                : 0,
            'commission_breakdown' => $this->getCommissionBreakdown($soldProperties),
            'monthly_comparison' => $this->getMonthlyComparison($startDate)
        ];
    }

    private function getStartDate(?string $period, ?string $customDate): \DateTime
    {
        if ($customDate) {
            return new \DateTime($customDate);
        }

        $date = new \DateTime();
        switch ($period) {
            case 'week':
                $date->modify('-1 week');
                break;
            case 'month':
                $date->modify('-1 month');
                break;
            case 'quarter':
                $date->modify('-3 months');
                break;
            case 'year':
                $date->modify('-1 year');
                break;
            default:
                $date->modify('-1 month');
        }
        return $date;
    }

    private function calculateDaysOnMarket(array $property): int
    {
        $listingDate = new \DateTime($property['created_at']);
        $endDate = $property['status'] === 'sold' 
            ? new \DateTime($property['sold_at'])
            : new \DateTime();
        
        return $listingDate->diff($endDate)->days;
    }

    private function getLastActivity(int $propertyId): array
    {
        $activities = array_merge(
            $this->viewingRepository->findByProperty($propertyId),
            $this->taskRepository->findByProperty($propertyId)
        );

        usort($activities, fn($a, $b) => 
            strtotime($b['created_at']) - strtotime($a['created_at'])
        );

        return $activities[0] ?? [];
    }

    private function getPriceChanges(int $propertyId, \DateTime $startDate): array
    {
        // Имплементация за проследяване на промените в цената
        return [];
    }

    private function calculateAverageResponseTime(int $agentId, \DateTime $startDate): float
    {
        // Имплементация за изчисляване на средното време за отговор
        return 0.0;
    }

    private function calculateClientSatisfaction(int $agentId, \DateTime $startDate): float
    {
        // Имплементация за изчисляване на удовлетвореността на клиентите
        return 0.0;
    }

    private function getPriceRange(float $price): string
    {
        if ($price < 100000) return '0-100k';
        if ($price < 200000) return '100k-200k';
        if ($price < 300000) return '200k-300k';
        if ($price < 500000) return '300k-500k';
        return '500k+';
    }

    private function calculateMarketTrends(\DateTime $startDate): array
    {
        // Имплементация за изчисляване на пазарните тенденции
        return [];
    }

    private function calculateCommission(array $property): float
    {
        // Имплементация за изчисляване на комисионната
        return 0.0;
    }

    private function getCommissionBreakdown(array $properties): array
    {
        // Имплементация за разбивка на комисионните
        return [];
    }

    private function getMonthlyComparison(\DateTime $startDate): array
    {
        // Имплементация за месечно сравнение
        return [];
    }

    private function getReportTemplate(string $type): string
    {
        $templates = [
            'property_performance' => 'reports/property_performance',
            'agent_activity' => 'reports/agent_activity',
            'market_analysis' => 'reports/market_analysis',
            'financial_summary' => 'reports/financial_summary'
        ];

        if (!isset($templates[$type])) {
            throw new \Exception('Невалиден тип отчет.');
        }

        return $templates[$type];
    }
} 