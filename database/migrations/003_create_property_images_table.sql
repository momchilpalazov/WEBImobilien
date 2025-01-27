CREATE TABLE property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 