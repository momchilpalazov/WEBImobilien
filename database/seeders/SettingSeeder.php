<?php
require_once __DIR__ . '/Seeder.php';

class SettingSeeder extends Seeder {
    public function run() {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'Industrial Properties',
                'type' => 'string',
                'group_name' => 'general'
            ],
            [
                'key' => 'contact_email',
                'value' => 'contact@example.com',
                'type' => 'string',
                'group_name' => 'contact'
            ],
            [
                'key' => 'contact_phone',
                'value' => '+359 888 123 456',
                'type' => 'string',
                'group_name' => 'contact'
            ],
            [
                'key' => 'social_links',
                'value' => json_encode([
                    'facebook' => 'https://facebook.com/example',
                    'linkedin' => 'https://linkedin.com/company/example'
                ]),
                'type' => 'json',
                'group_name' => 'social'
            ]
        ];
        
        foreach ($settings as $setting) {
            $this->db->prepare("
                INSERT INTO settings (`key`, value, type, group_name)
                VALUES (?, ?, ?, ?)
            ")->execute([
                $setting['key'],
                $setting['value'],
                $setting['type'],
                $setting['group_name']
            ]);
        }
    }
} 