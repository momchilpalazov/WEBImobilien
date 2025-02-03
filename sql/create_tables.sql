-- Изтриване на съществуващите таблици
DROP TABLE IF EXISTS `property_pdf_files`;
DROP TABLE IF EXISTS `property_images`;
DROP TABLE IF EXISTS `property_features`;
DROP TABLE IF EXISTS `inquiries`;
DROP TABLE IF EXISTS `properties`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `blog_posts`;
DROP TABLE IF EXISTS `admins`;

-- Създаване на таблица за имоти
CREATE TABLE IF NOT EXISTS `properties` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title_bg` VARCHAR(255) NOT NULL,
    `title_en` VARCHAR(255),
    `title_de` VARCHAR(255),
    `title_ru` VARCHAR(255),
    `description_bg` TEXT,
    `description_en` TEXT,
    `description_de` TEXT,
    `description_ru` TEXT,
    `price` DECIMAL(10,2),
    `area` DECIMAL(10,2),
    `type` VARCHAR(50),
    `status` VARCHAR(50),
    `location_bg` VARCHAR(255),
    `location_en` VARCHAR(255),
    `location_de` VARCHAR(255),
    `location_ru` VARCHAR(255),
    `address` VARCHAR(255),
    `latitude` DECIMAL(10,8),
    `longitude` DECIMAL(11,8),
    `featured` BOOLEAN DEFAULT FALSE,
    `pdf_flyer` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Създаване на таблица за снимки на имоти
CREATE TABLE IF NOT EXISTS `property_images` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `property_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `is_main` BOOLEAN DEFAULT FALSE,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Създаване на таблица за характеристики на имоти
CREATE TABLE IF NOT EXISTS `property_features` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `property_id` INT NOT NULL,
    `feature_bg` VARCHAR(255) NOT NULL,
    `feature_en` VARCHAR(255),
    `feature_de` VARCHAR(255),
    `feature_ru` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Създаване на таблица за запитвания
CREATE TABLE IF NOT EXISTS `inquiries` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `property_id` INT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(50),
    `message` TEXT,
    `status` VARCHAR(20) DEFAULT 'new',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Създаване на таблица за услуги
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title_bg` VARCHAR(255) NOT NULL,
    `title_en` VARCHAR(255),
    `title_de` VARCHAR(255),
    `title_ru` VARCHAR(255),
    `description_bg` TEXT,
    `description_en` TEXT,
    `description_de` TEXT,
    `description_ru` TEXT,
    `icon` VARCHAR(50),
    `image` VARCHAR(255),
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Създаване на таблица за блог постове
CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title_bg` VARCHAR(255) NOT NULL,
    `title_en` VARCHAR(255),
    `title_de` VARCHAR(255),
    `title_ru` VARCHAR(255),
    `content_bg` TEXT NOT NULL,
    `content_en` TEXT,
    `content_de` TEXT,
    `content_ru` TEXT,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `image` VARCHAR(255),
    `is_published` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Създаване на таблица за администратори
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `last_login` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Създаване на таблица за PDF файлове
CREATE TABLE IF NOT EXISTS `property_pdf_files` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `property_id` INT NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` INT NOT NULL,
    `version` INT DEFAULT 1,
    `language` VARCHAR(2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 