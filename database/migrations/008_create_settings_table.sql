CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(50) NOT NULL UNIQUE,
    value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Вмъкване на основни настройки
INSERT INTO settings (`key`, value) VALUES
('site_title', 'Industrial Properties'),
('contact_email', 'info@example.com'),
('seo_title', 'Най-добрите индустриални имоти'),
('seo_description', 'Намерете идеалния индустриален имот за вашия бизнес.'); 