<?php

namespace App\Services;

use App\Interfaces\PropertyMatchingInterface;
use App\Interfaces\PropertyRepositoryInterface;
use App\Interfaces\ClientRepositoryInterface;
use PDO;

class PropertyMatchingService implements PropertyMatchingInterface
{
    private PropertyRepositoryInterface $propertyRepository;
    private ClientRepositoryInterface $clientRepository;
    private PDO $db;
    
    public function __construct(
        PropertyRepositoryInterface $propertyRepository,
        ClientRepositoryInterface $clientRepository,
        PDO $db
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->clientRepository = $clientRepository;
        $this->db = $db;
    }
    
    public function findMatchingProperties(int $clientId, array $preferences = []): array
    {
        $client = $this->clientRepository->find($clientId);
        if (!$client) {
            return [];
        }
        
        // Обединяваме запазените предпочитания с новите
        $clientPreferences = json_decode($client['preferences'] ?? '{}', true) ?: [];
        $preferences = array_merge($clientPreferences, $preferences);
        
        // Намираме всички имоти, които отговарят на основните критерии
        $properties = $this->propertyRepository->findByFilters([
            'type' => $preferences['property_type'] ?? null,
            'price_min' => $preferences['price_min'] ?? null,
            'price_max' => $preferences['price_max'] ?? null,
            'area_min' => $preferences['area_min'] ?? null,
            'area_max' => $preferences['area_max'] ?? null,
            'location' => $preferences['locations'] ?? [],
            'features' => $preferences['required_features'] ?? []
        ]);
        
        // Изчисляваме резултат за съвпадение за всеки имот
        $matches = [];
        foreach ($properties as $property) {
            $matchDetails = $this->calculateMatchScore($property, $preferences);
            if ($matchDetails['overall_score'] >= 50) { // Минимален праг за съвпадение
                $matches[] = [
                    'property' => $property,
                    'match_details' => $matchDetails
                ];
            }
        }
        
        // Сортираме по резултат в низходящ ред
        usort($matches, function($a, $b) {
            return $b['match_details']['overall_score'] <=> $a['match_details']['overall_score'];
        });
        
        return $matches;
    }
    
    public function findMatchingClients(int $propertyId): array
    {
        $property = $this->propertyRepository->findById($propertyId);
        if (!$property) {
            return [];
        }
        
        // Намираме клиенти с подходящи предпочитания
        $clients = $this->clientRepository->findAll();
        
        $matches = [];
        foreach ($clients as $client) {
            $preferences = json_decode($client['preferences'] ?? '{}', true) ?: [];
            $matchDetails = $this->calculateMatchScore($property, $preferences);
            
            if ($matchDetails['overall_score'] >= 50) {
                $matches[] = [
                    'client' => $client,
                    'match_details' => $matchDetails
                ];
            }
        }
        
        usort($matches, function($a, $b) {
            return $b['match_details']['overall_score'] <=> $a['match_details']['overall_score'];
        });
        
        return $matches;
    }
    
    public function updateClientPreferences(int $clientId, array $preferences): bool
    {
        return $this->clientRepository->update($clientId, [
            'preferences' => json_encode($preferences)
        ]);
    }
    
    public function getMatchScore(int $propertyId, int $clientId): array
    {
        $property = $this->propertyRepository->findById($propertyId);
        $client = $this->clientRepository->find($clientId);
        
        if (!$property || !$client) {
            return [
                'overall_score' => 0,
                'criteria_scores' => []
            ];
        }
        
        $preferences = json_decode($client['preferences'] ?? '{}', true) ?: [];
        return $this->calculateMatchScore($property, $preferences);
    }
    
    public function saveMatchHistory(int $propertyId, int $clientId, array $matchDetails): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO property_match_history (
                property_id, client_id, match_score, match_details, created_at
            ) VALUES (
                :property_id, :client_id, :match_score, :match_details, NOW()
            )
        ");
        
        return $stmt->execute([
            'property_id' => $propertyId,
            'client_id' => $clientId,
            'match_score' => $matchDetails['overall_score'],
            'match_details' => json_encode($matchDetails)
        ]);
    }
    
    public function getClientMatchHistory(int $clientId): array
    {
        $stmt = $this->db->prepare("
            SELECT h.*, p.title_bg, p.address, p.price
            FROM property_match_history h
            JOIN properties p ON p.id = h.property_id
            WHERE h.client_id = :client_id
            ORDER BY h.created_at DESC
        ");
        
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }
    
    private function calculateMatchScore(array $property, array $preferences): array
    {
        $scores = [
            'price' => $this->calculatePriceScore($property['price'], $preferences),
            'area' => $this->calculateAreaScore($property['area'], $preferences),
            'location' => $this->calculateLocationScore($property['location'], $preferences),
            'features' => $this->calculateFeaturesScore($property['features'], $preferences),
            'type' => $this->calculateTypeScore($property['type'], $preferences)
        ];
        
        // Тежест на различните критерии
        $weights = [
            'price' => 0.3,
            'area' => 0.2,
            'location' => 0.25,
            'features' => 0.15,
            'type' => 0.1
        ];
        
        // Изчисляване на общ резултат
        $overallScore = 0;
        foreach ($scores as $criterion => $score) {
            $overallScore += $score * $weights[$criterion];
        }
        
        return [
            'overall_score' => round($overallScore),
            'criteria_scores' => $scores
        ];
    }
    
    private function calculatePriceScore(float $price, array $preferences): float
    {
        if (!isset($preferences['price_min']) || !isset($preferences['price_max'])) {
            return 100;
        }
        
        $min = $preferences['price_min'];
        $max = $preferences['price_max'];
        
        if ($price >= $min && $price <= $max) {
            return 100;
        }
        
        // Изчисляваме колко далеч е цената от желания диапазон
        $distance = min(abs($price - $min), abs($price - $max));
        $range = $max - $min;
        
        return max(0, 100 - ($distance / $range * 100));
    }
    
    private function calculateAreaScore(float $area, array $preferences): float
    {
        if (!isset($preferences['area_min']) || !isset($preferences['area_max'])) {
            return 100;
        }
        
        $min = $preferences['area_min'];
        $max = $preferences['area_max'];
        
        if ($area >= $min && $area <= $max) {
            return 100;
        }
        
        $distance = min(abs($area - $min), abs($area - $max));
        $range = $max - $min;
        
        return max(0, 100 - ($distance / $range * 100));
    }
    
    private function calculateLocationScore(string $location, array $preferences): float
    {
        if (!isset($preferences['locations']) || empty($preferences['locations'])) {
            return 100;
        }
        
        if (in_array($location, $preferences['locations'])) {
            return 100;
        }
        
        // Проверяваме дали локацията е в близост до желаните
        return 50; // Базова стойност за локации, които не са в списъка
    }
    
    private function calculateFeaturesScore(array $features, array $preferences): float
    {
        if (!isset($preferences['required_features']) || empty($preferences['required_features'])) {
            return 100;
        }
        
        $requiredFeatures = $preferences['required_features'];
        $matchingFeatures = array_intersect($features, $requiredFeatures);
        
        return (count($matchingFeatures) / count($requiredFeatures)) * 100;
    }
    
    private function calculateTypeScore(string $type, array $preferences): float
    {
        if (!isset($preferences['property_type'])) {
            return 100;
        }
        
        return $type === $preferences['property_type'] ? 100 : 0;
    }
} 