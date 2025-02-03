<?php

namespace App\Controllers;

use App\Interfaces\FinancialReportInterface;
use DateTime;

class FinancialReportController extends BaseAdminController
{
    private FinancialReportInterface $reportService;
    
    public function __construct(FinancialReportInterface $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }
    
    /**
     * Показва основната страница с финансови отчети
     */
    public function index()
    {
        $filters = $this->getFilters();
        
        // Основни финансови метрики
        $metrics = $this->reportService->getFinancialMetrics($filters);
        
        // Приходи и комисионни
        $revenue = $this->reportService->getRevenueReport($filters);
        
        // Прогнози
        $forecasts = $this->reportService->getForecastReport($filters);
        
        // Сравнение с предходен период
        $currentPeriod = [
            'start_date' => $filters['start_date'] ?? date('Y-m-01'),
            'end_date' => $filters['end_date'] ?? date('Y-m-t')
        ];
        
        $previousStart = new DateTime($currentPeriod['start_date']);
        $previousEnd = new DateTime($currentPeriod['end_date']);
        $interval = $previousStart->diff($previousEnd);
        
        $previousPeriod = [
            'start_date' => $previousStart->modify("-{$interval->days} days")->format('Y-m-d'),
            'end_date' => $previousEnd->modify("-{$interval->days} days")->format('Y-m-d')
        ];
        
        $comparison = $this->reportService->getComparisonReport(
            $previousPeriod,
            $currentPeriod,
            ['revenue', 'commission', 'sales_count', 'rentals_count', 'avg_transaction']
        );
        
        return $this->render('financial/index', [
            'metrics' => $metrics,
            'revenue' => $revenue,
            'forecasts' => $forecasts['forecasts'],
            'comparison' => $comparison,
            'filters' => $filters
        ]);
    }
    
    /**
     * Показва отчет за комисионни
     */
    public function commissions()
    {
        $filters = $this->getFilters();
        $report = $this->reportService->getCommissionReport($filters);
        
        if ($this->isAjax()) {
            return $this->json($report);
        }
        
        return $this->render('financial/commissions', [
            'report' => $report,
            'filters' => $filters
        ]);
    }
    
    /**
     * Показва отчет за представянето на агент
     */
    public function agentPerformance(int $agentId)
    {
        $filters = $this->getFilters();
        $report = $this->reportService->getAgentPerformance($agentId, $filters);
        
        if ($this->isAjax()) {
            return $this->json($report);
        }
        
        return $this->render('financial/agent_performance', [
            'report' => $report,
            'filters' => $filters,
            'agent_id' => $agentId
        ]);
    }
    
    /**
     * Показва отчет по тип имот
     */
    public function propertyTypes()
    {
        $filters = $this->getFilters();
        $report = $this->reportService->getPropertyTypePerformance($filters);
        
        if ($this->isAjax()) {
            return $this->json($report);
        }
        
        return $this->render('financial/property_types', [
            'report' => $report,
            'filters' => $filters
        ]);
    }
    
    /**
     * Показва отчет по локация
     */
    public function locations()
    {
        $filters = $this->getFilters();
        $report = $this->reportService->getLocationPerformance($filters);
        
        if ($this->isAjax()) {
            return $this->json($report);
        }
        
        return $this->render('financial/locations', [
            'report' => $report,
            'filters' => $filters
        ]);
    }
    
    /**
     * Показва история на транзакциите
     */
    public function transactions()
    {
        $filters = $this->getFilters();
        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 20);
        
        $sorting = [
            'field' => $_GET['sort'] ?? 'transaction_date',
            'direction' => $_GET['direction'] ?? 'desc'
        ];
        
        $history = $this->reportService->getTransactionHistory($filters, $sorting, $page, $perPage);
        
        if ($this->isAjax()) {
            return $this->json($history);
        }
        
        return $this->render('financial/transactions', [
            'history' => $history,
            'filters' => $filters,
            'sorting' => $sorting
        ]);
    }
    
    /**
     * Експортира отчет във файл
     */
    public function export()
    {
        if (!$this->isPost()) {
            return $this->redirect('/financial');
        }
        
        try {
            $reportType = $_POST['report_type'] ?? '';
            $format = $_POST['format'] ?? 'pdf';
            $filters = $this->getFilters();
            
            $filePath = $this->reportService->exportReport($reportType, $filters, $format);
            
            // TODO: Implement file download
            return $this->redirect('/financial');
            
        } catch (\Exception $e) {
            $this->setError('Грешка при експортиране на отчета: ' . $e->getMessage());
            return $this->redirect('/financial');
        }
    }
    
    /**
     * Извлича филтрите от заявката
     */
    private function getFilters(): array
    {
        $filters = [];
        
        // Период
        if (!empty($_GET['start_date'])) {
            $filters['start_date'] = $_GET['start_date'];
        }
        if (!empty($_GET['end_date'])) {
            $filters['end_date'] = $_GET['end_date'];
        }
        
        // Агент
        if (!empty($_GET['agent_id'])) {
            $filters['agent_id'] = (int)$_GET['agent_id'];
        }
        
        // Тип имот
        if (!empty($_GET['property_type'])) {
            $filters['property_type'] = $_GET['property_type'];
        }
        
        // Локация
        if (!empty($_GET['location'])) {
            $filters['location'] = $_GET['location'];
        }
        
        // Тип транзакция
        if (!empty($_GET['type'])) {
            $filters['type'] = $_GET['type'];
        }
        
        // Статус
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        
        return $filters;
    }
} 