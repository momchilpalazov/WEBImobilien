<?php

namespace App\Services;

class PropertyComparativeAnalysis
{
    public function analyze(array $monthlyStats): array
    {
        $monthlyData = array_values($monthlyStats);
        $count = count($monthlyData);
        
        if ($count < 2) {
            return [
                'previous_month' => [],
                'year_over_year' => []
            ];
        }

        $currentPeriod = end($monthlyData);
        $previousPeriod = prev($monthlyData);
        
        $analysis = [
            'previous_month' => $this->comparePeriods($currentPeriod, $previousPeriod),
            'year_over_year' => []
        ];
        
        // Сравнение с предходна година
        $currentMonth = array_key_last($monthlyStats);
        $previousYear = date('Y-m', strtotime($currentMonth . ' -1 year'));
        
        if (isset($monthlyStats[$previousYear])) {
            $analysis['year_over_year'] = $this->comparePeriods($currentPeriod, $monthlyStats[$previousYear]);
        }
        
        // Добавяне на тренд анализ
        $analysis['trends'] = $this->analyzeTrends($monthlyStats);
        
        return $analysis;
    }
    
    private function comparePeriods(array $current, array $previous): array
    {
        return [
            'listings' => [
                'current' => $current['count'],
                'previous' => $previous['count'],
                'change_percentage' => $this->calculateChangePercentage(
                    $current['count'],
                    $previous['count']
                )
            ],
            'total_value' => [
                'current' => $current['total_value'],
                'previous' => $previous['total_value'],
                'change_percentage' => $this->calculateChangePercentage(
                    $current['total_value'],
                    $previous['total_value']
                )
            ],
            'avg_price' => [
                'current' => $current['count'] > 0 ? $current['total_value'] / $current['count'] : 0,
                'previous' => $previous['count'] > 0 ? $previous['total_value'] / $previous['count'] : 0,
                'change_percentage' => $this->calculateChangePercentage(
                    $current['count'] > 0 ? $current['total_value'] / $current['count'] : 0,
                    $previous['count'] > 0 ? $previous['total_value'] / $previous['count'] : 0
                )
            ]
        ];
    }
    
    private function calculateChangePercentage(float $current, float $previous): float
    {
        if ($previous <= 0) return 0;
        return round(($current - $previous) / $previous * 100, 1);
    }
    
    private function analyzeTrends(array $monthlyStats): array
    {
        $data = array_values($monthlyStats);
        $count = count($data);
        
        if ($count < 3) {
            return [
                'direction' => 'stable',
                'strength' => 'low',
                'consistency' => 'low'
            ];
        }
        
        // Анализ на последните 3 месеца
        $changes = [];
        for ($i = 1; $i < min(4, $count); $i++) {
            $changes[] = $this->calculateChangePercentage(
                $data[$count - $i]['count'],
                $data[$count - $i - 1]['count']
            );
        }
        
        // Определяне на посоката на тренда
        $avgChange = array_sum($changes) / count($changes);
        $direction = match(true) {
            $avgChange > 5 => 'growing',
            $avgChange < -5 => 'declining',
            default => 'stable'
        };
        
        // Сила на тренда
        $strength = match(true) {
            abs($avgChange) > 15 => 'high',
            abs($avgChange) > 5 => 'medium',
            default => 'low'
        };
        
        // Консистентност на тренда
        $isConsistent = true;
        $firstChange = $changes[0] >= 0;
        foreach ($changes as $change) {
            if (($change >= 0) !== $firstChange) {
                $isConsistent = false;
                break;
            }
        }
        
        return [
            'direction' => $direction,
            'strength' => $strength,
            'consistency' => $isConsistent ? 'high' : 'low',
            'average_change' => round($avgChange, 1)
        ];
    }
} 