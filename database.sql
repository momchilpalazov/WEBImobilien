-- Създаване на базата данни
CREATE DATABASE IF NOT EXISTS industrial_properties CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE industrial_properties;

-- Таблица за имоти
CREATE TABLE properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('industrial', 'warehouse', 'logistics') NOT NULL,
    status ENUM('sale', 'rent') NOT NULL,
    
    -- Многоезични полета
    title_bg VARCHAR(255) NOT NULL,
    title_de VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) NOT NULL,
    
    description_bg TEXT NOT NULL,
    description_de TEXT NOT NULL,
    description_ru TEXT NOT NULL,
    
    -- Основни характеристики
    price DECIMAL(12,2) NOT NULL,
    area DECIMAL(10,2) NOT NULL,
    location_bg VARCHAR(255) NOT NULL,
    location_de VARCHAR(255) NOT NULL,
    location_ru VARCHAR(255) NOT NULL,
    
    -- Координати за картата
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    
    -- Допълнителни данни
    featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица за снимки на имотите
CREATE TABLE property_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Таблица за услуги
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('company_registration', 'recruitment', 'consulting') NOT NULL,
    
    title_bg VARCHAR(255) NOT NULL,
    title_de VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) NOT NULL,
    
    description_bg TEXT NOT NULL,
    description_de TEXT NOT NULL,
    description_ru TEXT NOT NULL,
    
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица за запитвания
CREATE TABLE inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT,
    service_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    message TEXT NOT NULL,
    status ENUM('new', 'in_progress', 'completed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
);

-- Таблица за администратори
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица за характеристики на имотите
CREATE TABLE property_features (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    
    feature_name_bg VARCHAR(100) NOT NULL,
    feature_name_de VARCHAR(100) NOT NULL,
    feature_name_ru VARCHAR(100) NOT NULL,
    
    feature_value_bg VARCHAR(255) NOT NULL,
    feature_value_de VARCHAR(255) NOT NULL,
    feature_value_ru VARCHAR(255) NOT NULL,
    
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Таблица за настройки на сайта
CREATE TABLE site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Добавяне на настройка за поддръжка
INSERT INTO site_settings (setting_key, setting_value) VALUES ('maintenance_mode', 'false');

-- Индекси за оптимизация на търсенето
CREATE INDEX idx_properties_type ON properties(type);
CREATE INDEX idx_properties_status ON properties(status);
CREATE INDEX idx_properties_featured ON properties(featured);
CREATE INDEX idx_inquiries_status ON inquiries(status); 