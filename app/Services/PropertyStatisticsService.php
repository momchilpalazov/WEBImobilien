<?php

namespace App\Services;

use App\Interfaces\PropertyRepositoryInterface;
use App\Models\Property;

class PropertyStatisticsService
{
    private PropertyRepositoryInterface $propertyRepository;

    public function __construct(PropertyRepositoryInterface $propertyRepository)
    {
        $this->propertyRepository = $propertyRepository;
    }

    public function getStatistics(array $filters = []): array
    {
        $stats = $this->calculateBaseStatistics($filters);
        
        // Добавяне на сравнителен анализ с предходни периоди
        $monthlyData = array_values($stats['monthly_stats']);
        $count = count($monthlyData);
        
        if ($count >= 2) {
            $currentPeriod = end($monthlyData);
            $previousPeriod = prev($monthlyData);
            
            $stats['comparative'] = [
                'previous_month' => [
                    'listings_change' => $previousPeriod['count'] > 0 
                        ? round(($currentPeriod['count'] - $previousPeriod['count']) / $previousPeriod['count'] * 100, 1)
                        : 0,
                    'value_change' => $previousPeriod['total_value'] > 0
                        ? round(($currentPeriod['total_value'] - $previousPeriod['total_value']) / $previousPeriod['total_value'] * 100, 1)
                        : 0,
                    'avg_price_change' => $previousPeriod['count'] > 0
                        ? round((($currentPeriod['total_value'] / $currentPeriod['count']) - 
                               ($previousPeriod['total_value'] / $previousPeriod['count'])) / 
                               ($previousPeriod['total_value'] / $previousPeriod['count']) * 100, 1)
                        : 0
                ]
            ];
            
            // Сравнение с предходна година
            $currentMonth = array_key_last($stats['monthly_stats']);
            $previousYear = date('Y-m', strtotime($currentMonth . ' -1 year'));
            
            if (isset($stats['monthly_stats'][$previousYear])) {
                $previousYearData = $stats['monthly_stats'][$previousYear];
                
                $stats['comparative']['year_over_year'] = [
                    'listings_change' => $previousYearData['count'] > 0
                        ? round(($currentPeriod['count'] - $previousYearData['count']) / $previousYearData['count'] * 100, 1)
                        : 0,
                    'value_change' => $previousYearData['total_value'] > 0
                        ? round(($currentPeriod['total_value'] - $previousYearData['total_value']) / $previousYearData['total_value'] * 100, 1)
                        : 0,
                    'avg_price_change' => $previousYearData['count'] > 0
                        ? round((($currentPeriod['total_value'] / $currentPeriod['count']) - 
                               ($previousYearData['total_value'] / $previousYearData['count'])) / 
                               ($previousYearData['total_value'] / $previousYearData['count']) * 100, 1)
                        : 0
                ];
            }
        }
        
        // Подобрени предикции с отчитане на сезонност
        $stats['predictions'] = $this->calculatePredictions($stats);
        
        return $stats;
    }

    private function calculateBaseStatistics(array $filters = []): array
    {
        // Извличане на имотите с приложени филтри
        $properties = $this->propertyRepository->findByFilters($filters, 0, PHP_INT_MAX);
        
        // Филтриране по период ако е зададен
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $properties = array_filter($properties, function($property) use ($filters) {
                $date = strtotime($property->created_at);
                $dateFrom = !empty($filters['date_from']) ? strtotime($filters['date_from']) : null;
                $dateTo = !empty($filters['date_to']) ? strtotime($filters['date_to']) : null;
                
                if ($dateFrom && $date < $dateFrom) return false;
                if ($dateTo && $date > $dateTo) return false;
                return true;
            });
        }
        
        $stats = [
            'total' => count($properties),
            'total_value' => 0,
            'avg_price' => 0,
            'avg_area' => 0,
            'by_type' => [],
            'by_status' => [],
            'price_ranges' => [
                '0-50000' => 0,
                '50000-100000' => 0,
                '100000-200000' => 0,
                '200000-500000' => 0,
                '500000+' => 0
            ],
            'area_ranges' => [
                '0-50' => 0,
                '50-100' => 0,
                '100-200' => 0,
                '200-500' => 0,
                '500+' => 0
            ],
            'monthly_stats' => [],
            'recent_changes' => array_slice($properties, 0, 10)
        ];

