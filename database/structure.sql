-- Създаване на базата данни
CREATE DATABASE IF NOT EXISTS industrial_properties CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE industrial_properties;

-- Таблица за имоти
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_bg VARCHAR(255) NOT NULL,
    title_de VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) NOT NULL,
    description_bg TEXT,
    description_de TEXT,
    description_ru TEXT,
    type ENUM('industrial', 'warehouse', 'logistics', 'office') NOT NULL,
    status ENUM('sale', 'rent') NOT NULL,
    price DECIMAL(12, 2) NOT NULL,
    price_per_sqm DECIMAL(10, 2),
    area DECIMAL(10, 2) NOT NULL,
    location_bg VARCHAR(255) NOT NULL,
    location_de VARCHAR(255) NOT NULL,
    location_ru VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    lat DECIMAL(10, 8),
    lng DECIMAL(11, 8),
    built_year INT,
    last_renovation INT,
    floors INT,
    parking_spots INT,
    ceiling_height DECIMAL(4, 2),
    office_space DECIMAL(10, 2),
    storage_space DECIMAL(10, 2),
    production_space DECIMAL(10, 2),
    heating BOOLEAN DEFAULT 0,
    electricity BOOLEAN DEFAULT 0,
    water_supply BOOLEAN DEFAULT 0,
    security BOOLEAN DEFAULT 0,
    loading_docks INT DEFAULT 0,
    virtual_tour_url VARCHAR(255),
    presentation_file VARCHAR(255),
    active BOOLEAN DEFAULT 1,
    featured BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Таблица за снимки на имотите
CREATE TABLE property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Таблица за документи на имотите
CREATE TABLE property_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    title_bg VARCHAR(255) NOT NULL,
    title_de VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Таблица за потребители
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'agent', 'user') NOT NULL DEFAULT 'user',
    phone VARCHAR(50),
    active BOOLEAN DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Таблица за запитвания
CREATE TABLE inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    message TEXT NOT NULL,
    status ENUM('new', 'in_progress', 'completed', 'spam') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Таблица за сделки
CREATE TABLE deals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    client_id INT,
    agent_id INT,
    type ENUM('sale', 'rent') NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    commission DECIMAL(10, 2),
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Таблица за клиенти
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    company VARCHAR(255),
    notes TEXT,
    source VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Таблица за новини/блог
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_bg VARCHAR(255) NOT NULL,
    title_de VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) NOT NULL,
    content_bg TEXT NOT NULL,
    content_de TEXT NOT NULL,
    content_ru TEXT NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    image VARCHAR(255),
    author_id INT,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Индекси за оптимизация на търсенето
CREATE INDEX idx_properties_type ON properties(type);
CREATE INDEX idx_properties_status ON properties(status);
CREATE INDEX idx_properties_price ON properties(price);
CREATE INDEX idx_properties_area ON properties(area);
CREATE INDEX idx_properties_location ON properties(location_bg);
CREATE INDEX idx_properties_active_featured ON properties(active, featured); 