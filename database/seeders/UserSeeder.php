<?php

require_once __DIR__ . '/Seeder.php';

class UserSeeder extends Seeder {
    public function run() {
        // Създаване на админ потребител
        $this->db->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, role, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            'admin',
            'admin@example.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            'Admin',
            'User',
            'admin',
            'active'
        ]);
        
        // Създаване на тестови потребители
        $faker = $this->faker();
        $roles = ['manager', 'agent'];
        
        for ($i = 0; $i < 5; $i++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $username = strtolower($firstName . '.' . $lastName);
            
            $this->db->prepare("
                INSERT INTO users (username, email, password, first_name, last_name, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $username,
                $faker->email(),
                password_hash('password123', PASSWORD_DEFAULT),
                $firstName,
                $lastName,
                $roles[array_rand($roles)],
                'active'
            ]);
        }
    }
} 