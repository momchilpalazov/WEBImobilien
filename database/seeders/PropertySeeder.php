<?php

require_once 'Seeder.php';
require_once __DIR__ . '/ImageGenerator.php';

class PropertySeeder extends Seeder {
    public function run() {
        $faker = $this->faker();
        $types = ['industrial', 'office', 'retail', 'warehouse'];
        $statuses = ['available', 'rented', 'sold'];
        
        // Вземаме ID-тата на потребителите
        $users = $this->db->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
        
        for ($i = 0; $i < 20; $i++) {
            $title = $faker->words(3, true);
            $location = $faker->city();
            
            $this->db->prepare("
                INSERT INTO properties (
                    title_bg, title_de, title_ru,
                    description_bg, description_de, description_ru,
                    type, status, price, area,
                    location_bg, location_de, location_ru,
                    address, lat, lng,
                    built_year, floors, parking_spots,
                    ceiling_height, office_space, storage_space,
                    production_space, heating, electricity,
                    water_supply, security, loading_docks,
                    featured, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $title, $title, $title,
                $faker->paragraphs(3, true),
                $faker->paragraphs(3, true),
                $faker->paragraphs(3, true),
                $types[array_rand($types)],
                $statuses[array_rand($statuses)],
                $faker->numberBetween(50000, 2000000),
                $faker->numberBetween(100, 5000),
                $location, $location, $location,
                $faker->address(),
                $faker->latitude(41.5, 43.5),
                $faker->longitude(22.5, 28.5),
                $faker->numberBetween(1960, 2020),
                $faker->numberBetween(1, 10),
                $faker->numberBetween(5, 100),
                $faker->randomFloat(2, 2.5, 6),
                $faker->numberBetween(50, 1000),
                $faker->numberBetween(50, 1000),
                $faker->numberBetween(100, 3000),
                $faker->boolean(),
                $faker->boolean(),
                $faker->boolean(),
                $faker->boolean(),
                $faker->numberBetween(0, 10),
                $faker->boolean(20),
                $users[array_rand($users)]
            ]);
        }
    }
} 