<?php

namespace App\Services;

use DateTime;
use Exception;

class PropertySearchService
{
    private $propertyRepository;
    private $locationRepository;
    private $featureRepository;
    private $searchRepository;
    private $notificationService;

    public function __construct(
        PropertyRepositoryInterface $propertyRepository,
        LocationRepositoryInterface $locationRepository,
        FeatureRepositoryInterface $featureRepository,
        SearchRepositoryInterface $searchRepository,
        NotificationService $notificationService
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->locationRepository = $locationRepository;
        $this->featureRepository = $featureRepository;
        $this->searchRepository = $searchRepository;
        $this->notificationService = $notificationService;
    }

    public function search(array $criteria, ?int $userId = null): array
    {
        try {
            $this->validateSearchCriteria($criteria);

            // Форматиране на критериите за търсене
            $formattedCriteria = $this->formatSearchCriteria($criteria);

            // Запазване на търсенето, ако има потребител
            if ($userId && ($criteria['save_search'] ?? false)) {
                $this->saveSearch($userId, $formattedCriteria);
            }

            // Изпълнение на търсенето
            $results = $this->propertyRepository->findByAdvancedCriteria($formattedCriteria);

            // Добавяне на допълнителна информация
            return $this->enrichSearchResults($results);

        } catch (Exception $e) {
            error_log("Error performing property search: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSimilarProperties(int $propertyId, int $limit = 4): array
    {
        try {
            // Вземане на оригиналния имот
            $property = $this->propertyRepository->find($propertyId);
            if (!$property) {
                throw new Exception('Имотът не е намерен.');
            }

            // Създаване на критерии базирани на оригиналния имот
            $criteria = [
                'property_type' => $property['type'],
                'transaction_type' => $property['transaction_type'],
                'price_range' => [
                    'min' => $property['price'] * 0.8,
                    'max' => $property['price'] * 1.2
                ],
                'area_range' => [
                    'min' => $property['area'] * 0.8,
                    'max' => $property['area'] * 1.2
                ],
                'locations' => [$property['location_id']],
                'exclude_property' => $propertyId,
                'limit' => $limit
            ];

            // Търсене на подобни имоти
            $results = $this->propertyRepository->findByAdvancedCriteria($criteria);
            
            // Изчисляване на процент на съвпадение
            return $this->calculateSimilarity($results, $property);

        } catch (Exception $e) {
            error_log("Error finding similar properties: " . $e->getMessage());
            throw $e;
        }
    }

    public function saveSearch(int $userId, array $criteria): int
    {
        try {
            // Проверка за съществуващо подобно търсене
            $existingSearch = $this->searchRepository->findSimilar($userId, $criteria);
            if ($existingSearch) {
                throw new Exception('Вече имате запазено подобно търсене.');
            }

            // Запазване на търсенето
            return $this->searchRepository->create([
                'user_id' => $userId,
                'criteria' => json_encode($criteria),
                'name' => $criteria['search_name'] ?? 'Търсене от ' . date('d.m.Y H:i'),
                'notifications_enabled' => $criteria['notifications_enabled'] ?? true,
                'created_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            error_log("Error saving search: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSavedSearches(int $userId): array
    {
        return $this->searchRepository->findByUser($userId);
    }

    public function deleteSavedSearch(int $searchId, int $userId): bool
    {
        try {
            $search = $this->searchRepository->find($searchId);
            if (!$search || $search['user_id'] !== $userId) {
                throw new Exception('Търсенето не е намерено.');
            }

            return $this->searchRepository->delete($searchId);

        } catch (Exception $e) {
            error_log("Error deleting saved search: " . $e->getMessage());
            throw $e;
        }
    }

    public function checkNewProperties(int $searchId): array
    {
        try {
            $search = $this->searchRepository->find($searchId);
            if (!$search) {
                throw new Exception('Търсенето не е намерено.');
            }

            $criteria = json_decode($search['criteria'], true);
            $criteria['created_after'] = $search['last_check'] ?? $search['created_at'];

            // Търсене на нови имоти
            $newProperties = $this->propertyRepository->findByAdvancedCriteria($criteria);

            if (!empty($newProperties)) {
                // Актуализиране на времето на последна проверка
                $this->searchRepository->update($searchId, [
                    'last_check' => date('Y-m-d H:i:s')
                ]);

                // Изпращане на известие
                if ($search['notifications_enabled']) {
                    $this->notificationService->sendNewPropertiesNotification([
                        'user_id' => $search['user_id'],
                        'search_name' => $search['name'],
                        'properties' => $newProperties
                    ]);
                }
            }

            return $newProperties;

        } catch (Exception $e) {
            error_log("Error checking new properties: " . $e->getMessage());
            throw $e;
        }
    }

    private function validateSearchCriteria(array $criteria): void
    {
        // Валидация на ценови диапазон
        if (!empty($criteria['price_min']) && !empty($criteria['price_max'])) {
            if ($criteria['price_min'] > $criteria['price_max']) {
                throw new Exception('Невалиден ценови диапазон.');
            }
        }

        // Валидация на площ
        if (!empty($criteria['area_min']) && !empty($criteria['area_max'])) {
            if ($criteria['area_min'] > $criteria['area_max']) {
                throw new Exception('Невалиден диапазон на площта.');
            }
        }

        // Валидация на локации
        if (!empty($criteria['locations'])) {
            foreach ($criteria['locations'] as $locationId) {
                if (!$this->locationRepository->find($locationId)) {
                    throw new Exception('Невалидна локация.');
                }
            }
        }

        // Валидация на характеристики
        if (!empty($criteria['features'])) {
            foreach ($criteria['features'] as $featureId) {
                if (!$this->featureRepository->find($featureId)) {
                    throw new Exception('Невалидна характеристика.');
                }
            }
        }
    }

    private function formatSearchCriteria(array $criteria): array
    {
        return [
            'property_type' => $criteria['property_type'] ?? [],
            'transaction_type' => $criteria['transaction_type'] ?? 'any',
            'price_range' => [
                'min' => $criteria['price_min'] ?? null,
                'max' => $criteria['price_max'] ?? null
            ],
            'area_range' => [
                'min' => $criteria['area_min'] ?? null,
                'max' => $criteria['area_max'] ?? null
            ],
            'rooms_range' => [
                'min' => $criteria['rooms_min'] ?? null,
                'max' => $criteria['rooms_max'] ?? null
            ],
            'floor_range' => [
                'min' => $criteria['floor_min'] ?? null,
                'max' => $criteria['floor_max'] ?? null
            ],
            'locations' => $criteria['locations'] ?? [],
            'features' => $criteria['features'] ?? [],
            'keywords' => $criteria['keywords'] ?? null,
            'construction_year' => [
                'min' => $criteria['construction_year_min'] ?? null,
                'max' => $criteria['construction_year_max'] ?? null
            ],
            'parking' => $criteria['parking'] ?? null,
            'heating' => $criteria['heating'] ?? null,
            'furnishing' => $criteria['furnishing'] ?? null,
            'status' => 'active',
            'sort' => [
                'field' => $criteria['sort_field'] ?? 'created_at',
                'direction' => $criteria['sort_direction'] ?? 'desc'
            ],
            'limit' => $criteria['limit'] ?? 20,
            'offset' => $criteria['offset'] ?? 0
        ];
    }

    private function enrichSearchResults(array $results): array
    {
        foreach ($results as &$property) {
            // Добавяне на допълнителни изображения
            $property['images'] = $this->propertyRepository->getImages($property['id']);

            // Добавяне на характеристики
            $property['features'] = $this->propertyRepository->getFeatures($property['id']);

            // Добавяне на информация за локацията
            $property['location'] = $this->locationRepository->find($property['location_id']);

            // Изчисляване на цена на квадрат
            $property['price_per_sqm'] = $property['price'] / $property['area'];

            // Добавяне на близки обекти
            $property['nearby'] = $this->propertyRepository->getNearbyPlaces($property['id']);
        }

        return $results;
    }

    private function calculateSimilarity(array $properties, array $originalProperty): array
    {
        foreach ($properties as &$property) {
            $similarity = 0;
            $factors = 0;

            // Тип имот
            if ($property['type'] === $originalProperty['type']) {
                $similarity += 20;
                $factors++;
            }

            // Цена (±20%)
            $priceDiff = abs($property['price'] - $originalProperty['price']) / $originalProperty['price'];
            if ($priceDiff <= 0.2) {
                $similarity += 20 * (1 - $priceDiff);
                $factors++;
            }

            // Площ (±20%)
            $areaDiff = abs($property['area'] - $originalProperty['area']) / $originalProperty['area'];
            if ($areaDiff <= 0.2) {
                $similarity += 20 * (1 - $areaDiff);
                $factors++;
            }

            // Локация
            if ($property['location_id'] === $originalProperty['location_id']) {
                $similarity += 20;
                $factors++;
            }

            // Характеристики
            $originalFeatures = $this->propertyRepository->getFeatures($originalProperty['id']);
            $propertyFeatures = $this->propertyRepository->getFeatures($property['id']);
            $commonFeatures = array_intersect($originalFeatures, $propertyFeatures);
            
            if (!empty($originalFeatures)) {
                $featureSimilarity = count($commonFeatures) / count($originalFeatures) * 20;
                $similarity += $featureSimilarity;
                $factors++;
            }

            // Изчисляване на финален процент
            $property['similarity'] = $factors > 0 ? round($similarity / $factors) : 0;
        }

        // Сортиране по процент на съвпадение
        usort($properties, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return $properties;
    }
} 