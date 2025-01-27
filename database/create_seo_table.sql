CREATE TABLE IF NOT EXISTS seo_meta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_type VARCHAR(50) NOT NULL,
    page_id INT,
    language VARCHAR(2) NOT NULL,
    title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    canonical_url VARCHAR(255),
    og_title VARCHAR(255),
    og_description TEXT,
    og_image VARCHAR(255),
    schema_markup TEXT,
    robots_meta VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_page_lang (page_type, page_id, language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 