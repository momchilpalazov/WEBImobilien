<?php

$services_translations = [
    'menu' => [
        'services' => [
            'bg' => 'Услуги',
            'en' => 'Services',
            'de' => 'Dienstleistungen',
            'ru' => 'Услуги'
        ]
    ],
    'title' => [
        'bg' => 'Нашите Услуги',
        'en' => 'Our Services',
        'de' => 'Unsere Dienstleistungen',
        'ru' => 'Наши Услуги'
    ]
];

// Добавяме преводите към глобалния масив с преводи
if (isset($translations)) {
    $translations['services'] = $services_translations;
} else {
    $translations = ['services' => $services_translations];
} 