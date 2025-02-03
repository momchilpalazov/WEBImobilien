<?php

namespace App\Repositories;

use App\Interfaces\PropertyRepositoryInterface;
use PDO;

class MySQLPropertyRepository implements PropertyRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllProperties(): array
    {
        $sql = "
            SELECT p.*, 
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image
            FROM properties p
            WHERE p.active = 1
            ORDER BY p.created_at DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getPropertyById(int $id): ?array
    {
        return $this->findById($id);
    }

    public function getPropertiesByStatus(string $status): array
    {
        $sql = "
            SELECT p.*, 
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image
            FROM properties p
            WHERE p.status = :status AND p.active = 1
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getPropertiesByType(string $type): array
    {
        $sql = "
            SELECT p.*, 
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image
            FROM properties p
            WHERE p.type = :type AND p.active = 1
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':type', $type);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        try {
            $this->db->beginTransaction();

            $sql = "
                INSERT INTO properties (
                    title_bg, title_de, title_ru,
                    description_bg, description_de, description_ru,
                    type, status, price, area,
                    location_bg, location_de, location_ru,
                    address, lat, lng,
                    built_year, last_renovation, floors, parking_spots,
                    ceiling_height, office_space, storage_space, production_space,
                    heating, electricity, water_supply, security, loading_docks,
                    virtual_tour_url, featured,
                    created_by, created_at, updated_at
                ) VALUES (
                    :title_bg, :title_de, :title_ru,
                    :description_bg, :description_de, :description_ru,
                    :type, :status, :price, :area,
                    :location_bg, :location_de, :location_ru,
                    :address, :lat, :lng,
                    :built_year, :last_renovation, :floors, :parking_spots,
                    :ceiling_height, :office_space, :storage_space, :production_space,
                    :heating, :electricity, :water_supply, :security, :loading_docks,
                    :virtual_tour_url, :featured,
                    :created_by, NOW(), NOW()
                )
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'title_bg' => $data['title_bg'],
                'title_de' => $data['title_de'],
                'title_ru' => $data['title_ru'],
                'description_bg' => $data['description_bg'] ?? null,
                'description_de' => $data['description_de'] ?? null,
                'description_ru' => $data['description_ru'] ?? null,
                'type' => $data['type'],
                'status' => $data['status'],
                'price' => $data['price'],
                'area' => $data['area'],
                'location_bg' => $data['location_bg'],
                'location_de' => $data['location_de'],
                'location_ru' => $data['location_ru'],
                'address' => $data['address'],
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
                'built_year' => $data['built_year'] ?? null,
                'last_renovation' => $data['last_renovation'] ?? null,
                'floors' => $data['floors'] ?? null,
                'parking_spots' => $data['parking_spots'] ?? null,
                'ceiling_height' => $data['ceiling_height'] ?? null,
                'office_space' => $data['office_space'] ?? null,
                'storage_space' => $data['storage_space'] ?? null,
                'production_space' => $data['production_space'] ?? null,
                'heating' => $data['heating'] ?? false,
                'electricity' => $data['electricity'] ?? false,
                'water_supply' => $data['water_supply'] ?? false,
                'security' => $data['security'] ?? false,
                'loading_docks' => $data['loading_docks'] ?? 0,
                'virtual_tour_url' => $data['virtual_tour_url'] ?? null,
                'featured' => $data['featured'] ?? false,
                'created_by' => $data['created_by'] ?? null
            ]);

            $propertyId = $this->db->lastInsertId();

            // Handle images if provided
            if (!empty($data['images'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO property_images (property_id, image_path, is_main, sort_order)
                    VALUES (:property_id, :image_path, :is_main, :sort_order)
                ");

                foreach ($data['images'] as $index => $image) {
                    $stmt->execute([
                        'property_id' => $propertyId,
                        'image_path' => $image['path'],
                        'is_main' => $index === 0 ? 1 : 0,
                        'sort_order' => $index
                    ]);
                }
            }

            $this->db->commit();
            return $propertyId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "
                UPDATE properties SET
                    title_bg = :title_bg,
                    title_de = :title_de,
                    title_ru = :title_ru,
                    description_bg = :description_bg,
                    description_de = :description_de,
                    description_ru = :description_ru,
                    type = :type,
                    status = :status,
                    price = :price,
                    area = :area,
                    location_bg = :location_bg,
                    location_de = :location_de,
                    location_ru = :location_ru,
                    address = :address,
                    lat = :lat,
                    lng = :lng,
                    built_year = :built_year,
                    last_renovation = :last_renovation,
                    floors = :floors,
                    parking_spots = :parking_spots,
                    ceiling_height = :ceiling_height,
                    office_space = :office_space,
                    storage_space = :storage_space,
                    production_space = :production_space,
                    heating = :heating,
                    electricity = :electricity,
                    water_supply = :water_supply,
                    security = :security,
                    loading_docks = :loading_docks,
                    virtual_tour_url = :virtual_tour_url,
                    featured = :featured,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge($data, [
                'id' => $id,
                'updated_by' => $data['updated_by'] ?? null
            ]));

            // Handle images if provided
            if (!empty($data['images'])) {
                // Delete existing images
                $stmt = $this->db->prepare("DELETE FROM property_images WHERE property_id = ?");
                $stmt->execute([$id]);

                // Insert new images
                $stmt = $this->db->prepare("
                    INSERT INTO property_images (property_id, image_path, is_main, sort_order)
                    VALUES (:property_id, :image_path, :is_main, :sort_order)
                ");

                foreach ($data['images'] as $index => $image) {
                    $stmt->execute([
                        'property_id' => $id,
                        'image_path' => $image['path'],
                        'is_main' => $index === 0 ? 1 : 0,
                        'sort_order' => $index
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            // Delete images
            $stmt = $this->db->prepare("DELETE FROM property_images WHERE property_id = ?");
            $stmt->execute([$id]);

            // Delete property
            $stmt = $this->db->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findSimilar(string $type, float $price, float $area, int $excludeId, int $limit = 4): array
    {
        // Calculate price range (±20%)
        $minPrice = $price * 0.8;
        $maxPrice = $price * 1.2;
        
        // Calculate area range (±20%)
        $minArea = $area * 0.8;
        $maxArea = $area * 1.2;

        $sql = "
            SELECT p.*, 
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image,
                ABS(p.price - :price) + ABS(p.area - :area) as similarity_score
            FROM properties p
            WHERE p.type = :type
            AND p.id != :exclude_id
            AND p.price BETWEEN :min_price AND :max_price
            AND p.area BETWEEN :min_area AND :max_area
            AND p.status = 'available'
            ORDER BY similarity_score ASC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':area', $area);
        $stmt->bindValue(':exclude_id', $excludeId);
        $stmt->bindValue(':min_price', $minPrice);
        $stmt->bindValue(':max_price', $maxPrice);
        $stmt->bindValue(':min_area', $minArea);
        $stmt->bindValue(':max_area', $maxArea);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findByFilters(array $filters, int $offset, int $limit): array
    {
        $sql = "
            SELECT p.*, 
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image
            FROM properties p
            WHERE 1=1
        ";
        $params = [];

        // Add type filter
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $sql .= " AND p.type = :type";
            $params[':type'] = $filters['type'];
        }

        // Add status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }

        // Add price range filter
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }

        // Add area range filter
        if (!empty($filters['min_area'])) {
            $sql .= " AND p.area >= :min_area";
            $params[':min_area'] = $filters['min_area'];
        }
        if (!empty($filters['max_area'])) {
            $sql .= " AND p.area <= :max_area";
            $params[':max_area'] = $filters['max_area'];
        }

        // Add location filter
        if (!empty($filters['location'])) {
            $sql .= " AND (
                p.location_bg LIKE :location OR 
                p.location_de LIKE :location OR 
                p.location_ru LIKE :location OR 
                p.address LIKE :location
            )";
            $params[':location'] = '%' . $filters['location'] . '%';
        }

        // Add features filters
        if (!empty($filters['features'])) {
            foreach ($filters['features'] as $feature) {
                switch ($feature) {
                    case 'heating':
                    case 'electricity':
                    case 'water_supply':
                    case 'security':
                        $sql .= " AND p.$feature = 1";
                        break;
                }
            }
        }

        // Add built year range
        if (!empty($filters['min_year'])) {
            $sql .= " AND p.built_year >= :min_year";
            $params[':min_year'] = $filters['min_year'];
        }
        if (!empty($filters['max_year'])) {
            $sql .= " AND p.built_year <= :max_year";
            $params[':max_year'] = $filters['max_year'];
        }

        // Add featured filter
        if (isset($filters['featured']) && $filters['featured']) {
            $sql .= " AND p.featured = 1";
        }

        // Add sorting
        $sql .= match ($filters['sort'] ?? 'newest') {
            'price_asc' => " ORDER BY p.price ASC",
            'price_desc' => " ORDER BY p.price DESC",
            'area_asc' => " ORDER BY p.area ASC",
            'area_desc' => " ORDER BY p.area DESC",
            default => " ORDER BY p.created_at DESC"
        };

        // Add pagination
        $sql .= " LIMIT :offset, :limit";
        $params[':offset'] = $offset;
        $params[':limit'] = $limit;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countByFilters(array $filters): int
    {
        $sql = "SELECT COUNT(*) FROM properties p WHERE 1=1";
        $params = [];

        // Add type filter
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $sql .= " AND p.type = :type";
            $params[':type'] = $filters['type'];
        }

        // Add status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }

        // Add price range filter
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }

        // Add area range filter
        if (!empty($filters['min_area'])) {
            $sql .= " AND p.area >= :min_area";
            $params[':min_area'] = $filters['min_area'];
        }
        if (!empty($filters['max_area'])) {
            $sql .= " AND p.area <= :max_area";
            $params[':max_area'] = $filters['max_area'];
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function getAvailableTypes(): array
    {
        $sql = "SELECT DISTINCT type FROM properties ORDER BY type ASC";
        return $this->db->query($sql)->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getAvailableStatuses(): array
    {
        $sql = "SELECT DISTINCT status FROM properties ORDER BY status ASC";
        return $this->db->query($sql)->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function findById(int $id): ?array
    {
        $sql = "
            SELECT p.*, 
                (SELECT image_path FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image,
                GROUP_CONCAT(DISTINCT pi.image_path ORDER BY pi.sort_order ASC) as additional_images
            FROM properties p
            LEFT JOIN property_images pi ON p.id = pi.property_id
            WHERE p.id = :id
            GROUP BY p.id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        $property = $stmt->fetch();
        if (!$property) {
            return null;
        }

        // Convert additional images string to array
        if ($property['additional_images']) {
            $property['additional_images'] = explode(',', $property['additional_images']);
        } else {
            $property['additional_images'] = [];
        }

        return $property;
    }

    public function getPropertyImages(int $propertyId): array
    {
        $sql = "
            SELECT * FROM property_images 
            WHERE property_id = :property_id 
            ORDER BY is_main DESC, sort_order ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':property_id', $propertyId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
} 
