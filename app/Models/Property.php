<?php

namespace App\Models;

class Property
{
    public const TYPES = [
        'apartment' => 'Apartment',
        'house' => 'House',
        'villa' => 'Villa',
        'office' => 'Office',
        'commercial' => 'Commercial',
        'land' => 'Land'
    ];

    public const STATUSES = [
        'available' => 'Available',
        'sold' => 'Sold',
        'rented' => 'Rented',
        'reserved' => 'Reserved'
    ];

    public ?int $id = null;
    public string $title_bg = '';
    public string $title_de = '';
    public string $title_ru = '';
    public string $description_bg = '';
    public string $description_de = '';
    public string $description_ru = '';
    public string $type = '';
    public string $status = '';
    public float $price = 0.0;
    public float $area = 0.0;
    public string $location = '';
    public string $address = '';
    public string $coordinates = '';
    public ?int $built_year = null;
    public ?int $last_renovation = null;
    public ?int $floors = null;
    public ?int $rooms = null;
    public ?int $bathrooms = null;
    public ?int $parking_spaces = null;
    public array $features = [];
    public array $images = [];
    public string $created_at = '';
    public string $updated_at = '';

    public function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
} 