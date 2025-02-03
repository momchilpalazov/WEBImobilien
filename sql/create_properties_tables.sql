-- Create properties table
CREATE TABLE IF NOT EXISTS properties (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title_bg VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    title_de VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) NOT NULL,
    location_bg VARCHAR(255) NOT NULL,
    location_en VARCHAR(255) NOT NULL,
    location_de VARCHAR(255) NOT NULL,
    location_ru VARCHAR(255) NOT NULL,
    description_bg TEXT,
    description_en TEXT,
    description_de TEXT,
    description_ru TEXT,
    type ENUM('manufacturing', 'logistics', 'office', 'logistics_park', 'specialized') NOT NULL,
    status ENUM('available', 'reserved', 'rented', 'sold') NOT NULL DEFAULT 'available',
    price DECIMAL(12, 2) NOT NULL,
    area DECIMAL(10, 2) NOT NULL,
    pdf_flyer VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create property images table
CREATE TABLE IF NOT EXISTS property_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    filename VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 