-- Create documents table
CREATE TABLE documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    category ENUM('contract', 'deed', 'certificate', 'permit', 'tax', 'insurance', 'appraisal', 'other') NOT NULL,
    status ENUM('draft', 'active', 'archived') DEFAULT 'active',
    is_template BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create document versions table for version control
CREATE TABLE document_versions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    document_id INT NOT NULL,
    version_number INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    changes_description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create document relations table to link documents to properties, deals, etc.
CREATE TABLE document_relations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    document_id INT NOT NULL,
    relation_type ENUM('property', 'deal', 'client', 'agent') NOT NULL,
    relation_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
);

-- Create document signatures table
CREATE TABLE document_signatures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    document_id INT NOT NULL,
    signer_type ENUM('client', 'agent', 'manager', 'other') NOT NULL,
    signer_id INT,
    signer_name VARCHAR(255) NOT NULL,
    signer_email VARCHAR(255),
    signature_status ENUM('pending', 'signed', 'rejected', 'expired') DEFAULT 'pending',
    signature_date TIMESTAMP NULL,
    signature_ip VARCHAR(45),
    signature_data TEXT,
    expiration_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
);

-- Create document access logs table
CREATE TABLE document_access_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    document_id INT NOT NULL,
    user_id INT NOT NULL,
    action_type ENUM('view', 'download', 'print', 'share', 'edit', 'delete') NOT NULL,
    action_details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create document templates table
CREATE TABLE document_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('contract', 'deed', 'certificate', 'permit', 'tax', 'insurance', 'appraisal', 'other') NOT NULL,
    content TEXT NOT NULL,
    variables JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create document shares table
CREATE TABLE document_shares (
    id INT PRIMARY KEY AUTO_INCREMENT,
    document_id INT NOT NULL,
    shared_by INT NOT NULL,
    shared_with_email VARCHAR(255) NOT NULL,
    access_token VARCHAR(100) NOT NULL,
    permissions JSON,
    expiration_date TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_by) REFERENCES users(id)
); 