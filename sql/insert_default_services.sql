-- Добавяне на примерни услуги
INSERT INTO `services` (
    title_bg, title_en, title_de, title_ru,
    description_bg, description_en, description_de, description_ru,
    icon, sort_order
) VALUES
(
    'Отдаване под наем', 'Property Rental', 'Vermietung', 'Аренда недвижимости',
    'Професионално управление на наеми на индустриални имоти', 
    'Professional management of industrial property rentals',
    'Professionelle Verwaltung von Industrieimmobilienvermietungen',
    'Профессиональное управление арендой промышленной недвижимости',
    'bi-building', 1
),
(
    'Продажба', 'Property Sale', 'Verkauf', 'Продажа недвижимости',
    'Експертно консултиране при покупко-продажба на индустриални имоти',
    'Expert consulting in industrial property sales',
    'Expertenberatung beim Verkauf von Industrieimmobilien',
    'Экспертные консультации по продаже промышленной недвижимости',
    'bi-cash-coin', 2
),
(
    'Управление на имоти', 'Property Management', 'Immobilienverwaltung', 'Управление недвижимостью',
    'Цялостно управление и поддръжка на индустриални имоти',
    'Complete management and maintenance of industrial properties',
    'Komplette Verwaltung und Wartung von Industrieimmobilien',
    'Комплексное управление и обслуживание промышленной недвижимости',
    'bi-gear', 3
),
(
    'Консултации', 'Consulting', 'Beratung', 'Консультации',
    'Професионални консултации за инвестиции в индустриални имоти',
    'Professional consulting for industrial property investments',
    'Professionelle Beratung für Investitionen in Industrieimmobilien',
    'Профессиональные консультации по инвестициям в промышленную недвижимость',
    'bi-chat-dots', 4
); 