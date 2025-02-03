-- Create viewings table
CREATE TABLE viewings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    client_id INT NOT NULL,
    agent_id INT NOT NULL,
    scheduled_at DATETIME NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    feedback_rating INT,
    feedback_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id),
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (agent_id) REFERENCES users(id)
);

-- Create viewing notifications table
CREATE TABLE viewing_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    viewing_id INT NOT NULL,
    recipient_type ENUM('client', 'agent') NOT NULL,
    recipient_id INT NOT NULL,
    type ENUM('scheduled', 'reminder', 'cancelled', 'rescheduled', 'feedback_request') NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (viewing_id) REFERENCES viewings(id) ON DELETE CASCADE
);

-- Create viewing feedback table
CREATE TABLE viewing_feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    viewing_id INT NOT NULL,
    property_condition INT,
    price_opinion INT,
    location_rating INT,
    overall_impression INT,
    interested BOOLEAN,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (viewing_id) REFERENCES viewings(id) ON DELETE CASCADE
); 