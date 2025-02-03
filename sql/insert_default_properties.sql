-- Добавяне на примерни имоти
INSERT INTO `properties` (
    title_bg, title_en, title_de, title_ru,
    description_bg, description_en, description_de, description_ru,
    price, area, type, status,
    location_bg, location_en, location_de, location_ru,
    address, latitude, longitude, featured
) VALUES
(
    'Складова база София', 'Warehouse Sofia', 'Lagerhaus Sofia', 'Складской комплекс София',
    'Модерна складова база в София с отлична локация', 'Modern warehouse in Sofia with excellent location', 'Modernes Lagerhaus in Sofia mit ausgezeichneter Lage', 'Современный складской комплекс в Софии с отличным расположением',
    500000, 1500, 'warehouse', 'available',
    'София', 'Sofia', 'Sofia', 'София',
    'бул. Цариградско шосе 139', 42.6977, 23.3219, true
),
(
    'Индустриален парк Бургас', 'Industrial Park Burgas', 'Industriepark Burgas', 'Индустриальный парк Бургас',
    'Индустриален парк с всички комуникации', 'Industrial park with all communications', 'Industriepark mit allen Kommunikationen', 'Индустриальный парк со всеми коммуникациями',
    750000, 2500, 'industrial', 'available',
    'Бургас', 'Burgas', 'Burgas', 'Бургас',
    'Промишлена зона Север', 42.5047, 27.4626, true
),
(
    'Логистичен хъб Русе', 'Logistics Hub Ruse', 'Logistik-Hub Ruse', 'Логистический хаб Русе',
    'Стратегически разположен логистичен център', 'Strategically located logistics center', 'Strategisch gelegenes Logistikzentrum', 'Стратегически расположенный логистический центр',
    600000, 2000, 'logistics', 'available',
    'Русе', 'Ruse', 'Ruse', 'Русе',
    'Източна промишлена зона', 43.8492, 25.9533, false
);

-- Добавяне на примерни снимки
INSERT INTO property_images (property_id, image_path, is_main) VALUES 
(1, 'warehouse1.jpg', 1),
(1, 'warehouse2.jpg', 0),
(1, 'warehouse3.jpg', 0),
(2, 'industrial1.jpg', 1),
(2, 'industrial2.jpg', 0),
(3, 'logistics1.jpg', 1),
(3, 'logistics2.jpg', 0);

-- Добавяне на примерни характеристики
INSERT INTO property_features (property_id, feature_bg, feature_en, feature_de, feature_ru) VALUES 
(1, 'Рампи за товарене', 'Loading ramps', 'Laderampen', 'Погрузочные рампы'),
(1, 'Охрана 24/7', '24/7 Security', '24/7 Sicherheit', 'Охрана 24/7'),
(2, 'Собствен трафопост', 'Own transformer', 'Eigener Transformator', 'Собственная трансформаторная подстанция'),
(2, 'Газификация', 'Gas supply', 'Gasversorgung', 'Газоснабжение'),
(3, 'Жп достъп', 'Railway access', 'Bahnanschluss', 'Железнодорожный доступ'),
(3, 'Митническа обработка', 'Customs clearance', 'Zollabfertigung', 'Таможенное оформление');

-- Добавяне на примерни блог постове
INSERT INTO `blog_posts` (
    title_bg, title_en, title_de, title_ru,
    content_bg, content_en, content_de, content_ru,
    slug, is_published
) VALUES
(
    'Тенденции в индустриалните имоти 2024', 
    'Industrial Real Estate Trends 2024',
    'Trends in Industrieimmobilien 2024',
    'Тенденции промышленной недвижимости 2024',
    'Анализ на най-важните тенденции в сектора на индустриалните имоти през 2024 година.',
    'Analysis of the most important trends in the industrial real estate sector in 2024.',
    'Analyse der wichtigsten Trends im Industrieimmobiliensektor im Jahr 2024.',
    'Анализ важнейших тенденций в секторе промышленной недвижимости в 2024 году.',
    'industrial-real-estate-trends-2024',
    true
),
(
    'Устойчиво развитие в логистиката',
    'Sustainable Development in Logistics',
    'Nachhaltige Entwicklung in der Logistik',
    'Устойчивое развитие в логистике',
    'Как устойчивото развитие променя сектора на логистичните имоти.',
    'How sustainable development is changing the logistics property sector.',
    'Wie nachhaltige Entwicklung den Logistikimmobiliensektor verändert.',
    'Как устойчивое развитие меняет сектор логистической недвижимости.',
    'sustainable-development-logistics',
    true
);

-- Добавяне на примерни PDF файлове
INSERT INTO `property_pdf_files` (property_id, file_name, file_path, file_size, language) VALUES 
(1, 'warehouse-sofia-bg.pdf', 'flyers/warehouse-sofia-bg.pdf', 1024, 'bg'),
(1, 'warehouse-sofia-en.pdf', 'flyers/warehouse-sofia-en.pdf', 1024, 'en'),
(1, 'warehouse-sofia-de.pdf', 'flyers/warehouse-sofia-de.pdf', 1024, 'de'),
(1, 'warehouse-sofia-ru.pdf', 'flyers/warehouse-sofia-ru.pdf', 1024, 'ru'),
(2, 'industrial-park-burgas-bg.pdf', 'flyers/industrial-park-burgas-bg.pdf', 1024, 'bg'),
(2, 'industrial-park-burgas-en.pdf', 'flyers/industrial-park-burgas-en.pdf', 1024, 'en'),
(2, 'industrial-park-burgas-de.pdf', 'flyers/industrial-park-burgas-de.pdf', 1024, 'de'),
(2, 'industrial-park-burgas-ru.pdf', 'flyers/industrial-park-burgas-ru.pdf', 1024, 'ru'),
(3, 'logistics-hub-ruse-bg.pdf', 'flyers/logistics-hub-ruse-bg.pdf', 1024, 'bg'),
(3, 'logistics-hub-ruse-en.pdf', 'flyers/logistics-hub-ruse-en.pdf', 1024, 'en'),
(3, 'logistics-hub-ruse-de.pdf', 'flyers/logistics-hub-ruse-de.pdf', 1024, 'de'),
(3, 'logistics-hub-ruse-ru.pdf', 'flyers/logistics-hub-ruse-ru.pdf', 1024, 'ru'); 