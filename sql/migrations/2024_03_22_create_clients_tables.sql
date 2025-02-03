-- Create clients table
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(50),
    status ENUM('active', 'inactive', 'potential') DEFAULT 'potential',
    source VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create client preferences table
CREATE TABLE client_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    property_type VARCHAR(50),
    min_price DECIMAL(12,2),
    max_price DECIMAL(12,2),
    min_area DECIMAL(10,2),
    max_area DECIMAL(10,2),
    location VARCHAR(255),
    bedrooms INT,
    bathrooms INT,
    additional_features TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Create client interactions table
CREATE TABLE client_interactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    interaction_type ENUM('call', 'email', 'meeting', 'viewing', 'offer', 'other'),
    description TEXT,
    agent_id INT,
    property_id INT NULL,
    scheduled_at DATETIME NULL,
    status ENUM('planned', 'completed', 'cancelled') DEFAULT 'planned',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES users(id),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
);

-- Create client documents table
CREATE TABLE client_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    document_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
);

-- Create client property matches table
CREATE TABLE client_property_matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    property_id INT NOT NULL,
    match_score DECIMAL(5,2),
    status ENUM('pending', 'shown', 'interested', 'not_interested') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
); 