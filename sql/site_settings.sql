-- Таблица за основните настройки на сайта
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица за контактна информация с поддръжка на множество езици
CREATE TABLE IF NOT EXISTS contact_information (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL, -- phone, email, address, social_link, working_hours
    value_bg TEXT NOT NULL,
    value_en TEXT,
    value_de TEXT,
    value_ru TEXT,
    icon VARCHAR(50), -- Bootstrap icon class
    link VARCHAR(255), -- URL за социални мрежи
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Първоначални настройки
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_logo', ''),
('site_name', 'WEBImobilien'),
('footer_text', ''),
('google_maps_api_key', ''),
('recaptcha_site_key', ''),
('recaptcha_secret_key', '');

-- Примерни контакти
INSERT INTO contact_information (type, value_bg, value_en, value_de, value_ru, icon, sort_order) VALUES
('phone', '+359 888 123 456', '+359 888 123 456', '+359 888 123 456', '+359 888 123 456', 'bi-telephone', 1),
('email', 'contact@webimobilien.com', 'contact@webimobilien.com', 'contact@webimobilien.com', 'contact@webimobilien.com', 'bi-envelope', 2),
('address', 'ул. България 102, София 1680, България', 'Bulgaria Blvd. 102, Sofia 1680, Bulgaria', 'Bulgaria Blvd. 102, Sofia 1680, Bulgarien', 'Bulgaria Blvd. 102, София 1680, Болгария', 'bi-geo-alt', 3),
('working_hours', 'Пон-Пет: 9:00-18:00', 'Mon-Fri: 9:00-18:00', 'Mo-Fr: 9:00-18:00', 'Пн-Пт: 9:00-18:00', 'bi-clock', 4),
('social_link', 'Facebook', 'Facebook', 'Facebook', 'Facebook', 'bi-facebook', 5);

-- Добавяне на записи за социални мрежи
INSERT INTO contact_information (type, value_bg, value_en, value_de, value_ru, icon, link, sort_order, is_active) VALUES
('facebook', '', '', '', '', 'bi-facebook', '', 5, 1),
('instagram', '', '', '', '', 'bi-instagram', '', 6, 1),
('linkedin', '', '', '', '', 'bi-linkedin', '', 7, 1),
('twitter', '', '', '', '', 'bi-twitter', '', 8, 1); 