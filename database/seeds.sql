-- Изключване на проверката на външните ключове
SET FOREIGN_KEY_CHECKS = 0;

-- Изтриване на съществуващи данни
TRUNCATE TABLE property_images;
TRUNCATE TABLE properties;

-- Вмъкване на тестови имоти
INSERT INTO properties (
    title_bg, 
    title_de, 
    title_ru,
    title_en,
    type,
    status,
    price, 
    area,
    location_bg,
    location_de,
    location_ru,
    location_en,
    address,
    featured,
    created_at
) VALUES 
(
    'Индустриален имот София - Запад',
    'Industrieimmobilie Sofia - West',
    'Промышленная недвижимость София - Запад',
    'Industrial Property Sofia - West',
    'warehouse',
    'available',
    250000,
    1500,
    'София',
    'Sofia',
    'София',
    'Sofia',
    'бул. Сливница 150',
    1,
    NOW()
),
(
    'Логистичен център Пловдив',
    'Logistikzentrum Plovdiv',
    'Логистический центр Пловдив',
    'Logistics Center Plovdiv',
    'warehouse',
    'available',
    350000,
    2500,
    'Пловдив',
    'Plovdiv',
    'Пловдив',
    'Plovdiv',
    'ул. Васил Левски 55',
    1,
    NOW()
),
(
    'Производствена база Варна',
    'Produktionsanlage Varna',
    'Производственная база Варна',
    'Manufacturing Facility Varna',
    'industrial',
    'available',
    450000,
    3000,
    'Варна',
    'Varna',
    'Варна',
    'Varna',
    'ул. Девня 10',
    1,
    NOW()
),
(
    'Складова база София',
    'Lagerhalle Sofia',
    'Складской комплекс София',
    'Warehouse Complex Sofia',
    'warehouse',
    'available',
    200000,
    1200,
    'София',
    'Sofia',
    'София',
    'Sofia',
    'ул. Околовръстен път 15',
    0,
    NOW()
),
(
    'Индустриален парк Бургас',
    'Industriepark Burgas',
    'Индустриальный парк Бургас',
    'Industrial Park Burgas',
    'industrial',
    'available',
    550000,
    4000,
    'Бургас',
    'Burgas',
    'Бургас',
    'Burgas',
    'Промишлена зона Север',
    1,
    NOW()
),
(
    'Логистичен хъб Русе',
    'Logistik-Hub Ruse',
    'Логистический хаб Русе',
    'Logistics Hub Ruse',
    'warehouse',
    'available',
    300000,
    2000,
    'Русе',
    'Ruse',
    'Русе',
    'Ruse',
    'ул. Пристанищна 8',
    1,
    NOW()
);

-- Вмъкване на тестови снимки
INSERT INTO property_images (
    property_id, 
    image_path, 
    is_main, 
    created_at
) VALUES 
(1, 'warehouse1.jpg', 1, NOW()),
(1, 'warehouse1_2.jpg', 0, NOW()),
(2, 'logistics1.jpg', 1, NOW()),
(2, 'logistics1_2.jpg', 0, NOW()),
(3, 'manufacturing1.jpg', 1, NOW()),
(3, 'manufacturing1_2.jpg', 0, NOW()),
(4, 'warehouse2.jpg', 1, NOW()),
(5, 'industrial1.jpg', 1, NOW()),
(6, 'logistics2.jpg', 1, NOW());

-- Включване на проверката на външните ключове
SET FOREIGN_KEY_CHECKS = 1; 