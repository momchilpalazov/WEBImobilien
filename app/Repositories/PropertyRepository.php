<?php

namespace App\Repositories;

use App\Interfaces\PropertyRepositoryInterface;
use App\Models\Property;
use PDO;

class PropertyRepository implements PropertyRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(array $filters = []): array
    {
        return $this->getAllProperties($filters);
    }

    public function find(int $id): ?array
    {
        return $this->getPropertyById($id);
    }

    public function create(Property $property): int
    {
        $data = $property->toArray();
        $fields = $this->prepareFields($data);
        
        $query = "INSERT INTO properties (" . implode(', ', array_keys($fields)) . ") 
                 VALUES (" . implode(', ', array_fill(0, count($fields), '?')) . ")";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(array_values($fields));
        
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = $this->prepareFields($data);
        
        $setClause = implode(', ', array_map(fn($field) => "{$field} = ?", array_keys($fields)));
        $query = "UPDATE properties SET {$setClause} WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([...array_values($fields), $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM properties WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllProperties(array $filters = []): array
    {
        $sql = "SELECT p.*, 
                COALESCE(
                    (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1),
                    (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY id ASC LIMIT 1)
                ) as image_path
                FROM properties p WHERE 1=1";
        
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND p.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['min_area'])) {
            $sql .= " AND p.area >= ?";
            $params[] = $filters['min_area'];
        }

        if (!empty($filters['max_area'])) {
            $sql .= " AND p.area <= ?";
            $params[] = $filters['max_area'];
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }

        // Add sorting
        $sql .= $this->addSorting($filters['sort'] ?? 'date_desc');

        // Add pagination
        if (isset($filters['per_page']) && isset($filters['page'])) {
            $offset = ($filters['page'] - 1) * $filters['per_page'];
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $filters['per_page'];
            $params[] = $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $properties = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $properties[] = $this->createPropertyFromRow($row);
        }
        
        return $properties;
    }

    public function getPropertyById(int $id): ?array
    {
        $sql = "SELECT p.*, 
                COALESCE(
                    (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1),
                    (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY id ASC LIMIT 1)
                ) as image_path
                FROM properties p WHERE p.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function getPropertiesByStatus(string $status): array
    {
        return $this->getAllProperties(['status' => $status]);
    }

    public function getPropertiesByType(string $type): array
    {
        return $this->getAllProperties(['type' => $type]);
    }

    private function addSorting(string $sort): string
    {
        switch ($sort) {
            case 'date_asc':
                return " ORDER BY p.created_at ASC";
            case 'price_asc':
                return " ORDER BY p.price ASC";
            case 'price_desc':
                return " ORDER BY p.price DESC";
            case 'area_asc':
                return " ORDER BY p.area ASC";
            case 'area_desc':
                return " ORDER BY p.area DESC";
            case 'date_desc':
            default:
                return " ORDER BY p.created_at DESC";
        }
    }

    /**
     * @return Property[]
     */
    public function findByFilters(array $filters = [], int $offset = 0, int $limit = 10): array
    {
        $query = "SELECT * FROM properties WHERE 1=1";
        $params = [];
        
        // Филтриране по тип
        if (!empty($filters['type'])) {
            $query .= " AND type = ?";
            $params[] = $filters['type'];
        }
        
        // Филтриране по статус
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        // Филтриране по цена
        if (!empty($filters['min_price'])) {
            $query .= " AND price >= ?";
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $query .= " AND price <= ?";
            $params[] = $filters['max_price'];
        }
        
        // Филтриране по площ
        if (!empty($filters['min_area'])) {
            $query .= " AND area >= ?";
            $params[] = $filters['min_area'];
        }
        if (!empty($filters['max_area'])) {
            $query .= " AND area <= ?";
            $params[] = $filters['max_area'];
        }
        
        // Филтриране по локация
        if (!empty($filters['location'])) {
            $query .= " AND (location LIKE ? OR address LIKE ?)";
            $searchTerm = "%{$filters['location']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Сортиране
        $query .= match ($filters['sort'] ?? 'date_desc') {
            'date_asc' => " ORDER BY created_at ASC",
            'price_desc' => " ORDER BY price DESC",
            'price_asc' => " ORDER BY price ASC",
            'area_desc' => " ORDER BY area DESC",
            'area_asc' => " ORDER BY area ASC",
            default => " ORDER BY created_at DESC"
        };
        
        // Добавяне на лимит и отместване за пагинация
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        $properties = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $properties[] = $this->createPropertyFromRow($row);
        }
        
        return $properties;
    }

    public function findById(int $id): ?Property
    {
        $stmt = $this->db->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        
        return $this->createPropertyFromRow($row);
    }

    public function countByFilters(array $filters = []): int
    {
        $query = "SELECT COUNT(*) FROM properties WHERE 1=1";
        $params = [];
        
        // Филтриране по тип
        if (!empty($filters['type'])) {
            $query .= " AND type = ?";
            $params[] = $filters['type'];
        }
        
        // Филтриране по статус
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        // Филтриране по цена
        if (!empty($filters['min_price'])) {
            $query .= " AND price >= ?";
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $query .= " AND price <= ?";
            $params[] = $filters['max_price'];
        }
        
        // Филтриране по площ
        if (!empty($filters['min_area'])) {
            $query .= " AND area >= ?";
            $params[] = $filters['min_area'];
        }
        if (!empty($filters['max_area'])) {
            $query .= " AND area <= ?";
            $params[] = $filters['max_area'];
        }
        
        // Филтриране по локация
        if (!empty($filters['location'])) {
            $query .= " AND (location LIKE ? OR address LIKE ?)";
            $searchTerm = "%{$filters['location']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    }

    public function getAvailableTypes(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT type FROM properties");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAvailableStatuses(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT status FROM properties");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPropertyImages(int $propertyId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM property_images WHERE property_id = :property_id ORDER BY id ASC");
        $stmt->execute([':property_id' => $propertyId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function prepareFields(array $data): array
    {
        $allowedFields = [
            'title_bg', 'title_de', 'title_ru',
            'description_bg', 'description_de', 'description_ru',
            'type', 'status', 'price', 'area',
            'location', 'address', 'coordinates',
            'built_year', 'last_renovation', 'floors',
            'rooms', 'bathrooms', 'parking_spaces',
            'features', 'images'
        ];
        
        $fields = array_intersect_key($data, array_flip($allowedFields));
        
        // Handle JSON fields
        if (isset($fields['features']) && is_array($fields['features'])) {
            $fields['features'] = json_encode($fields['features']);
        }
        if (isset($fields['images']) && is_array($fields['images'])) {
            $fields['images'] = json_encode($fields['images']);
        }
        
        return $fields;
    }

    private function createPropertyFromRow(array $row): Property
    {
        $property = new Property();
        
        foreach ($row as $key => $value) {
            if ($key === 'features' || $key === 'images') {
                $value = json_decode($value, true) ?? [];
            }
            $property->$key = $value;
        }
        
        return $property;
    }

    public function findSimilar(string $type, float $price, float $area, int $excludeId, int $limit = 4): array
    {
        $query = "SELECT * FROM properties 
                 WHERE type = ? 
                 AND id != ?
                 AND (
                     (price BETWEEN ? * 0.8 AND ? * 1.2)
                     OR (area BETWEEN ? * 0.8 AND ? * 1.2)
                 )
                 ORDER BY 
                     CASE 
                         WHEN price BETWEEN ? * 0.9 AND ? * 1.1 
                         AND area BETWEEN ? * 0.9 AND ? * 1.1 
                         THEN 1
                         ELSE 2
                     END,
                     ABS(price - ?) + ABS(area - ?)
                 LIMIT ?";
        
        $params = [
            $type,
            $excludeId,
            $price, $price,
            $area, $area,
            $price, $price,
            $area, $area,
            $price, $area,
            $limit
        ];
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        $properties = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $properties[] = $this->createPropertyFromRow($row);
        }
        
        return $properties;
    }

    public function getDistinctLocations(): array
    {
        $stmt = $this->db->query('SELECT DISTINCT location FROM properties WHERE location IS NOT NULL ORDER BY location');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function findAll(array $filters = [], array $sorting = [], int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Базова WHERE клауза за филтрите
        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($filters['type'])) {
            $whereClause .= " AND p.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $whereClause .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['min_area'])) {
            $whereClause .= " AND p.area >= ?";
            $params[] = $filters['min_area'];
        }

        if (!empty($filters['max_area'])) {
            $whereClause .= " AND p.area <= ?";
            $params[] = $filters['max_area'];
        }

        if (!empty($filters['min_price'])) {
            $whereClause .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $whereClause .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }

        if (!empty($filters['rooms'])) {
            $whereClause .= " AND p.rooms = ?";
            $params[] = $filters['rooms'];
        }

        // Подготовка на ORDER BY клаузата
        $orderClause = " ORDER BY p.created_at DESC";
        if (!empty($sorting)) {
            $allowedFields = ['price', 'area', 'rooms', 'created_at'];
            $allowedDirections = ['ASC', 'DESC'];
            
            $orderClauses = [];
            foreach ($sorting as $field => $direction) {
                if (in_array($field, $allowedFields) && in_array(strtoupper($direction), $allowedDirections)) {
                    $orderClauses[] = "p.{$field} " . strtoupper($direction);
                }
            }
            
            if (!empty($orderClauses)) {
                $orderClause = " ORDER BY " . implode(', ', $orderClauses);
            }
        }

        // Заявка за броене на общия брой резултати
        $countSql = "SELECT COUNT(*) as total FROM properties p {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Основна заявка за вземане на данните
        $sql = "SELECT p.*, 
                (SELECT pi.image_path 
                 FROM property_images pi 
                 WHERE pi.property_id = p.id 
                 ORDER BY CASE WHEN pi.is_main = 1 THEN 0 ELSE 1 END, pi.id ASC 
                 LIMIT 1) as image_path
                FROM properties p 
                {$whereClause}
                {$orderClause}
                LIMIT ? OFFSET ?";

        // Добавяне на параметрите за LIMIT и OFFSET
        $params[] = $perPage;
        $params[] = $offset;

        // Изпълнение на основната заявка
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $properties,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
} 
