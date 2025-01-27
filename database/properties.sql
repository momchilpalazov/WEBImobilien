CREATE TABLE properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title_bg VARCHAR(255) NOT NULL,
    title_de VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) NOT NULL,
    description_bg TEXT NOT NULL,
    description_de TEXT NOT NULL,
    description_ru TEXT NOT NULL,
    type ENUM('industrial', 'warehouse', 'logistics') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    area DECIMAL(10,2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    latitude DECIMAL(10,8),
    longitude DECIMAL(10,8),
    status ENUM('available', 'rented', 'sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE property_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
); 