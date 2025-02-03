-- Create marketing materials table
CREATE TABLE marketing_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    type ENUM('photo', 'video', 'virtual_tour', 'brochure', 'floor_plan', 'document') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    sort_order INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Create marketing campaigns table
CREATE TABLE marketing_campaigns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    budget DECIMAL(10,2),
    status ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create campaign properties junction table
CREATE TABLE campaign_properties (
    campaign_id INT NOT NULL,
    property_id INT NOT NULL,
    PRIMARY KEY (campaign_id, property_id),
    FOREIGN KEY (campaign_id) REFERENCES marketing_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Create campaign channels table
CREATE TABLE campaign_channels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campaign_id INT NOT NULL,
    channel_type ENUM('social_media', 'email', 'website', 'print', 'portal', 'other') NOT NULL,
    channel_name VARCHAR(100) NOT NULL,
    target_audience TEXT,
    budget_allocation DECIMAL(10,2),
    start_date DATE,
    end_date DATE,
    metrics JSON,
    status ENUM('planned', 'active', 'completed') DEFAULT 'planned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES marketing_campaigns(id) ON DELETE CASCADE
);

-- Create marketing analytics table
CREATE TABLE marketing_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    material_id INT,
    campaign_id INT,
    channel_id INT,
    metric_type ENUM('views', 'clicks', 'inquiries', 'shares', 'leads', 'conversions') NOT NULL,
    metric_value INT NOT NULL,
    date_recorded DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES marketing_materials(id) ON DELETE SET NULL,
    FOREIGN KEY (campaign_id) REFERENCES marketing_campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (channel_id) REFERENCES campaign_channels(id) ON DELETE SET NULL
); 