        // Инициализиране на броячи по тип и статус
        foreach (Property::TYPES as $type => $label) {
            $stats['by_type'][$type] = [
                'count' => 0,
                'total_value' => 0,
                'avg_price' => 0,
                'avg_area' => 0
            ];
        }
        foreach (Property::STATUSES as $status => $label) {
            $stats['by_status'][$status] = [
                'count' => 0,
                'total_value' => 0
            ];
        }

        // Събиране на статистика
        foreach ($properties as $property) {
            // Общи суми
            $stats['total_value'] += $property->price;
            
            // По тип
            $stats['by_type'][$property->type]['count']++;
            $stats['by_type'][$property->type]['total_value'] += $property->price;
            $stats['by_type'][$property->type]['avg_price'] = 
                $stats['by_type'][$property->type]['total_value'] / $stats['by_type'][$property->type]['count'];
            $stats['by_type'][$property->type]['avg_area'] += $property->area;
            
            // По статус
            $stats['by_status'][$property->status]['count']++;
            $stats['by_status'][$property->status]['total_value'] += $property->price;
            
            // Ценови диапазони
            if ($property->price <= 50000) {
                $stats['price_ranges']['0-50000']++;
            } elseif ($property->price <= 100000) {
                $stats['price_ranges']['50000-100000']++;
            } elseif ($property->price <= 200000) {
                $stats['price_ranges']['100000-200000']++;
            } elseif ($property->price <= 500000) {
                $stats['price_ranges']['200000-500000']++;
            } else {
                $stats['price_ranges']['500000+']++;
            }
            
            // Диапазони по площ
            if ($property->area <= 50) {
                $stats['area_ranges']['0-50']++;
            } elseif ($property->area <= 100) {
                $stats['area_ranges']['50-100']++;
            } elseif ($property->area <= 200) {
                $stats['area_ranges']['100-200']++;
            } elseif ($property->area <= 500) {
                $stats['area_ranges']['200-500']++;
            } else {
                $stats['area_ranges']['500+']++;
            }
            
            // Месечна статистика
            $month = date('Y-m', strtotime($property->created_at));
            if (!isset($stats['monthly_stats'][$month])) {
                $stats['monthly_stats'][$month] = [
                    'count' => 0,
                    'total_value' => 0
                ];
            }
            $stats['monthly_stats'][$month]['count']++;
            $stats['monthly_stats'][$month]['total_value'] += $property->price;
        }

        // Изчисляване на средни стойности
        if ($stats['total'] > 0) {
            $stats['avg_price'] = $stats['total_value'] / $stats['total'];
            
            foreach ($stats['by_type'] as &$type) {
                if ($type['count'] > 0) {
                    $type['avg_area'] = $type['avg_area'] / $type['count'];
                }
            }
        }

        // Сортиране на месечната статистика
        ksort($stats['monthly_stats']);

        // Добавяне на приложените филтри към резултата
        $stats['applied_filters'] = array_filter($filters);

