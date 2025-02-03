-- Таблица за транзакции
CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('sale', 'rent', 'commission', 'expense', 'other') NOT NULL,
    property_id INT UNSIGNED NULL,
    client_id INT UNSIGNED NULL,
    agent_id INT UNSIGNED NULL,
    amount DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'EUR',
    commission_rate DECIMAL(5, 2) NULL,
    commission_amount DECIMAL(12, 2) NULL,
    status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    description TEXT NULL,
    transaction_date DATE NOT NULL,
    due_date DATE NULL,
    payment_method VARCHAR(50) NULL,
    reference_number VARCHAR(100) NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type_date (type, transaction_date),
    INDEX idx_property (property_id),
    INDEX idx_client (client_id),
    INDEX idx_agent (agent_id),
    INDEX idx_status (status),
    
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица за разходи
CREATE TABLE IF NOT EXISTS expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'EUR',
    description TEXT NULL,
    expense_date DATE NOT NULL,
    recurring BOOLEAN NOT NULL DEFAULT FALSE,
    recurring_period VARCHAR(20) NULL,
    next_recurring_date DATE NULL,
    property_id INT UNSIGNED NULL,
    agent_id INT UNSIGNED NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category_date (category, expense_date),
    INDEX idx_property (property_id),
    INDEX idx_agent (agent_id),
    INDEX idx_recurring (recurring, next_recurring_date),
    
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица за комисионни правила
CREATE TABLE IF NOT EXISTS commission_rules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('sale', 'rent') NOT NULL,
    property_type VARCHAR(50) NULL,
    min_amount DECIMAL(12, 2) NULL,
    max_amount DECIMAL(12, 2) NULL,
    rate DECIMAL(5, 2) NOT NULL,
    is_percentage BOOLEAN NOT NULL DEFAULT TRUE,
    fixed_amount DECIMAL(12, 2) NULL,
    agent_share DECIMAL(5, 2) NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type_active (type, active),
    INDEX idx_property_type (property_type),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица за финансови цели
CREATE TABLE IF NOT EXISTS financial_goals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    target_amount DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'EUR',
    achieved_amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
    agent_id INT UNSIGNED NULL,
    property_type VARCHAR(50) NULL,
    location VARCHAR(100) NULL,
    status ENUM('active', 'achieved', 'missed', 'cancelled') NOT NULL DEFAULT 'active',
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_period (period_start, period_end),
    INDEX idx_agent (agent_id),
    INDEX idx_status (status),
    
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица за финансови прогнози
CREATE TABLE IF NOT EXISTS financial_forecasts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    forecast_amount DECIMAL(12, 2) NOT NULL,
    actual_amount DECIMAL(12, 2) NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'EUR',
    confidence_level DECIMAL(5, 2) NULL,
    agent_id INT UNSIGNED NULL,
    property_type VARCHAR(50) NULL,
    location VARCHAR(100) NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_period (period_start, period_end),
    INDEX idx_agent (agent_id),
    
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 