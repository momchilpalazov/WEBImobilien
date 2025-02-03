<?php

namespace App\Interfaces;

interface FinancialReportInterface
{
    /**
     * Get revenue report
     *
     * @param array $filters Period, property type, agent, etc.
     * @return array Revenue data with breakdowns
     */
    public function getRevenueReport(array $filters): array;
    
    /**
     * Get commission report
     *
     * @param array $filters Period, agent, property type, etc.
     * @return array Commission data with breakdowns
     */
    public function getCommissionReport(array $filters): array;
    
    /**
     * Get agent performance report
     *
     * @param int $agentId
     * @param array $filters Period, metrics, etc.
     * @return array Agent performance data
     */
    public function getAgentPerformance(int $agentId, array $filters): array;
    
    /**
     * Get property type performance report
     *
     * @param array $filters Period, location, etc.
     * @return array Property type performance data
     */
    public function getPropertyTypePerformance(array $filters): array;
    
    /**
     * Get location performance report
     *
     * @param array $filters Period, property type, etc.
     * @return array Location performance data
     */
    public function getLocationPerformance(array $filters): array;
    
    /**
     * Get transaction history
     *
     * @param array $filters Period, type, agent, etc.
     * @param array $sorting Sorting options
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Transaction history with pagination
     */
    public function getTransactionHistory(array $filters, array $sorting = [], int $page = 1, int $perPage = 20): array;
    
    /**
     * Get financial metrics
     *
     * @param array $filters Period, metrics, etc.
     * @return array Key financial metrics
     */
    public function getFinancialMetrics(array $filters): array;
    
    /**
     * Get forecast report
     *
     * @param array $filters Period, metrics, etc.
     * @return array Forecast data
     */
    public function getForecastReport(array $filters): array;
    
    /**
     * Export report to specified format
     *
     * @param string $reportType Type of report to export
     * @param array $filters Report filters
     * @param string $format Export format (pdf, excel, etc.)
     * @return string Path to exported file
     */
    public function exportReport(string $reportType, array $filters, string $format): string;
    
    /**
     * Get comparison report between periods
     *
     * @param array $period1 First period filters
     * @param array $period2 Second period filters
     * @param array $metrics Metrics to compare
     * @return array Comparison data
     */
    public function getComparisonReport(array $period1, array $period2, array $metrics): array;
} 