        return $stats;
    }

    private function calculatePredictions(array $stats): array
    {
        $predictions = [
            'next_month' => [
                'expected_listings' => 0,
                'price_trend' => 0,
                'popular_types' => [],
                'price_range_distribution' => []
            ],
            'next_quarter' => [
                'market_trend' => '',
                'avg_price_change' => 0,
                'demand_forecast' => []
            ]
        ];

        // Изчисляване на очакван брой имоти за следващия месец
        $monthlyData = array_values($stats['monthly_stats']);
        $count = count($monthlyData);
        if ($count >= 3) {
            $last3Months = array_slice($monthlyData, -3);
            $avgGrowth = 0;
            
            for ($i = 1; $i < 3; $i++) {
                $prevCount = $last3Months[$i-1]['count'];
                if ($prevCount > 0) {
                    $growthRate = ($last3Months[$i]['count'] - $prevCount) / $prevCount;
                    $avgGrowth += $growthRate;
                }
            }
            
            $avgGrowth = $avgGrowth / 2;
            $lastCount = end($last3Months)['count'];
            $predictions['next_month']['expected_listings'] = round($lastCount * (1 + $avgGrowth));
        }

        // Изчисляване на ценови тренд
        if ($count >= 6) {
            $last6Months = array_slice($monthlyData, -6);
            $priceChanges = [];
            
            for ($i = 1; $i < 6; $i++) {
                $prevAvg = $last6Months[$i-1]['total_value'] / $last6Months[$i-1]['count'];
                $currentAvg = $last6Months[$i]['total_value'] / $last6Months[$i]['count'];
                $priceChanges[] = ($currentAvg - $prevAvg) / $prevAvg;
            }
            
            $avgPriceChange = array_sum($priceChanges) / count($priceChanges);
            $predictions['next_month']['price_trend'] = round($avgPriceChange * 100, 1);
        }

        // Определяне на популярни типове имоти
        arsort($stats['by_type']);
        $predictions['next_month']['popular_types'] = array_slice(
            array_keys($stats['by_type']), 
            0, 
            3
        );

        // Прогноза за пазарен тренд
        $predictions['next_quarter']['market_trend'] = $this->determineMarketTrend($stats);
        
        // Прогноза за промяна на средната цена
        if ($predictions['next_month']['price_trend'] > 0) {
            $predictions['next_quarter']['avg_price_change'] = round(
                $predictions['next_month']['price_trend'] * 2.5, 
                1
            );
        } else {
            $predictions['next_quarter']['avg_price_change'] = round(
                $predictions['next_month']['price_trend'] * 1.5, 
                1
            );
        }

        // Прогноза за търсене по тип имот
        foreach ($stats['by_type'] as $type => $data) {
            if ($data['count'] > 0) {
                $trend = $this->calculateTypeTrend($type, $stats);
                $predictions['next_quarter']['demand_forecast'][$type] = $trend;
            }
        }

        return $predictions;
    }

    private function determineMarketTrend(array $stats): string
    {
        $monthlyData = array_values($stats['monthly_stats']);
        $count = count($monthlyData);
        
        if ($count < 3) {
            return 'Недостатъчно данни';
        }

        $last3Months = array_slice($monthlyData, -3);
        $totalGrowth = 0;
        $priceGrowth = 0;

        for ($i = 1; $i < 3; $i++) {
            $prevMonth = $last3Months[$i-1];
            $currentMonth = $last3Months[$i];
            
            if ($prevMonth['count'] > 0) {
                $totalGrowth += ($currentMonth['count'] - $prevMonth['count']) / $prevMonth['count'];
                
                $prevAvgPrice = $prevMonth['total_value'] / $prevMonth['count'];
                $currentAvgPrice = $currentMonth['total_value'] / $currentMonth['count'];
                $priceGrowth += ($currentAvgPrice - $prevAvgPrice) / $prevAvgPrice;
            }
        }

        $totalGrowth = $totalGrowth / 2;
        $priceGrowth = $priceGrowth / 2;

        if ($totalGrowth > 0.1 && $priceGrowth > 0.05) {
            return 'Силен растеж';
        } elseif ($totalGrowth > 0 && $priceGrowth > 0) {
            return 'Умерен растеж';
        } elseif ($totalGrowth < -0.1 && $priceGrowth < -0.05) {
            return 'Значителен спад';
        } elseif ($totalGrowth < 0 || $priceGrowth < 0) {
            return 'Лек спад';
        } else {
            return 'Стабилен';
        }
    }

    private function calculateTypeTrend(string $type, array $stats): string
    {
        $monthlyData = array_values($stats['monthly_stats']);
        $count = count($monthlyData);
        
        if ($count < 3) {
            return 'Стабилно';
        }

        $typeData = $stats['by_type'][$type];
        $totalShare = $typeData['count'] / $stats['total'];
        
        if ($totalShare > 0.3 && $typeData['avg_price'] > $stats['avg_price']) {
            return 'Висока';
        } elseif ($totalShare > 0.2) {
            return 'Умерена';
        } elseif ($totalShare < 0.1) {
            return 'Ниска';
        } else {
            return 'Стабилна';
        }
    }

    private function calculateComparativeAnalysis(array $stats): array
    {
        $monthlyData = array_values($stats['monthly_stats']);
        $count = count($monthlyData);
        
        if ($count < 2) {
            return [
                'previous_period' => [],
                'year_over_year' => []
            ];
        }

        // Сравнение с предходен период
        $currentPeriod = end($monthlyData);
        $previousPeriod = prev($monthlyData);
        
        $previousPeriodComparison = [
            'listings' => [
                'current' => $currentPeriod['count'],
                'previous' => $previousPeriod['count'],
                'change_percentage' => $previousPeriod['count'] > 0 
                    ? round(($currentPeriod['count'] - $previousPeriod['count']) / $previousPeriod['count'] * 100, 1)
                    : 0
            ],
            'total_value' => [
                'current' => $currentPeriod['total_value'],
                'previous' => $previousPeriod['total_value'],
                'change_percentage' => $previousPeriod['total_value'] > 0
                    ? round(($currentPeriod['total_value'] - $previousPeriod['total_value']) / $previousPeriod['total_value'] * 100, 1)
                    : 0
            ],
            'avg_price' => [
                'current' => $currentPeriod['count'] > 0 ? $currentPeriod['total_value'] / $currentPeriod['count'] : 0,
                'previous' => $previousPeriod['count'] > 0 ? $previousPeriod['total_value'] / $previousPeriod['count'] : 0
            ]
        ];
        
        $previousPeriodComparison['avg_price']['change_percentage'] = 
            $previousPeriodComparison['avg_price']['previous'] > 0
                ? round(($previousPeriodComparison['avg_price']['current'] - $previousPeriodComparison['avg_price']['previous']) 
                    / $previousPeriodComparison['avg_price']['previous'] * 100, 1)
                : 0;

        // Сравнение с предходна година
        $yearOverYearComparison = [
            'listings' => ['current' => 0, 'previous' => 0, 'change_percentage' => 0],
            'total_value' => ['current' => 0, 'previous' => 0, 'change_percentage' => 0],
            'avg_price' => ['current' => 0, 'previous' => 0, 'change_percentage' => 0]
        ];

        // Намиране на данни от предходната година
        $currentMonth = array_key_last($stats['monthly_stats']);
        $previousYear = date('Y-m', strtotime($currentMonth . ' -1 year'));
        
        if (isset($stats['monthly_stats'][$previousYear])) {
            $previousYearData = $stats['monthly_stats'][$previousYear];
            
            $yearOverYearComparison['listings'] = [
                'current' => $currentPeriod['count'],
                'previous' => $previousYearData['count'],
                'change_percentage' => $previousYearData['count'] > 0
                    ? round(($currentPeriod['count'] - $previousYearData['count']) / $previousYearData['count'] * 100, 1)
                    : 0
            ];
            
            $yearOverYearComparison['total_value'] = [
                'current' => $currentPeriod['total_value'],
                'previous' => $previousYearData['total_value'],
                'change_percentage' => $previousYearData['total_value'] > 0
                    ? round(($currentPeriod['total_value'] - $previousYearData['total_value']) / $previousYearData['total_value'] * 100, 1)
                    : 0
            ];
            
            $yearOverYearComparison['avg_price'] = [
                'current' => $currentPeriod['count'] > 0 ? $currentPeriod['total_value'] / $currentPeriod['count'] : 0,
                'previous' => $previousYearData['count'] > 0 ? $previousYearData['total_value'] / $previousYearData['count'] : 0
            ];
            
            $yearOverYearComparison['avg_price']['change_percentage'] = 
                $yearOverYearComparison['avg_price']['previous'] > 0
                    ? round(($yearOverYearComparison['avg_price']['current'] - $yearOverYearComparison['avg_price']['previous'])
                        / $yearOverYearComparison['avg_price']['previous'] * 100, 1)
                    : 0;
        }

        return [
            'previous_period' => $previousPeriodComparison,
            'year_over_year' => $yearOverYearComparison
        ];
    }
} 