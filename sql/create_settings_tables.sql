-- Изтриване на съществуващите таблици, ако има нужда
DROP TABLE IF EXISTS `contact_information`;
DROP TABLE IF EXISTS `site_settings`;

-- Таблица за основните настройки на сайта
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `setting_key` VARCHAR(50) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица за контактна информация с поддръжка на множество езици
CREATE TABLE IF NOT EXISTS `contact_information` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `type` VARCHAR(50) NOT NULL,
    `value_bg` TEXT NOT NULL,
    `value_en` TEXT,
    `value_de` TEXT,
    `value_ru` TEXT,
    `icon` VARCHAR(50),
    `link` VARCHAR(255),
    `sort_order` INT DEFAULT 0,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Първоначални настройки
INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_logo', ''),
('site_name', 'WEBImobilien'),
('footer_text', ''),
('google_maps_api_key', ''),
('recaptcha_site_key', ''),
('recaptcha_secret_key', '');

-- Изтриване на съществуващите контакти
DELETE FROM `contact_information`;

-- Примерни контакти
INSERT INTO `contact_information` (`type`, `value_bg`, `value_en`, `value_de`, `value_ru`, `icon`, `sort_order`) VALUES
('phone', '+359 888 123 456', '+359 888 123 456', '+359 888 123 456', '+359 888 123 456', 'bi-telephone', 1),
('email', 'contact@webimobilien.com', 'contact@webimobilien.com', 'contact@webimobilien.com', 'contact@webimobilien.com', 'bi-envelope', 2),
('address', 'ул. България 102, София 1680, България', 'Bulgaria Blvd. 102, Sofia 1680, Bulgaria', 'Bulgaria Blvd. 102, Sofia 1680, Bulgarien', 'Bulgaria Blvd. 102, София 1680, Болгария', 'bi-geo-alt', 3),
('working_hours', 'Пон-Пет: 9:00-18:00', 'Mon-Fri: 9:00-18:00', 'Mo-Fr: 9:00-18:00', 'Пн-Пт: 9:00-18:00', 'bi-clock', 4); 