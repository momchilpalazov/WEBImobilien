-- Таблица с права
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица за връзка между роли и права
CREATE TABLE role_permissions (
    role ENUM('admin', 'manager', 'agent') NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role, permission_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Вмъкване на основни права
INSERT INTO permissions (name, description) VALUES
('manage_users', 'Управление на потребители'),
('manage_properties', 'Управление на имоти'),
('manage_inquiries', 'Управление на запитвания'),
('manage_settings', 'Управление на настройки'),
('view_reports', 'Преглед на отчети'),
('manage_content', 'Управление на съдържание');

-- Задаване на права за ролите
INSERT INTO role_permissions (role, permission_id) 
SELECT 'admin', id FROM permissions;

INSERT INTO role_permissions (role, permission_id)
SELECT 'manager', id FROM permissions 
WHERE name IN ('manage_properties', 'manage_inquiries', 'view_reports');

INSERT INTO role_permissions (role, permission_id)
SELECT 'agent', id FROM permissions 
WHERE name IN ('manage_properties', 'manage_inquiries'); 